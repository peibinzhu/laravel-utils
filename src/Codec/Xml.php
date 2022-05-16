<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils\Codec;

use InvalidArgumentException;
use SimpleXMLElement;

class Xml
{
    public static function toXml($data, $parentNode = null, $root = 'root'): string
    {
        $data = (array)$data;
        if ($parentNode === null) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?>' . "<{$root}></{$root}>");
        } else {
            $xml = $parentNode;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::toXml($value, $xml->addChild($key));
            } elseif (is_numeric($key)) {
                $xml->addChild('item' . $key, (string)$value);
            } else {
                $xml->addChild($key, (string)$value);
            }
        }
        return trim($xml->asXML());
    }

    public static function toArray($xml): array
    {
        // For PHP 8.0, libxml_disable_entity_loader() has been deprecated.
        // As libxml 2.9.0 is now required, external entity loading is guaranteed to be disabled by default.
        // And this function is no longer needed to protect against XXE attacks, unless the (still vulnerable). LIBXML_NOENT is used.
        // In that case, it is recommended to refactor the code using libxml_set_external_entity_loader() to suppress loading of external entities.
        if (\PHP_VERSION_ID < 80000) {
            $disableLibxmlEntityLoader = libxml_disable_entity_loader(true);
            $respObject = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);
            libxml_disable_entity_loader($disableLibxmlEntityLoader);
        } else {
            $respObject = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);
        }

        if ($respObject === false) {
            throw new InvalidArgumentException('Syntax error.');
        }

        return json_decode(json_encode($respObject), true);
    }
}
