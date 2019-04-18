<?php

include "init.php";

$uri = "https://testnet.yuanbenlian.com";

// test key
// you can create private key
// $privateKey = \YBL\Kernel\Eth\Key::createPrivateKey();

$privateKey = '4e8c4b58b63e4584b807efa63182cff82440c263e442e21d4ac80ce857ccfbd6';

/*
 * timeout : Float describing the timeout of the request in seconds. default 30 s.
 * verify : Describes the SSL certificate verification behavior of a request.
 * Set to true to enable SSL certificate verification and use the default CA bundle provided by operating system.
 * Set to false to disable certificate verification (this is insecure!).
 * Set to a string to provide the path to a CA bundle to enable verification using a custom certificate.
 */
$config = [
    'private_key' => $privateKey,  // must
    'base_uri' => $uri,  // not must, default is https://openapi.yuanbenlian.com,
    'timeout' => 30,   // not must , default is 30
    'verify' => true,  // not must , default is true
];

$client = new \YBL\ClientBuilder($config);

$subkeys = [];

for ($i = 0; $i < 10; $i++) {
    $privateKeyTmp = \YBL\Kernel\Eth\Key::createPrivateKey();
    \YBL\Kernel\Eth\Key::init($privateKeyTmp);
    $subkeys[] = \YBL\Kernel\Eth\Key::getPublicKey()->getHex();
}

$response = $client->registerAccount($subkeys);

echo "result : " . $response->getBody()->getContents() . PHP_EOL;

