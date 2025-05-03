<?php

namespace App\Http\Controllers;

use App\Repositories\TokenRepository;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected const ENTITY_TYPE_LEAD = 'leads';
    protected const ENTITY_TYPE_CONTACT = 'contacts';
    protected ApiController $apiController;
    protected AuthController $authController;

    public function __construct()
    {
        $this->apiController = new ApiController();
        $this->authController = new AuthController;
    }
    public function handler(Request $request) {
        file_put_contents('request_update_lead.json', json_encode($request->all()));
        try {
            if (!$this->authController->getToken()->isActive()) {
                $this->authController->updateToken();
            }

            if (isset($request['leads'])) {
                if (isset($request['leads']['add'])) {
                    foreach ($request['leads']['add'] as $lead) {
                        $this->addNoteToLeadOnCreate($lead);
                    }
                }
                if (isset($request['leads']['update'])) {
                    foreach ($request['leads']['update'] as $lead) {
                        $this->addNoteToLeadOnUpdate($lead);
                    }
                }
            }
            if ($request['contacts']) {
                if (isset($request['contacts']['add'])) {
                    foreach ($request['contacts']['add'] as $contact) {
                        $this->addNoteToContactOnCreate($contact);
                    }
                }
                if (isset($request['contacts']['update'])) {
                    foreach ($request['contacts']['update'] as $contact) {
                        $this->addNoteToContactOnUpdate($contact);
                    }
                }
            }

            $result = response('ok', 200);
        } catch (ClientException $e) {
            $result = response($e->getResponse()->getBody(), 200);
        }
        

        return $result;
    }

    public function addNoteToLeadOnUpdate(array $data) {
        $this->apiController->setNote(
            $this->generateNoteTextOnLeadUpdate($data),
            self::ENTITY_TYPE_LEAD,
            $data['id'],
            $data['created_user_id']
        );
    }

    public function addNoteToContactOnUpdate(array $data) {
        $this->apiController->setNote(
            $this->generateNoteTextOnContactUpdate($data),
            self::ENTITY_TYPE_CONTACT,
            $data['id'],
            $data['created_user_id']
        );
    }

    public function addNoteToLeadOnCreate(array $data)
    {
        $this->apiController->setNote(
            $this->generateNoteTextOnAdd($data),
            self::ENTITY_TYPE_LEAD,
            $data['id'],
            $data['created_user_id']
        );
    }

    public function addNoteToContactOnCreate(array $data)
    {
        $this->apiController->setNote(
            $this->generateNoteTextOnAdd($data),
            self::ENTITY_TYPE_CONTACT,
            $data['id'],
            $data['created_user_id']
        );
    }

    public function generateNoteTextOnContactUpdate(array $data): string
    {
        $customFields = [];
        if (isset($data['custom_fields'])) {  
            foreach ($data['custom_fields'] as $field) {
                $customFields[] = sprintf('%s - %s', $field['name'], $field['values'][0]['value']);
            }
        }

        return $this->generateNoteText(
            $data['name'],
            implode(',', $customFields),
            Carbon::createFromTimestamp($data['updated_at'])
                ->format('d.m.Y H:m')
        );
    }
    public function generateNoteTextOnLeadUpdate(array $data): string
    {
        $fields = [
            sprintf('price - %s', $data['price']),
            sprintf('status_id - %s', $data['status_id'])
        ];
        return $this->generateNoteText(
            $data['name'],
            implode(',', $fields),
            Carbon::createFromTimestamp($data['updated_at'])
                ->format('d.m.Y H:m')
        );
    }
    public function generateNoteTextOnAdd(array $data): string
    {
        return $this->generateNoteText(
            $data['name'],
            $data['responsible_user_id'],
            Carbon::createFromTimestamp($data['updated_at'])
                ->format('d.m.Y H:m')
        );
    }

    public function generateNoteText(string $name, string $info, string $updatedAt)
    {
        return sprintf('%s; %s; %s',
        $name,
            $info,
            $updatedAt,
        );
    }
}
