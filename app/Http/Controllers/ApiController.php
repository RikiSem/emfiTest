<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected Client $client;
    protected AuthController $authController;

    public function __construct()
    {
        $this->authController = new AuthController();
        $this->client = new Client();
    }
    protected const SET_NOTE_METHOD = '/api/v4/{%s}/{%s}/notes';
    public function setNote(string $text, string $entityType, int $entityId, int $createdBy)
    {
        $this->client->post(config('amoCrmAuth.url') . "/api/v4/$entityType/$entityId/notes", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => sprintf('Bearer %s', $this->authController->getToken()->access_token)
            ],
            'json' => [
                [
                    'note_type' => 'common',
                    'responsible_user_id' => $createdBy,
                    'params' => [
                        'text' => $text
                    ]
                ]
            ]
        ]);
    }
}
