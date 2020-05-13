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

$res = $client->searchLatestBlock();

$result = json_decode($res->getBody()->getContents(), true);

if (isset($result['code']) && $result['code'] == 'ok') {
    $latest_block_hash = $result['data']['latest_block_hash'];
    $latest_block_height = (string)$result['data']['latest_block_height'];
} else {
    echo "get latest block wrong : " . $res;
    exit;
}

$content = 'just for test!';

// build metadata parameters
// pubkey is populated by default
$data = [
    'type' => 'article',
    'language' => 'en',
    'title' => 'test ybl article',
    'category' => 'news',
    'block_hash' => $latest_block_hash,
    'block_height' => $latest_block_height,
    'created' => (string)time(),
    'content_hash' => hash('sha256', $content),  // or Keccak::hash($content,256) or other hash algorithm
    'license' => [
        'type' => 'none'
    ],
];

$metadata = $client->buildMetadata($data);

$signature = $client->sign($metadata);

echo "signature : " . $signature . PHP_EOL;

/*
 * you can use signature to generate dna
 * $client->generateDNA($signature);
 */

$metadata = $client->setSignature($metadata, $signature);

$response = $client->publishMetadata($metadata);
$content = $response->getBody()->getContents();
echo "result : " . $content . PHP_EOL;

/*
 * result : {"code":"ok","data":{"dna":"1AGK96C7JR6CC69JUKG0ZJLCSFODZ6GS9JNYSHKWWS3JZ7RJN5"}}
 */
$resp = json_decode($content, true);
if ($resp["code"] == "ok") {

    $dna = $resp["data"]["dna"];
    $responseMeta = $client->searchMetadata($dna);
    echo "metadata info : " . $responseMeta->getBody()->getContents() . PHP_EOL;

    /*
     * {"code":"3012","msg":"Query failure","data":{"license":{}},"tx":{"block_hash":"","block_height":0,"sender":"","proposer":"","time":0,"version":""}}
     * If the code is 3012, wait a minute or so to check again until the code value is ok
     */
}

