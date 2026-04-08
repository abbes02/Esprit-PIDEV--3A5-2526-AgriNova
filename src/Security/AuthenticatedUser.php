<?php

namespace App\Security;

final class AuthenticatedUser
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $displayName,
        public readonly string $role,
    ) {
    }

    /**
     * @return array{id: string, email: string, display_name: string, role: string}
     */
    public function toSession(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'display_name' => $this->displayName,
            'role' => $this->role,
        ];
    }
}
