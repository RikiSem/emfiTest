<?php

namespace App\Http\Controllers;

use App\Models\AuthToken;
use App\Repositories\TokenRepository;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
class AuthController extends Controller
{
    protected const AUTH_METHOD = '/oauth2/access_token';
    protected TokenRepository $tokenRepository;
    protected Client $client;

    public function __construct()
    {
        $this->tokenRepository = new TokenRepository;
        $this->client = new Client();
    }
    public function handler() {
        try {
            $response = $this->client->post(config('amoCrmAuth.url') . self::AUTH_METHOD, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $this->prepareJson(
                    config('amoCrmAuth.code'),
                    'authorization_code'
                )
            ]);

            $body = json_decode($response->getBody(), true);
            $this->tokenRepository->seveToken(
                $body['access_token'],
                $body['refresh_token'],
                Carbon::now()->timestamp + $body['expires_in']
            );

            $result = response('ok');
        } catch (ClientException $e) {
            $result = response($e->getResponse()->getBody(), 200);
        }

        return $result;
    }

    public function updateToken()
    {
        try {
            $token = $this->getToken();
            $response = $this->client->post(config('amoCrmAuth.url') . self::AUTH_METHOD, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $this->prepareJson(
                    $token->refresh_token,
                    'refresh_token'
                )
            ]);

            $body = json_decode($response->getBody(), true);
            $this->tokenRepository->updateToken(
                $body['access_token'],
                $body['refresh_token'],
                Carbon::now()->timestamp + $body['expires_in']
            );
        } catch (ClientException $e) {
            throw new ClientException(
                $e->getResponse()->getBody(), 
                $e->getRequest(), 
                $e->getResponse()
            );
        }
    }

    public function getToken(): AuthToken
    {
        return $this->tokenRepository->getToken();
    }

    public function prepareJson(string $code, string $grandType): array
    {
        switch ($grandType) {
            case 'refresh_token':
                $result = [
                    'client_id' => config('amoCrmAuth.integration_id'),
                    'client_secret' => config('amoCrmAuth.secret'),
                    'grant_type' => $grandType,
                    'refresh_token' => $code,
                    'redirect_uri' => config('amoCrmAuth.redirect')
                ];
                break;
            case 'authorization_code':
                $result = [
                    'client_id' => config('amoCrmAuth.integration_id'),
                    'client_secret' => config('amoCrmAuth.secret'),
                    'grant_type' => $grandType,
                    'code' => $code,
                    'redirect_uri' => config('amoCrmAuth.redirect')
                ];
                break;
        }
        return $result;
    }
}
