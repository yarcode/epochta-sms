<?php
/**
 * Created by PhpStorm.
 * User: Sony
 * Date: 25.09.2017
 * Time: 13:28
 */

require_once 'vendor/autoload.php';

use YarCode\EpochtaSMS\Api;

$publicKey = 'a9277c9eb7fc510227e5514a2595ebf0';
$privateKey = '99980883bd09aebb28cbb3479cbdbe51';
$api = new Api($privateKey, $publicKey, true);

var_dump($api->addAddressBook('Моя новыя книга', 'щдщдщ'));