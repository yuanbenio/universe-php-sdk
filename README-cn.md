# YuanBenLian SDK of PHP (v1)

* 原本链API文档 [https://yuanbenlian.com/documents/readme](https://yuanbenlian.com/documents/readme)

## 翻译

- [English Document](README.md)

## 依赖 

```
 "php-64bit": ">=7.0",
 "ext-gmp": "*",
 "ext-secp256k1": ">=0.1.0",
 "ext-keccak": "~0.2",
 "guzzlehttp/guzzle": "~6.0",
 "bitwasp/buffertools": "^0.5.0"
```

* secp256k1 : [https://github.com/bitcoin-core/secp256k1](https://github.com/bitcoin-core/secp256k1)
* ext-secp256k1: [https://github.com/Bit-Wasp/secp256k1-php](https://github.com/Bit-Wasp/secp256k1-php)
* ext-keccak: [https://github.com/EricYChu/php-keccak-hash](https://github.com/EricYChu/php-keccak-hash)

### 注意

*ECDH api is destroyed, check the commit of the latest secp256k1 before the merge of 95e99f196fd08a8b2c236ab99d7e7fec8f6dc78f*

### 作为参考的Dockerfile
- [Dockerfile](Dockerfile)

## 安装

#### 从GitHub安装

* git clone https://github.com/yuanbenio/universe-php-sdk.git
* cd universe-php-sdk 
* composer install

#### 从composer

* composer require yuanbenio/universe-php-sdk

## 原本链 API 返回码

|code|describe|
|---|---|
|ok|Success|
|3001|Invalid parameter|
|3002|Empty parameter|
|3003|Record does not exist|
|3004|Record already exists|
|3005|Permission denied: please register the public key first|
|3006|Permission denied: please contract Yuanben chain support|
|3007|Incorrect data|
|3009|Data storage fail|
|3010|Data not on YuanBen chain |
|3011|block information is empty|
|3012|Query fail|
|3020|Signature verification fail|
|3023|Parameter verification fail|
|3021|Invalid public key|
|3022|License's parameters are empty|
|4001|Error connecting to redis server|
|4002|Error connecting to the first-level node|
|4003|Broadcast transaction fail|
|4004|ABCI query fail|
|4005|Redis handling error|
|5000|Unknown error|

## Data flow diagram
![Data-flow](https://github.com/yuanbenio/universe-php-sdk/blob/master/resources/data-flow.png)


## API Document

> TestNet address: https://testnet.yuanbenlian.com

### Metadata Introduction

| name           | type    |must| comment              |source|
| -------------- | ------- | ----|---------------------|------|
| type           | string  | Y |eg:image,article,audio,video,custom,private |user-defined|
| language       | string  | Y |'zh-CN',                                    |default:zh-CN,user-defined|
| title          | string  | N |title                                       |user-defined|
| signature      | string  | Y |sign by secp256k1                           |generate by system|
| abstract       | string  | N |Content summary                             |default:content[:200],user-defined|
| category       | string  | N |eg:"news"                                   |user-defined，if there is content, the system will add five more|
| dna            | string  | Y |metadata dna                                |generate by system|
| parent_dna     | string  | N |-                                           |user-defined,link an other metadata|
| block_hash     | string  | Y |block_hash on YuanBen chain                 |user-defined|
| block_height   | string  | Y |block_hash corresponding block_height       |user-defined|
| created        | integer | Y |timestamp, eg:1506302092                    |generate by system|
| content_hash   | string  | Y |Keccak256(content)                          |default:Keccak256(content),user-defined|
| extra          | TreeMap<String, Object>  | N | user-defined content       |user-defined|
| license        | Metadata.License  | Y |                                 |user-defined|
| license.type   | string  | Y |the type of license                         |user-defined|
| license.parameters | TreeMap<String, Object>  | N | the parameters of license   |user-defined|
| source         | string  | N |source link.                                |user-defined|
| data           | TreeMap<String, Object>  | N |extension data of the type |user-defined|


### Quick Start
* Note: If you want to use the YuanBenLian OPENAPI, please contact the YuanBenLian owner to register your public key to the YuanBenLian.
* Note: the API configuration needs to be initialized before using the API method
* Note: If there is an integer used in metadata, please convert to string

### Example

**create private key**

```php
// if you don't have private key,You can use this method to generate a private key
// generate private key
$privateKey = \YBL\Kernel\Eth\Key::createPrivateKey();
echo $privateKey . PHP_EOL;

// init key
\YBL\Kernel\Eth\Key::init($privateKey);
$private_key = \YBL\Kernel\Eth\Key::getPrivateKey()->getHex();

// if you use production env,You can contact the YuanBenLian owner to add the public key to the authorized public key.
$public_key = \YBL\Kernel\Eth\Key::getPublicKey()->getHex();
```

**Authorized secondary account**

```php
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

```

**publish article**
```php
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

echo "signature : ".$signature . PHP_EOL;

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
```

[more examples](https://github.com/yuanbenio/universe-php-sdk/tree/master/examples) 