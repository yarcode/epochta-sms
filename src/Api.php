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

    public $version = '3.0';
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
    public function generateControlSum($method, $params = [])
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
     * @param string $method
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function call($method, $params = [])
    {
        if (null === $this->client) {
            $this->client = new Client([
                'base_uri' => static::API_URL. '/'. $method
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
     * Создать адресную книгу
     *
     * @param $name
     * @param null $description
     * @return string
     */
    public function addAddressBook($name, $description = null)
    {
        return $this->call('addAddressbook', compact('name', 'description'));
    }
}
