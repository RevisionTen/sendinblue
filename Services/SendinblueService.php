<?php

declare(strict_types=1);

namespace RevisionTen\Sendinblue\Services;

use Exception;
use GuzzleHttp\Client;
use SendinBlue\Client\Api\ContactsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\CreateContact;

class SendinblueService
{
    /** @var array */
    private $config;
    private $apiInstance;

    public function __construct(array $config)
    {
        $this->config = $config;
        $apiConfig = Configuration::getDefaultConfiguration()->setApiKey('api-key', $config['api_key']);
        $this->apiInstance = new ContactsApi(
            new Client(),
            $apiConfig
        );
    }

    /**
     * Subscribes a user to a list.
     *
     * @param string $campaign
     * @param string $email
     * @param array  $mergeFields
     *
     * @return bool
     *
     * @throws Exception
     */
    public function subscribe(string $campaign, string $email, array $mergeFields = []): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        $contact = new CreateContact();
        $contact['email'] = $email;
        $contact['attributes'] = $mergeFields;
        $contact['listIds'] = [
            $this->config['campaigns'][$campaign]['list_id'],
        ];
        $this->apiInstance->createContact($contact);

        return true;
    }

    /**
     * Unsubscribes a user from a list.
     *
     * @param string $campaign
     * @param string $email
     *
     * @return bool
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unsubscribe(string $campaign, string $email): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        $this->apiInstance->removeContactFromList($this->config['campaigns'][$campaign]['list_id'], $email);

        return true;
    }
}
