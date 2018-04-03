<?php
namespace library;

use GuzzleHttp\Client;
use Symfony\Component\Cache\Simple\FilesystemCache as Cache;
use Web3p\Secp256k1\Secp256k1;
use Web3p\Secp256k1\Serializer\HexSignatureSerializer;

class YuanbenHandle
{
    public static function handle($imageUrl, $title, $category)
    {
        $metadataJson = self::_generateMetadataArr($imageUrl, $title, $category);
        list($code, $dna) = self::_createMetadata($metadataJson);

        return $dna;
    }

    /**
     * 生成Metadata数据
     * @param $imageUrl
     * @param $title
     * @param $category
     * @author mingwang
     * @date 2018.3.30
     * @return string
     */
    private static function _generateMetadataArr($imageUrl, $title, $category)
    {
        $privateKey = Yuanben::generatePrivateKey();
        $contentHash = Yuanben::getContentHash($imageUrl, Yuanben::TYPE_IMAGE);
        $data = Yuanben::getBlockMsg();
        $blockHash = $data['latest_block_hash'];
        $blockHeight = $data['latest_block_height'];
        $metadataArr = [
            'type' => 'image',
            'title' => $title,
            'category' => $category,

            // 特殊字段
            'created' => (string)time(),
            'content_hash' => $contentHash,
            'license' => [
                'type' => 'cc',
                'parameters' => [
                    'b' => '2',
                ],
            ],
            'data' => [
                'thumb' => $imageUrl,
                'original' => $imageUrl,
            ],
            'block_hash' => $blockHash,
            'block_height' => (string)$blockHeight,
        ];
        $metadataArr = Yuanben::supplyMetadataArr($privateKey, $metadataArr);

        $sign = Yuanben::getSign($privateKey, $metadataArr);
        $metadataArr['signature'] = $sign;
        $dna = Yuanben::generateDNA($sign);
        $metadataArr['dna'] = $dna;

        return json_encode($metadataArr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    /**
     * 创建metadata数据
     * @author mingwang
     * @date 2018.3.30
     */
    public static function _createMetadata($metadataJson)
    {
        $client = new Client();
        $url = 'http://119.23.22.129:8080/v1/metadata?async=true';
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $metadataJson,
        ]);

        $code = $response->getStatusCode();
        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);
        $dna = isset($data['data']['dna']) ? $data['data']['dna']: null;

        return [$code, $dna];
    }
}

class Yuanben
{
    const TYPE_IMAGE = 'image';
    const TYPE_TEXT = 'text';

    private static $context;

    /**
     * 生成私钥
     * @author mingwang
     * @date 2018.3.30
     * @return string
     */
    public static function generatePrivateKey()
    {
        do {
            $key = \openssl_random_pseudo_bytes(32);
        } while (secp256k1_ec_seckey_verify(self::_getContext(), $key) == 0);

        $key = self::_unpack($key);
        return $key;
    }

    /**
     * 生成公钥
     * @param null $secretKey
     * @author mingwang
     * @date 2018.3.30
     * @return string
     */
    public static function generatePublicKey($secretKey = null)
    {
        isset($secretKey) ?:$secretKey = self::getPrivateKey();
        $secretKey = self::_toBinary32($secretKey);

        $pubkey = '';
        \secp256k1_ec_pubkey_create(self::_getContext(), $pubkey, $secretKey);

        $serialized = '';
        $fcompressed = 1;
        secp256k1_ec_pubkey_serialize(self::_getContext(), $serialized, $pubkey, $fcompressed);
        return bin2hex($serialized);
    }

    /**
     * 获取内容hash - 支持text和image url
     * @param $data
     * @param string $type
     * @author mingwang
     * @date 2018.3.30
     * @return null
     */
    public static function getContentHash($data, $type = Yuanben::TYPE_TEXT)
    {
        $content = null;
        switch ($type) {
            case 'text':
                $content = $data;
                break;
            case 'image':
                $content = file_get_contents($data);
                break;
            default:
                return null;
        }
        return \kornrunner\Keccak::hash($content, 256);
    }

