<?php
namespace Oxalis\WebAuthn;

use Oxalis\Models\Passkey;
use Cose\Algorithm\Manager as CoseManager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithms;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Session;
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
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            ),
            excludeCredentials: $this->existingDescriptors($user),
            timeout: 60000,
        );

        $json = $this->serializer()->serialize($options, 'json');

        Session::put(self::SESSION_REGISTER, [
            'options' => $json,
            'label'   => $label,
            'user_id' => $user->getAuthIdentifier(),
            'at'      => now()->timestamp,
        ]);

        return json_decode($json, true);
    }

    public function finishRegistration(Authenticatable $user, array $response): Passkey
    {
        $stored = Session::get(self::SESSION_REGISTER);
        abort_if(!$stored || now()->timestamp - $stored['at'] > 300, 422, 'Registration session expired.');

        $serializer = $this->serializer();
        $options    = $serializer->deserialize($stored['options'], PublicKeyCredentialCreationOptions::class, 'json');

        $credential = $serializer->deserialize(json_encode($response), PublicKeyCredential::class, 'json');
        abort_unless($credential->response instanceof AuthenticatorAttestationResponse, 422, 'Invalid response type.');

        $factory = new CeremonyStepManagerFactory();
        $factory->setAllowedOrigins($this->origins());
        $factory->setAttestationStatementSupportManager($this->attestationManager());

        $validator = AuthenticatorAttestationResponseValidator::create($factory->creationCeremony());

        $source = $validator->check($credential->response, $options, $this->origin());

        // Enterprise attestation enforcement:
        // When OXALIS_REQUIRE_ATTESTATION=true, reject 'none' attestation.
        // 'none' means the device didn't prove its origin — acceptable for consumer
        // apps but insufficient for enterprise/high-assurance scenarios.
        if (config('oxalis.require_attestation', false)) {
            $stmt = $credential->response->attestationObject->attStmt ?? null;
            $fmt  = $credential->response->attestationObject->fmt ?? 'none';
            abort_if(
                $fmt === 'none',
                422,
                'This platform requires verified device attestation. ' .
                'Please use a hardware security key or a platform with TPM/Secure Enclave support.'
            );
        }

        Session::forget(self::SESSION_REGISTER);

        return Passkey::create([
            'user_id'         => $user->getAuthIdentifier(),
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

    public function finishAuthentication(array $response): ?Authenticatable
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

        $factory = new CeremonyStepManagerFactory();
        $factory->setAllowedOrigins($this->origins());

        $validator = AuthenticatorAssertionResponseValidator::create($factory->requestCeremony());

        $updatedSource = $validator->check($pkSource, $credential->response, $options, $this->origin(), null);

        $passkey->update([
            'public_key_json' => $serializer->serialize($updatedSource, 'json'),
            'last_used_at'    => now(),
        ]);

        Session::forget(self::SESSION_ASSERT);

        $userModel = config('oxalis.user_model');
        return $userModel::find($passkey->user_id);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────────────

    public function hasPasskeys(Authenticatable $user): bool
    {
        return Passkey::where('user_id', $user->getAuthIdentifier())->exists();
    }

    private function userHandle(Authenticatable $user): string
    {
        // Raw binary bytes — hex2bin converts the 64-char hex HMAC to 32 bytes.
        // The serializer base64url-encodes these for the browser.
        return hex2bin(hash_hmac('sha256', $user->getAuthIdentifier().'|oxalis', config('app.key')));
    }

    /** All configured origins as an array. */
    private function origins(): array
    {
        $raw = config('oxalis.origins', [env('APP_URL', 'http://localhost')]);
        return is_array($raw) ? array_values(array_filter($raw)) : [$raw];
    }

    /** The first configured origin — used as the default host for check(). */
    private function origin(): string
    {
        return $this->origins()[0] ?? 'http://localhost';
    }

    private function serializer(): \Symfony\Component\Serializer\SerializerInterface
    {
        return (new WebauthnSerializerFactory($this->attestationManager()))->create();
    }

    /** @return PublicKeyCredentialDescriptor[] */
    private function existingDescriptors(Authenticatable $user): array
    {
        return Passkey::where('user_id', $user->getAuthIdentifier())
            ->get()
            ->map(fn($p) => PublicKeyCredentialDescriptor::create(
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
        return $manager;
    }
}
