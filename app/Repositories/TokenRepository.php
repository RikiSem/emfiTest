<?php

namespace App\Repositories;

use App\Models\AuthToken;

class TokenRepository
{
    public function seveToken(string $accessToken, string $refreshToken, int $expiresAt)
    {
        AuthToken::create([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt
        ])
            ->save();
    }

    public function updateToken(string $accessToken, string $refreshToken, int $expiresAt)
    {
        $token = $this->getToken();
        $token->access_token = $accessToken;
        $token->refresh_token = $refreshToken;
        $token->expires_at = $expiresAt;
        $token->update();
    }

    public function getToken(): AuthToken
    {
        return AuthToken::first();
    }
}
