<?php

namespace YuanBen;

class BlockHandle
{
    /**
     * @var resource
     */
    private $context;

    /**
     * 生成私钥
     * @return string 32 bytes
     */
    public function generatePrivateKey()
    {
        do {
            $key = \openssl_random_pseudo_bytes(32);
        } while (secp256k1_ec_seckey_verify(self::_getContext(), $key) == 0);
        return $key;
    }

    /**
     * 生成公钥
     * @param null $secretKey
     * @return string
     */
    public function generatePublicKey($secretKey = null)
    {
        isset($secretKey) ?: $secretKey = $this->generatePrivateKey();
        $secretKey = str_pad($secretKey, 32, chr(0), STR_PAD_LEFT);;
        $pubkey = '';
        \secp256k1_ec_pubkey_create($this->_getContext(), $pubkey, $secretKey);
        $serialized = '';
        $fcompressed = true;
        secp256k1_ec_pubkey_serialize($this->_getContext(), $serialized, $pubkey, $fcompressed);
        return bin2hex($serialized);
    }

    /**
     * @param $content
     * @return string
     */
    public function getContentHash($content)
    {
        return Keccak::hash($content, 256);
    }

    /**
     * 生成dna信息
     * @param $sign
     * @return string
     */
    public function generateDNA($sign)
    {
        $sign32 = $this->_toBinary32($sign);
        $msg = $this->getContentHash($sign32);
        return $this->_base36Encode($msg);
    }

    /**
     * 补全metadata
     * @param $priKey
     * @param $metadataArr
     * @return mixed
     */
    public function supplyMetadataArr($priKey, $metadataArr)
    {
        if (!isset($metadataArr['pubkey']) || empty($metadataArr['pubkey'])) {
            $metadataArr['pubkey'] = $this->generatePublicKey($priKey);
        }
        if (!isset($metadataArr['id']) || empty($metadataArr['id'])) {
            $metadataArr['id'] = $this->_generateId();
        }
        return $metadataArr;
    }

    /**
     * @param $priKey
     * @param $metadataArr
     * @return string
     * @throws \Exception
     */
    public function getSign($priKey, $metadataArr)
    {
        $msg = $this->_handleMetadataArr($metadataArr);
        $msg = $this->_toBinary32($msg);
        $signature = '';
        if (secp256k1_ecdsa_sign_recoverable($this->_getContext(), $signature, $msg, $priKey) != 1) {
            throw new \Exception("Failed to create recoverable signature");
        }
        $recId = 0;
        $output = '';
        secp256k1_ecdsa_recoverable_signature_serialize_compact($this->_getContext(), $signature, $output, $recId);
        $signatureNative = bin2hex($output) . dechex($recId + 27);
        return $signatureNative;
    }

    public function verifySign($publicKey, $sign, $msg)
    {
        $context = secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);
        $msgByte = $this->_toBinary32($msg);
        $recId = hexdec(substr($sign, 128, 2)) - 27;
        $siginput = hex2bin(substr($sign, 0, 128));
        $signature = '';
        secp256k1_ecdsa_recoverable_signature_parse_compact($context, $signature, $siginput, $recId);
        $pubKey = '';
        secp256k1_ecdsa_recover($context, $pubKey, $signature, $msgByte);
        $serialized = '';
        $compress = 1;
        secp256k1_ec_pubkey_serialize($context, $serialized, $pubKey, $compress);
        $pubkeyNative = bin2hex($serialized);
        if (strcmp($publicKey, $pubkeyNative) == 0) {
            //如果本地计算的公钥和服务器返回的公钥一致就说明签名正确
            return true;
        }
        return false;
    }

    public function validateMetaData(array $metaData)
    {
        //TODO 校验
    }

    /**
     * @return resource
     */
    private function _getContext()
    {
        if ($this->context == null) {
            $this->context = \secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);
        }
        return $this->context;
    }

    public function _pack($string)
    {
        if (strlen($string) % 2 !== 0) {
            $string = '0' . $string;
        }
        return pack("H*", $string);
    }

    public function _unpack($str)
    {
        return unpack("H*", $str)[1];
    }

    private function _toBinary32($str)
    {
        return str_pad(pack("H*", (string)$str), 32, chr(0), STR_PAD_LEFT);
    }

    private function _base36Encode($strNum)
    {
        $base36 = gmp_strval(gmp_init($strNum, 16), 36);
        return strtoupper($base36);
    }

    private function _ksort($arr)
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

    /**
     * @param $metadataArr
     * @return string
     */
    public function _handleMetadataArr($metadataArr)
    {
        $removeFields = ['signature', 'dna', 'content'];
        foreach ($removeFields as $field) {
            if (isset($metadataArr[$field])) unset($metadataArr[$field]);
        }
        $metadataArr = $this->_ksort($metadataArr);
        $metadataJson = json_encode($metadataArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
        return $this->getContentHash($metadataJson);
    }

    /**
     * @return string
     */
    private function _generateId()
    {
        return uniqid('php_', true);
    }
}