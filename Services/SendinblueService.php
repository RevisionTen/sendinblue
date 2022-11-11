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
use SendinBlue\Client\Model\RemoveContactFromList;
use SendinBlue\Client\Model\UpdateContact;

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
    public function getContacts(string $campaign): ?array
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return null;
        }

        $listId = (int) ($this->config['campaigns'][$campaign]['list_id'] ?? 1);

        $rows = 500;
        $contactsResult = $this->apiInstance->getContactsFromList($listId, null, (string) $rows);
        $contacts = $contactsResult->getContacts();
        $count = $contactsResult->getCount();
        $maxPages = ceil($count / $rows);

        if ($maxPages > 1) {
            $currentPage = 2;
            while ($currentPage <= $maxPages) {
                $offset = (($currentPage - 1) * $rows);
                $contactsResult = $this->apiInstance->getContactsFromList($listId, null, (string) $rows, (string) $offset);
                array_push($contacts, ...$contactsResult->getContacts());

                $currentPage++;
            }
        }

        return $contacts;
    }

    /**
     * @throws ApiException
     */
    public function getContact(string $email): GetExtendedContactDetails
    {
        return $this->apiInstance->getContactInfo($email);
    }

    /**
     * @throws ApiException
     */
    public function updateContact(string $email, UpdateContact $updateContact): void
    {
        $this->apiInstance->updateContact($email, $updateContact);
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
    public function subscribeWithDoubleOptIn(string $campaign, string $email, string $source = null, array $attributes = [], string $redirectionUrl = null): bool
    {
        if (null === $redirectionUrl) {
            throw new Exception('You must provide a $redirectionUrl');
        }

        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        $templateId = $this->config['campaigns'][$campaign]['doi_template_id'] ?? null;
        if (null === $templateId) {
            throw new Exception('You must provide a doi_template_id');
        }

        $listId = (int) ($this->config['campaigns'][$campaign]['list_id'] ?? 1);

        try {
            $contact = $this->getContact($email);
        } catch (ApiException $exception) {
            $contact = false;
        }

        if ($contact) {
            // Update existing contact.
            $updateContact = new UpdateContact();

            // Update attributes.
            $existingAttributes = (object) $contact->getAttributes(); // Sometimes returns an array.
            foreach ($attributes as $key => $value) {
                $existingAttributes->{$key} = $value;
            }
            $updateContact->setAttributes($existingAttributes);

            // Add to list.
            $listIds = $contact->getListIds();
            if (!in_array($listId, $listIds, false)) {
                $listIds[] = $listId;
                $updateContact->setListIds($listIds);
            }

            $this->updateContact($email, $updateContact);
        } else {
            // Create new contact.
            $contact = new CreateDoiContact();
            $contact->setEmail($email);
            $contact->setAttributes((object) $attributes);
            $contact->setIncludeListIds([$listId]);
            $contact->setTemplateId((int) $templateId);
            $contact->setRedirectionUrl($redirectionUrl);

            $this->apiInstance->createDoiContact($contact);
        }

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

        $removeContactFromList = new RemoveContactFromList();
        $removeContactFromList->setEmails([$email]);
        $this->apiInstance->removeContactFromList($this->config['campaigns'][$campaign]['list_id'], $removeContactFromList);

        return true;
    }
}
