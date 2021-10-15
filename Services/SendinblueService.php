<?php

declare(strict_types=1);

namespace RevisionTen\Sendinblue\Services;

use Exception;
use GuzzleHttp\Client;
use SendinBlue\Client\Api\ContactsApi;
use SendinBlue\Client\ApiException;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\CreateContact;
use SendinBlue\Client\Model\CreateDoiContact;
use SendinBlue\Client\Model\GetExtendedContactDetails;

class SendinblueService
{
    private array $config;

    private ContactsApi $apiInstance;

    public function __construct(array $config)
    {
        $this->config = $config;

        $apiConfig = Configuration::getDefaultConfiguration()->setApiKey('api-key', $config['api_key']);
        $client = new Client();
        $this->apiInstance = new ContactsApi($client, $apiConfig);
    }

    /**
     * @throws ApiException
     */
    public function getContact(string $email): GetExtendedContactDetails
    {
        return $this->apiInstance->getContactInfo($email);
    }

    /**
     * Subscribes a user to a list.
     *
     * @param string $campaign
     * @param string $email
     * @param string|null $source
     * @param array $attributes
     *
     * @return bool
     *
     * @throws ApiException
     */
    public function subscribe(string $campaign, string $email, string $source = null, array $attributes = []): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        $contact = new CreateContact();
        $contact->setEmail($email);
        $contact->setAttributes((object) $attributes);

        $listId = (int) ($this->config['campaigns'][$campaign]['list_id'] ?? 1);
        $contact->setListIds([$listId]);

        $this->apiInstance->createContact($contact);

        return true;
    }

    /**
     * Subscribes a user to a list and require a double opt-in.
     *
     * @param string $campaign
     * @param string $email
     * @param string|null $source
     * @param array $attributes
     * @param string $redirectionUrl
     *
     * @return bool
     *
     * @throws ApiException
     */
    public function subscribeWithDoubleOptIn(string $campaign, string $email, string $source = null, array $attributes = [], string $redirectionUrl): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        $contact = new CreateDoiContact();
        $contact->setEmail($email);
        $contact->setAttributes((object) $attributes);

        $listId = (int) ($this->config['campaigns'][$campaign]['list_id'] ?? 1);
        $contact->setIncludeListIds([$listId]);

        $templateId = $this->config['campaigns'][$campaign]['doi_template_id'] ?? null;
        if (null === $templateId) {
            throw new Exception('You must provide a doi_template_id');
        }
        $contact->setTemplateId((int) $templateId);

        $contact->setRedirectionUrl($redirectionUrl);

        $this->apiInstance->createDoiContact($contact);

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
     * @throws ApiException
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
