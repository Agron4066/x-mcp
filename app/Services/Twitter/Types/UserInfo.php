<?php

namespace App\Services\Twitter\Types;

class UserInfo
{
    public function __construct(
        public readonly string $username,
        public readonly string $name,
        public readonly int $followersCount,
        public readonly ?string $description = null,
        public readonly ?string $profileImageUrl = null,
    ) {}

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'name' => $this->name,
            'followers_count' => $this->followersCount,
            'description' => $this->description,
            'profile_image_url' => $this->profileImageUrl,
        ];
    }
}
