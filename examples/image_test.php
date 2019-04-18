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

$content = file_get_contents(__DIR__ . '/../resources/test.png');

// build metadata parameters
// pubkey is populated by default
$data = [
    'type' => 'image',
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
    'data' => [
        'ext' => 'png',
        'width' => 200,
        'height' => 150
    ], // not must need
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

echo "result : " . $response->getBody()->getContents() . PHP_EOL;

/*
 * result : {"code":"ok","data":{"dna":"1AGK96C7JR6CC69JUKG0ZJLCSFODZ6GS9JNYSHKWWS3JZ7RJN5"}}
 */

$dna = '1AGK96C7JR6CC69JUKG0ZJLCSFODZ6GS9JNYSHKWWS3JZ7RJN5';

$responseMeta = $client->searchMetadata($dna);

echo "metadata info : " . $responseMeta->getBody()->getContents() . PHP_EOL;

/*
 metadata info : {"code":"ok","data":{"content_hash":"028a6011079dc3316f36198bdcc9476937ea2c3f9b705557c8e11be737b01107","created":"1555508743","block_hash":"627E5DEA02407894CA42EFE1267A9BC4F6770AB3","block_height":"323099","language":"en","signature":"af4123fb82d0ba48f99c604c3990e676f8b21266ad4c74b9632d6fd6519b16e41565453189ccfb7b390629f516eee53c40556620fcbc4b3989441f3606b11b4d00","pubkey":"02960bfff6ffd689b58c87e142e37a47ec348984dfdb79d6a069302cfac89d4368","type":"article","license":{"type":"none"},"category":"news","dna":"1AGK96C7JR6CC69JUKG0ZJLCSFODZ6GS9JNYSHKWWS3JZ7RJN5","title":"test ybl article"},"tx":{"block_hash":"32137a2f4f5d24870caeb535724cf946230f1243","block_height":323101,"sender":"1624de6420e15aee7543c19a01a12ab135416bedc6b77d86ceb373c1bb51785f192c2c8452","proposer":"","time":1555508726,"version":"v4"}}
 */

