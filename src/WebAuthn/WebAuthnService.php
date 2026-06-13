<?php
namespace Oxalis\WebAuthn;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Session;
use Oxalis\Models\Passkey;
use Cose\Algorithm\Manager as CoseManager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithms;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AppleAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class WebAuthnService
{
    private const SESSION_REGISTER = 'oxalis_register_options';
    private const SESSION_ASSERT   = 'oxalis_assert_options';

    // ────────────────────────────────────────────────────────────────────────
    // Registration
    // ────────────────────────────────────────────────────────────────────────

    public function beginRegistration(Authenticatable $user, string $label = 'My Passkey'): array
    {
        $passkeyOnly = (bool) config('oxalis.passkey_only', false);

        $userEntity = PublicKeyCredentialUserEntity::create(
            name: $user->email,
            id: $this->userHandle($user),
            displayName: $user->name ?? $user->email,
        );

        $options = PublicKeyCredentialCreationOptions::create(
            rp: PublicKeyCredentialRpEntity::create(
                name: config('oxalis.rp_name'),
                id: config('oxalis.rp_id'),
            ),
            user: $userEntity,
            challenge: random_bytes(32),
            pubKeyCredParams: [
                PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256),
                PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS256),
            ],
            authenticatorSelection: AuthenticatorSelectionCriteria::create(
                authenticatorAttachment: $this->authenticatorAttachment(),
                residentKey: $passkeyOnly
                    ? AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED
                    : AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            ),
            excludeCredentials: $this->existingDescriptors($user),
            timeout: 60000,
        );

        $json = $this->serializer()->serialize($options, 'json');

        Session::put(self::SESSION_REGISTER, [
            'options' => $json,
            'label'   => $label,
            'user_id' => (string) $user->getAuthIdentifier(),
            'at'      => now()->timestamp,
        ]);

        $publicOptions = json_decode($json, true);
        $hints = $this->passkeyHints();

        if ($hints !== []) {
            $publicOptions['hints'] = $hints;
        }

        return $publicOptions;
    }

    public function finishRegistration(Authenticatable $user, array $response, ?string $host = null): Passkey
    {
        $stored = Session::get(self::SESSION_REGISTER);
        abort_if(!$stored || now()->timestamp - $stored['at'] > 300, 422, 'Registration session expired.');

        $serializer = $this->serializer();
        $options    = $serializer->deserialize($stored['options'], PublicKeyCredentialCreationOptions::class, 'json');

        $credential = $serializer->deserialize(json_encode($response), PublicKeyCredential::class, 'json');
        abort_unless($credential->response instanceof AuthenticatorAttestationResponse, 422, 'Invalid response type.');

        $factory   = $this->ceremonyFactory();
        $validator = AuthenticatorAttestationResponseValidator::create($factory->creationCeremony());

        $source = $validator->check($credential->response, $options, $this->ceremonyHost($host));

        if (config('oxalis.require_attestation', false)) {
            $fmt = $credential->response->attestationObject->fmt ?? 'none';
            abort_if(
                $fmt === 'none',
                422,
                'This platform requires verified device attestation. ' .
                'Please use a hardware security key or a platform with TPM/Secure Enclave support.'
            );
        }

        Session::forget(self::SESSION_REGISTER);

        return Passkey::create([
            'user_id'         => (string) $user->getAuthIdentifier(),
            'label'           => $stored['label'],
            'credential_id'   => base64_encode($source->publicKeyCredentialId),
            'public_key_json' => $serializer->serialize($source, 'json'),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Authentication
    // ────────────────────────────────────────────────────────────────────────

    public function beginAuthentication(?Authenticatable $user = null): array
    {
        if ($user && ! $this->hasPasskeys($user)) {
            abort(422, 'No passkeys registered for this account.');
        }

        $options = PublicKeyCredentialRequestOptions::create(
            challenge: random_bytes(32),
            rpId: config('oxalis.rp_id', 'localhost'),
            allowCredentials: $user ? $this->existingDescriptors($user) : [],
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            timeout: 60000,
        );

        $json = $this->serializer()->serialize($options, 'json');

        Session::put(self::SESSION_ASSERT, [
            'options' => $json,
            'at'      => now()->timestamp,
        ]);

        return json_decode($json, true);
    }

    /**
     * @return array{user: Authenticatable, passkey: Passkey}
     */
    public function finishAuthentication(array $response, ?string $host = null): array
    {
        $stored = Session::get(self::SESSION_ASSERT);
        abort_if(!$stored || now()->timestamp - $stored['at'] > 300, 422, 'Authentication session expired.');

        $serializer = $this->serializer();
        $options    = $serializer->deserialize($stored['options'], PublicKeyCredentialRequestOptions::class, 'json');

        $credential = $serializer->deserialize(json_encode($response), PublicKeyCredential::class, 'json');
        abort_unless($credential->response instanceof AuthenticatorAssertionResponse, 422, 'Invalid response type.');

        $rawId   = base64_encode($credential->rawId);
        $passkey = Passkey::where('credential_id', $rawId)->first();
        abort_if(!$passkey, 422, 'Credential not found.');

        $pkSource = $serializer->deserialize($passkey->public_key_json, PublicKeyCredentialSource::class, 'json');

        $factory   = $this->ceremonyFactory();
        $validator = AuthenticatorAssertionResponseValidator::create($factory->requestCeremony());

        $updatedSource = $validator->check(
            $pkSource,
            $credential->response,
            $options,
            $this->ceremonyHost($host),
            $credential->response->userHandle
        );

        $passkey->update([
            'public_key_json' => $serializer->serialize($updatedSource, 'json'),
            'last_used_at'    => now(),
        ]);

        Session::forget(self::SESSION_ASSERT);

        $userModel = config('oxalis.user_model');
        $user      = $userModel::find($passkey->user_id);
        abort_if(!$user, 422, 'Account not found for this passkey.');

        return ['user' => $user, 'passkey' => $passkey];
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────────────

    public function hasPasskeys(Authenticatable $user): bool
    {
        return Passkey::where('user_id', (string) $user->getAuthIdentifier())->exists();
    }

    private function userHandle(Authenticatable $user): string
    {
        return hex2bin(hash_hmac('sha256', (string) $user->getAuthIdentifier().'|oxalis', config('app.key')));
    }

    /** @return string[] */
    private function origins(): array
    {
        $raw = config('oxalis.origins', [env('APP_URL', 'http://localhost')]);

        return is_array($raw) ? array_values(array_filter($raw)) : array_filter([(string) $raw]);
    }

    /** RP ID / hostname passed to WebAuthn ceremony validators (not a full origin URL). */
    private function ceremonyHost(?string $override = null): string
    {
        if ($override !== null && $override !== '') {
            return $override;
        }

        if (! app()->runningInConsole() && request()) {
            return request()->getHost();
        }

        $first = $this->origins()[0] ?? 'http://localhost';
        $host  = parse_url($first, PHP_URL_HOST);

        return $host ?: (string) config('oxalis.rp_id', 'localhost');
    }

    private function ceremonyFactory(): CeremonyStepManagerFactory
    {
        $factory = new CeremonyStepManagerFactory();
        $origins = $this->origins();

        if ($origins !== []) {
            $factory->setAllowedOrigins($origins, false);
        } else {
            $factory->setSecuredRelyingPartyId([(string) config('oxalis.rp_id', 'localhost')]);
        }

        $factory->setAttestationStatementSupportManager($this->attestationManager());
        $factory->setAlgorithmManager($this->coseManager());

        return $factory;
    }

    private function serializer(): \Symfony\Component\Serializer\SerializerInterface
    {
        return (new WebauthnSerializerFactory($this->attestationManager()))->create();
    }

    /** @return PublicKeyCredentialDescriptor[] */
    private function existingDescriptors(Authenticatable $user): array
    {
        return Passkey::where('user_id', (string) $user->getAuthIdentifier())
            ->get()
            ->map(fn ($p) => PublicKeyCredentialDescriptor::create(
                type: 'public-key',
                id: base64_decode($p->credential_id),
            ))
            ->all();
    }

    private function coseManager(): CoseManager
    {
        return CoseManager::create()
            ->add(ES256::create())
            ->add(RS256::create());
    }

    private function attestationManager(): AttestationStatementSupportManager
    {
        $manager = AttestationStatementSupportManager::create();
        $manager->add(NoneAttestationStatementSupport::create());
        $manager->add(PackedAttestationStatementSupport::create($this->coseManager()));
        $manager->add(FidoU2FAttestationStatementSupport::create());
        $manager->add(AppleAttestationStatementSupport::create());
        $manager->add(AndroidKeyAttestationStatementSupport::create());

        return $manager;
    }

    private function authenticatorAttachment(): ?string
    {
        $attachment = config('oxalis.passkey_authenticator_attachment');

        return in_array($attachment, [
            AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
            AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_CROSS_PLATFORM,
        ], true) ? $attachment : null;
    }

    /** @return string[] */
    private function passkeyHints(): array
    {
        $hints = config('oxalis.passkey_hints', []);

        if (! is_array($hints)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $hints)));
    }
}
