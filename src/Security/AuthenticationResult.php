<?php

namespace App\Security;

final class AuthenticationResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?AuthenticatedUser $user = null,
        public readonly ?string $error = null,
    ) {
    }

    public static function success(AuthenticatedUser $user): self
    {
        return new self(true, $user);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }
}