    /**
     * 获取block msg
     * @author mingwang
     * @date 2018.3.30
     * @return mixed|null
     */
    public static function getBlockMsg()
    {
        global $blockUrl;
        global $cache;
        $blockUrl = 'http://119.23.22.129:8080/v1/block_hash';
        $cache = new Cache();

        // 从缓存获取
        $key = 'yuanben_block_hash';
        $data = $cache->get($key);
        if (!empty($data)) {
            $data = unserialize($data);
            return $data;
        }

        // 获取
        $client = new \GuzzleHttp\Client();
        $response = $client->get($blockUrl);
        $content = $response->getBody()->getContents();
        $contentArr = json_decode($content, true);
        if (!isset($contentArr['data'])) {
            log_error('#信息#: 获取block hash失败', [
                'response_content' => $content,
            ]);
            return null;
        }
//        log_info('yuanben', "获取最新的block_hash", $contentArr);

        $data = $contentArr['data'];
        $cache->set($key, serialize($data), 1 * 3600);

        return $data;
    }

    /**
     * 生成dna信息
     * @param $sign
     * @author mingwang
     * @date 2018.3.30
     * @return string
     */
    public static function generateDNA($sign)
    {
        $sign32 = self::_toBinary32($sign);
        $msg = self::getContentHash($sign32);

        return self::_base36Encode($msg);
    }

    /**
     * 补全metadata
     * @param $priKey
     * @param $metadataArr
     * @author mingwang
     * @date 2018.3.30
     * @return mixed
     */
    public static function supplyMetadataArr($priKey, $metadataArr)
    {
        if (!isset($metadataArr['pubkey']) || empty($metadataArr['pubkey'])) {
            $metadataArr['pubkey'] = self::generatePublicKey($priKey);
        }
        if (!isset($metadataArr['id']) || empty($metadataArr['id'])) {
            $metadataArr['id'] = self::_generateId();
        }

        return $metadataArr;
    }

    /**
     * 签名
     * @param $priKey
     * @param $metadataArr
     * @author mingwang
     * @date 2018.3.30
     * @return string
     */
    public static function getSign($priKey, $metadataArr)
    {
        $msg = self::_handleMetadataArr($metadataArr);

        $secp256k1 = new Secp256k1();
        $signature = $secp256k1->sign($msg, $priKey);
        $serializer = new HexSignatureSerializer();
        $signatureString = $serializer->serialize($signature);

        return str_pad($signatureString, 130, 0, STR_PAD_RIGHT);
    }

    private static function _getContext()
    {
        if (self::$context == null) {
            self::$context = \secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);
        }
        return self::$context;
    }

    private static function _pack($string)
    {
        if (strlen($string) % 2 !== 0) {
            $string = '0' . $string;
        }
        return pack("H*", $string);
    }

    private static function _unpack($str)
    {
        return unpack("H*", $str)[1];
    }

    private static function _toBinary32($str)
    {
        return str_pad(pack("H*", (string)$str), 32, chr(0), STR_PAD_LEFT);
    }

    private static function _base36Encode($strNum) {
        $base36 = gmp_strval(gmp_init($strNum, 16), 36);
        return strtoupper($base36);
    }

    private static function _ksort($arr)
    {
        // 排序整体数组
        ksort($arr);

        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = self::_ksort($v);
            }
        }

        return $arr;
    }

    private static function _handleMetadataArr($metadataArr)
    {
        $removeFields = ['signature', 'dna', 'content'];
        foreach ($removeFields as $field) {
            if (isset($metadataArr[$field])) unset($metadataArr[$field]);
        }
        $metadataArr = self::_ksort($metadataArr);
        $metadataJson = json_encode($metadataArr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return self::getContentHash($metadataJson);
    }

    private static function _generateId()
    {
        return md5(microtime(true) + rand(1, 10000) + str_random(10));
    }
}