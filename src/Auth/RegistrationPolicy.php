<?php
namespace Oxalis\Auth;

use Oxalis\Models\Invite;
use Illuminate\Validation\ValidationException;

class RegistrationPolicy
{
    public function validate(string $email, ?string $inviteCode = null): ?Invite
    {
        $this->validateAllowedDomain($email);

        if (! (bool) config('oxalis.invites.required', false)) {
            return null;
        }

        if (! filled($inviteCode)) {
            throw ValidationException::withMessages([
                'invite_code' => 'An invite code is required to create an account.',
            ]);
        }

        try {
            $invite = Invite::where('code', strtoupper(trim((string) $inviteCode)))->first();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'invite_code' => 'Invite system unavailable.',
            ]);
        }

        if (! $invite || ! $invite->isValid()) {
            throw ValidationException::withMessages([
                'invite_code' => 'Invalid or expired invite code.',
            ]);
        }

        return $invite;
    }

    public function validateAccountCreationWithoutInvite(string $email): void
    {
        $this->validate($email, null);
    }

    public function consumeInvite(int|string|null $inviteId): void
    {
        if (! $inviteId) {
            return;
        }

        try {
            $invite = Invite::find($inviteId);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'invite_code' => 'Invite system unavailable.',
            ]);
        }

        if (! $invite || ! $invite->isValid()) {
            throw ValidationException::withMessages([
                'invite_code' => 'This invite code is no longer valid. Please request a new invite.',
            ]);
        }

        $invite->consume();
    }

    private function validateAllowedDomain(string $email): void
    {
        $allowed = array_values(array_filter(array_map(
            static fn (string $domain) => strtolower(trim($domain)),
            explode(',', (string) config('oxalis.allowed_domains', '')),
        )));

        if ($allowed === []) {
            return;
        }

        $at = strrpos($email, '@');
        $domain = $at === false ? '' : strtolower(substr($email, $at + 1));

        if (! in_array($domain, $allowed, true)) {
            throw ValidationException::withMessages([
                'email' => 'Registration is restricted to specific email domains.',
            ]);
        }
    }
}
