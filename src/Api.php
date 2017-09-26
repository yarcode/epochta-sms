<?php

namespace YarCode\EpochtaSMS;

use GuzzleHttp\Client;

/**
 * Class Api
 *
 * @package YarCode\EpochtaSMS
 * @author Yan Kuznetsov <info@yanman.ru>
 * @see https://www.epochtasms.ru/api/v3.php
 */
class Api
{
    const API_URL = 'http://api.atompark.com/api/sms/3.0';

    /** @var string */
    public $version = '3.0';

    /** @var bool */
    public $sandbox = false;

    /** @var Client */
    protected $client = null;

    /** @var array */
    protected $defaultParams = [];

    /** @var string */
    private $privateKey;

    /** @var string */
    private $publicKey;

    /**
     * Api constructor.
     * @param $privateKey
     * @param $publicKey
     * @param bool $sandbox
     */
    public function __construct($privateKey, $publicKey, $sandbox = false)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->sandbox = $sandbox;
    }

    /**
     * @param $method
     * @param array $params
     * @return string
     */
    protected function generateControlSum($method, $params = [])
    {
        $params['version'] = $this->version;
        $params['action'] = $method;

        ksort($params);
        $sum = '';
        foreach ($params as $k => $v)
            $sum .= $v;
        $sum .= $this->privateKey;
        return md5($sum);
    }

    /**
     * Makes api call
     *
     * @param $method
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function call($method, $params = [])
    {
        if (null === $this->client) {
            $this->client = new Client([
                'base_uri' => static::API_URL . '/' . $method
            ]);
        }

        $requestParams = array_merge($this->defaultParams, $params);
        $requestParams['key'] = $this->publicKey;

        if ($this->sandbox) {
            $requestParams['test'] = 1;
        }

        $requestParams['sum'] = $this->generateControlSum($method, $requestParams);

        $response = $this->client->post($method, ['form_params' => $requestParams]);
        if ($response->getStatusCode() != 200) {
            throw new \Exception('Api http error: ' . $response->getStatusCode(), $response->getStatusCode());
        }

        $result = json_decode($response->getBody(), true);
        if (isset($result['error'])) {
            throw new \BadMethodCallException('Api error: ' . $result['error'], $result['code']);
        }

        return $result;
    }


    /**
     * Create address book
     *
     * @param $name
     * @param null $description
     * @return mixed
     */
    public function addAddressBook($name, $description = null)
    {
        return $this->call('addAddressbook', compact('name', 'description'));
    }

    /**
     * Delete address book by address book ID
     *
     * @param $idAddressBook
     * @return mixed
     */
    public function delAddressBook($idAddressBook)
    {
        return $this->call('delAddressbook', [
            'idAddressBook' => $idAddressBook
        ]);
    }

    /**
     * Edit address book by address book ID
     *
     * @param $idAddressBook
     * @param $name
     * @param $description
     * @return mixed
     */
    public function editAddressBook($idAddressBook, $name, $description = null)
    {
        return $this->call('editAddressbook', [
            'idAddressBook' => $idAddressBook,
            'newName' => $name,
            'newDescr' => $description,
        ]);
    }

    /**
     * Get address book by address book ID
     *
     * @param null $idAddressBook
     * @param null $from
     * @param null $offset
     * @return mixed
     */
    public function getAddressBook($idAddressBook = null, $from = null, $offset = null)
    {
        return $this->call('getAddressbook', [
            'idAddressBook' => $idAddressBook,
            'from' => $from,
            'offset' => $offset,
        ]);
    }

    /**
     * Get all address books
     *
     * @param null $from
     * @param null $offset
     * @return mixed
     */
    function getAllAddressBook($from = null, $offset = null)
    {
        return $this->call('getAddressbook', [
            'from' => $from,
            'offset' => $offset
        ]);
    }

    /**
     * Search addressbook
     *
     * Available fields: name,phones,date.
     * Available operations: like,=,>,>=,<,<=.
     * Example for searchFields: $searchFields['name']=array('operation'=>'like', 'value'=>"test%");
     *
     * @param $fields
     * @param $from
     * @param $offset
     * @return mixed
     */
    public function searchAddressBook($fields = null, $from = null, $offset = null)
    {
        return $this->call('searchAddressBook', [
            'searchFields' => $fields,
            'from' => $from,
            'offset' => $offset,
        ]);
    }

    /**
     * Cloning the addressbook
     *
     * @param $idAddressBook
     * @return mixed
     */
    public function cloneAddressBook($idAddressBook)
    {
        return $this->call('cloneaddressbook', [
            'idAddressBook' => $idAddressBook
        ]);
    }

    /**
     * Add phone to addressbook
     *
     * @param $idAddressBook
     * @param $phone
     * @param null $variables
     * @return mixed
     */
    public function addPhoneToAddressBook($idAddressBook, $phone, $variables)
    {
        return $this->call('addPhoneToAddressBook', [
            'idAddressBook' => $idAddressBook,
            'phone' => $phone,
            'variables' => $variables,
        ]);
    }

    /**
     * Add phones to addressbook
     *
     * @param $idAddressBook
     * @param $phones
     * @return mixed
     */
    public function addPhonesToAddressBook($idAddressBook, array $phones)
    {
        return $this->call('addPhoneToAddressBook', [
            'idAddressBook' => $idAddressBook,
            'data' => json_encode($phones)
        ]);
    }

    /**
     * Get phone from addressbook
     *
     * @param $idPhone
     * @param null $idAddressBook
     * @return mixed
     */
    public function getPhoneFromAddressBookByIdPhone($idPhone, $idAddressBook = null)
    {
        return $this->call('getPhoneFromAddressBook', ['idAddressBook' => $idAddressBook, 'idPhone' => $idPhone]);
    }

    /**
     * Get phone from addressbook by phon ID
     *
     * @param $idPhone
     * @return mixed
     */
    public function getPhoneById($idPhone)
    {
        return $this->call('getPhoneFromAddressBook', ['idPhone' => $idPhone]);
    }

    /**
     * Get phone from addressbook by phone
     *
     * @param $phone
     * @return mixed
     */
    public function getPhoneByPhone($phone)
    {
        return $this->call('getPhoneFromAddressBook', [
            'phone' => $phone
        ]);
    }

    /**
     * Get phone from addressbook by phone
     *
     * @param $from
     * @param $offset
     * @return mixed
     */
    public function getAllPhones($from, $offset)
    {
        return $this->call('getPhoneFromAddressBook', [
            'from' => $from, 'offset' => $offset
        ]);
    }

    /**
     * Get phone from addressbook by address book ID
     *
     * @param $idAddressBook
     * @param null $from
     * @param null $offset
     * @return mixed
     */
    public function getPhonesByAddressBook($idAddressBook, $from = null, $offset = null)
    {
        return $this->call('getPhoneFromAddressBook', [
            'idAddressBook' => $idAddressBook,
            'from' => $from,
            'offset' => $offset
        ]);
    }

    /**
     * Get phone from addressbook by address book ID and phone
     *
     * @param $idAddressBook
     * @param $phone
     * @param null $from
     * @param null $offset
     * @return mixed
     */
    public function getPhonesByAddressBookByPhone($idAddressBook, $phone, $from = null, $offset = null)
    {
        return $this->call('getPhoneFromAddressBook', [
            'idAddressBook' => $idAddressBook,
            'phone' => $phone,
            'from' => $from,
            'offset' => $offset
        ]);
    }

    /**
     * Delete phone from addressbook by phone ID
     *
     * @param $idPhone
     * @return mixed
     */
    public function delPhoneFromAddressBookById($idPhone)
    {
        return $this->call('delPhoneFromAddressBook', [
            'idPhone' => $idPhone
        ]);
    }

    /**
     * Delete all phones from addressbook by address book ID
     *
     * @param $idAddressBook
     * @return mixed
     */
    public function delPhonesFromAddressBookByAddressBookId($idAddressBook)
    {
        return $this->call('delPhoneFromAddressBook', [
            'idAddressBook' => $idAddressBook
        ]);
    }

    /**
     * To delete a group of phone numbers from your addressbook
     *
     * @param $idPhones
     * @return mixed
     */
    public function delPhoneFromAddressBookGroup($idPhones)
    {
        return $this->call('delphonefromaddressbookgroup', [
            'idPhones' => $idPhones
        ]);
    }

    /**
     * Edit phone addressbook by phone ID
     *
     * @param $idPhone
     * @param $phone
     * @param $variables
     * @return mixed
     */
    public function editPhone($idPhone, $phone, $variables)
    {
        return $this->call('editPhone', [
            'idPhone' => $idPhone,
            'phone' => $phone,
            'variables' => $variables
        ]);
    }

    /**
     * Search phones
     * Available fields: idAddressBook,phones,normalPhone, variables, status.
     * Available operations: like,=,>,>=,<,<=.
     * Example for searchFields:
     * $searchFields['normalPhone']=array('operation'=>'like', 'value'=>"test%");
     *
     * @param $searchFields
     * @param null $from
     * @param null $offset
     * @return mixed
     */
    public function searchPhones($searchFields, $from = null, $offset = null)
    {
        return $this->call('searchPhones', [
            'searchFields' => json_encode($searchFields),
            'from' => $from,
            'offset' => $offset
        ]);
    }

    /**
     * Add phone to exceptions
     *
     * @param null $idPhone
     * @param null $phone
     * @param $reason
     * @return mixed
     */
    public function addPhoneToExceptions($idPhone = null, $phone = null, $reason)
    {
        return $this->call('addPhoneToExceptions', [
            'isPhone' => $idPhone,
            'phone' => $phone,
            'reason' => $reason
        ]);
    }


    /**
     * Delete the phone from exceptions
     *
     * @param null $idPhone
     * @param null $phone
     * @param null $idException
     * @return mixed
     */
    public function delPhoneFromExceptions($idPhone = null, $phone = null, $idException = null)
    {
        return $this->call('delPhoneFromExceptions', [
            'isPhone' => $idPhone,
            'phone' => $phone,
            'idException' => $idException
        ]);
    }

    /**
     * To edit the phone in the exceptions
     *
     * @param $idException
     * @param $reason
     * @return mixed
     */
    public function editPhoneFromExceptions($idException, $reason)
    {
        return $this->call('editExceptions', [
            'idException' => $idException,
            'reason' => $reason
        ]);
    }

    /**
     * Get exception
     *
     * @param null $idException
     * @param null $phone
     * @param null $idAddressBook
     * @param null $from
     * @param null $offset
     * @return mixed
     */
    public function getException($idException = null, $phone = null, $idAddressBook = null, $from = null, $offset = null)
    {
        return $this->call('getException', [
            'idException' => $idException,
            'phone' => $phone,
            'idAddresbook' => $idAddressBook, // TODO: check param name
            'from' => $from,
            'offset' => $offset,
        ]);
    }

    /**
     * Search exceptions
     *
     * Available fields: id, phone, date, descr.
     * Available operations: like,=,>,>=,<,<=.
     * Example for searchFields: $searchFields['name']=array('operation'=>'like', 'value'=>"test%");
     *
     * @param $fields
     * @param $from
     * @param $offset
     * @return mixed
     */
    public function searchPhonesInExceptions($fields = null, $from = null, $offset = null)
    {
        return $this->call('searchPhonesInExceptions', [
            'searchFields' => $fields,
            'from' => $from,
            'offset' => $offset,
        ]);
    }

    /**
     * Get user balance
     *
     * @param null $currency
     * @return mixed
     */
    public function getUserBalance($currency = null)
    {
        return $this->call('getUserBalance', [
            'currency' => $currency
        ]);
    }

    /**
     * To send a message to an arbitrary phone
     *
     * @param $sender
     * @param $text
     * @param $phone
     * @param null $datetime
     * @param null $smsLifetime
     * @param null $type
     * @param null $aSender
     * @return mixed
     */
    public function sendSMS($sender, $text, $phone, $datetime = null, $smsLifetime = null, $type = null, $aSender = null)
    {
        return $this->call('sendSMS', [
            'sender' => $sender,
            'text' => $text,
            'phone' => $phone,
            'datetime' => $datetime,
            'sms_lifetime' => $smsLifetime,
            'type' => $type,
            'asender' => $aSender,
        ]);
    }

    /**
     * Sending messages to a group of recipients
     *
     * @param $sender
     * @param $text
     * @param $phones
     * @param $datetime
     * @param $smsLifetime
     * @param null $type
     * @param null $aSender
     * @return mixed
     */
    public function sendSMSGroup($sender, $text, $phones, $datetime, $smsLifetime, $type = null, $aSender = null)
    {
        return $this->call('sendsmsgroup', [
            'sender' => $sender,
            'text' => $text,
            'phones' => $phones,
            'datetime' => $datetime,
            'sms_lifetime' => $smsLifetime,
            'type' => $type,
            'asender' => $aSender,
        ]);
    }

    /**
     * Register new sender name
     * Return status:
     * 0-moderation
     * 1-registered
     * 2-rejected
     *
     * @param $name
     * @param $country
     * @return mixed
     */
    public function registerSender($name, $country)
    {
        return $this->call('registerSender', ['name' => $name, 'country' => $country]);
    }

    /**
     * Get Sender object by id.
     * Return status:
     * 0-moderation
     * 1-registered
     * 2-rejected
     *
     * @param $id
     * @return mixed
     */
    public function getSenderStatusById($id)
    {
        return $this->call('getSenderStatus', ['idName' => $id]);
    }

    /**
     * Get Sender object by name and country.
     * Return status:
     * 0-moderation
     * 1-registered
     * 2-rejected
     *
     * @param $name
     * @param $country
     * @return mixed
     */
    public function getSenderStatusByNameCountry($name, $country)
    {
        return $this->call('getSenderStatus', ['name' => $name, 'country' => $country]);
    }

    /**
     * Get Sender objects.
     * Return status:
     * 0-moderation
     * 1-registered
     * 2-rejected
     *
     * @param null $from
     * @param null $offset
     * @return mixed
     */
    public function getSenderStatusAll($from = null, $offset = null)
    {
        return $this->call('getSenderStatus', ['from' => $from, 'offset' => $offset]);
    }

    /**
     * Creating campaign
     * $sender - sender. Up to 14 numbers for numeric senders, up to 11 for alphanumeric
     * $text - sms text
     * $listId - id of address book
     * $datetime must be in GMT, PHP format Y-m-d H:i:s
     *
     * @param $sender
     * @param $text
     * @param $listId
     * @param $datetime
     * @param $batch
     * @param $batchInterval
     * @param $smsLifetime
     * @param $controlPhone
     * @return mixed
     */
    public function createCampaign($sender, $text, $listId, $datetime, $batch, $batchInterval, $smsLifetime, $controlPhone)
    {
        return $this->call('createCampaign', [
            'sender' => $sender,
            'text' => $text,
            'list_id' => $listId,
            'datetime' => $datetime,
            'batch' => $batch,
            'batchinterval' => $batchInterval,
            'sms_lifetime' => $smsLifetime,
            'control_phone' => $controlPhone
        ]);
    }

    /**
     * This function will return general information about campaign
     *
     * @param $id
     * @return mixed
     */
    public function getCampaignInfo($id)
    {
        return $this->call('getCampaignInfo', ['id' => $id]);
    }

    /**
     * This function returns complete list of phones of the task, including DLR
     *
     * @param $id
     * @param null $dateFrom
     * @return mixed
     */
    public function getCampaignDeliveryStats($id, $dateFrom = null)
    {
        return $this->call('getCampaignDeliveryStats', ['id' => $id, 'datefrom' => $dateFrom]);
    }

    /**
     * Cancels campaign. Campaign must be in "Ready for sent" or "Scheduled" state
     *
     * @param $id
     * @return mixed
     */
    public function cancelCampaign($id)
    {
        return $this->call('cancelCampaign', ['id' => $id]);
    }

    /**
     * Deletes campaign, any status
     *
     * @param $id
     * @return mixed
     */
    public function deleteCampaign($id)
    {
        return $this->call('deleteCampaign', ['id' => $id]);
    }

    /**
     * Gets list of campaigns
     *
     * @return mixed
     */
    public function getCampaignList()
    {
        return $this->call('getCampaignList');
    }

    /**
     * Calculates price of campaign sending
     *
     * @param $sender
     * @param $text
     * @param $listId
     * @return mixed
     */
    public function checkCampaignPrice($sender, $text, $listId)
    {
        return $this->call('checkCampaignPrice', [
            'sender' => $sender,
            'text' => $text,
            'list_id' => $listId
        ]);
    }
}
