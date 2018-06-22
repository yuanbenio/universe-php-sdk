<?php

namespace YuanBen\YuanBenLian;

class Secp256k1
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
        $compressed = true;
        secp256k1_ec_pubkey_serialize($this->_getContext(), $serialized, $pubkey, $compressed);
        return bin2hex($serialized);
    }

    /**
     * @param $content
     * @return string
     */
    public function getContentHash($content)
    {
        return hash("sha256", $content);
    }

    /**
     * 可恢复的
     * @param $privateKey
     * @param $msg32
     * @return string
     * @throws \Exception
     */
    public function getRecoverableSign($privateKey, $msg32)
    {
        $signature = '';
        if (secp256k1_ecdsa_sign_recoverable($this->_getContext(), $signature, $msg32, $privateKey) != 1) {
            throw new \Exception("Failed to create recoverable signature");
        }
        $recId = 0;
        $output = '';
        secp256k1_ecdsa_recoverable_signature_serialize_compact($this->_getContext(), $signature, $output, $recId);
        $signatureNative = bin2hex($output) . dechex($recId + 27);
        return $signatureNative;
    }

    /**
     * @param $privateKey
     * @param $msg32
     * @return string
     * @throws \Exception
     */
    public function getSign($privateKey, $msg32)
    {
        $signature = '';
        if (1 !== secp256k1_ecdsa_sign($this->_getContext(), $signature, $msg32, $privateKey)) {
            throw new \Exception("Failed to create signature");
        }
        $serialized = '';
        secp256k1_ecdsa_signature_serialize_der($this->_getContext(), $serialized, $signature);
        $sign = bin2hex($serialized);
        return $sign;
    }

    /**
     * @param $publicKey
     * @param $sign
     * @param $msg32
     * @return bool
     */
    public function verifyRecoverableSign($publicKey, $sign, $msg32)
    {
        $context = secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);
        $recId = hexdec(substr($sign, 128, 2)) - 27;
        $siginput = hex2bin(substr($sign, 0, 128));
        $signature = '';
        secp256k1_ecdsa_recoverable_signature_parse_compact($context, $signature, $siginput, $recId);
        $pubKey = '';
        secp256k1_ecdsa_recover($context, $pubKey, $signature, $msg32);
        $serialized = '';
        $compress = true;
        secp256k1_ec_pubkey_serialize($context, $serialized, $pubKey, $compress);
        $pubkeyNative = bin2hex($serialized);
        if (strcmp($publicKey, $pubkeyNative) == 0) {
            //如果本地计算的公钥和服务器返回的公钥一致就说明签名正确
            return true;
        }
        return false;
    }

    /**
     * @param $publicKey
     * @param $sign
     * @param $msg32
     * @return bool
     * @throws \Exception
     */
    public function verifySign($publicKey, $sign, $msg32)
    {
        $publicKeyRaw = hex2bin($publicKey);
        $signatureRaw = hex2bin($sign);
        // Load up the public key from its bytes (into $publicKey):
        /** @var resource $publicKey */
        $pubKey = '';
        if (1 !== secp256k1_ec_pubkey_parse($this->_getContext(), $pubKey, $publicKeyRaw)) {
            throw new \Exception("Failed to parse public key");
        }
        // Load up the signature from its bytes (into $signature):
        /** @var resource $signature */
        $signature = '';
        if (1 !== secp256k1_ecdsa_signature_parse_der($this->_getContext(), $signature, $signatureRaw)) {
            throw new \Exception("Failed to parse DER signature");
        }
        // Verify:
        $result = secp256k1_ecdsa_verify($this->_getContext(), $signature, $msg32, $pubKey);
        if ($result == 1) {
            return true;
        } else {
            return false;
        }
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

    public function _toBinary32($str)
    {
        return str_pad(pack("H*", (string)$str), 32, chr(0), STR_PAD_LEFT);
    }

    public function _base36Encode($strNum)
    {
        $base36 = gmp_strval(gmp_init($strNum, 16), 36);
        return strtoupper($base36);
    }

    /**
     * @return string
     */
    public function _generateId()
    {
        return uniqid('php_', true);
    }
}