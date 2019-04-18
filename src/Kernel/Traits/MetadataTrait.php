<?php

namespace YBL\Kernel\Traits;

use YBL\Kernel\Crypto\Keccak;
use YBL\Kernel\Crypto\Signature;
use YBL\Kernel\Support\Arr;
use YBL\Kernel\Types\Byte;

/**
 * Trait Metadata
 * @package YBL\Kernel\Traits
 */
trait MetadataTrait
{
    /**
     * @var Byte
     */
    private $privateKey;

    /**
     * @var Byte
     */
    private $publicKey;

    /**
     * @param array $data
     * @return array
     */
    protected function removeFields(array $data): array
    {
        $removeFields = ['signature', 'dna', 'content'];
        foreach ($removeFields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        return $data;
    }

    /**
     * @param array $array
     * @return array
     */
    protected function filterEmptyValue(array $array)
    {
        foreach ($array as $k => $v) {
            if ($v === '' || $v === null) {
                unset($array[$k]);
            } elseif (is_array($v)) {
                $array[$k] = self::filterEmptyValue($array[$k]);
            }
        }
        return $array;
    }

    /**
     * @param array $data
     * @return array
     */
    public function buildMetadata(array $data): array
    {
        $metadata = $this->removeFields($this->filterEmptyValue($data));
        // padding public key
        $metadata['pubkey'] = $this->publicKey->getHex();
        $metadata = Arr::ksort($metadata);

        return $metadata;
    }

    /**
     * @param array $metadata
     * @return string
     * @throws \Exception
     */
    public function sign(array $metadata): string
    {
        $metadataJson = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_AMP);
        // JSON_HEX_TAG will convert "<",">" to "\u003C","\u003E"
        // yuanbenlian need "<",">" convert to \u003c","\u003e"
        $metadataJson = str_replace(["<", ">"], ["\u003c", "\u003e"], $metadataJson);

        $signature = Signature::sign(Byte::initWithHex(Keccak::hash($metadataJson, 256)), $this->privateKey)->getHex();

        return $signature;
    }

    /**
     * @param $metadata
     * @param string $signature
     * @return array
     */
    public function setSignature($metadata, string $signature): array
    {
        $metadata["signature"] = $signature;

        return $metadata;
    }

    /**
     * @param $sign
     * @return string
     */
    public function generateDNA($sign)
    {
        $msg = Keccak::hash(pack("H*", $sign), 256);

        $count = $this->searchHexLeftIsZeroCount($msg);

        if ($count > 0) {
            $base36 = $this->_base36Encode($msg);
            return str_pad($base36, strlen($base36) + $count, "0", STR_PAD_LEFT);
        }
        return $this->_base36Encode($msg);
    }

    private function _base36Encode($str)
    {
        $base36 = gmp_strval(gmp_init($str, 16), 36);

        return strtoupper($base36);
    }

    /**
     * @param $str
     * @return int
     */
    private function searchHexLeftIsZeroCount($str)
    {
        $count = 0;
        for ($i = 0; $i < strlen($str); $i = $i + 2) {
            if ($str[$i] == '0' && $str[$i + 1] == '0') {
                $count++;
            } else {
                break;
            }
        }

        return $count;
    }
}