<?php

declare(strict_types=1);

namespace qtismtest\common\dom;

use qtism\common\dom\SerializableDomDocument;
use qtismtest\QtiSmTestCase;
use DOMDocument;

/**
 * Class VersionTest
 */
class SerializableDomDocumentTest extends QtiSmTestCase
{
    public function testSerialization(): void
    {
        $ser = serialize($this->getSerializableDomDocument());
        $dom = unserialize($ser);

        $this::assertEquals('http://www.imsglobal.org/xsd/imsqti_v2p2', $dom->documentElement->namespaceURI);
    }


    public function testAccessingProperty(): void
    {
        $xmlVersion = '1.0';
        $dom = $this->getSerializableDomDocument($xmlVersion);

        $this->assertNotEmpty($dom->xmlVersion);
        $this->assertEquals($xmlVersion, $dom->xmlVersion);
    }

    public function testAccessingInexistentProperty(): void
    {
        $dom = $this->getSerializableDomDocument();
        $property = 'test';

        $this->expectError();
        $this->expectErrorMessage(
            sprintf('Undefined property: %s::%s', SerializableDomDocument::class, $property)
        );

        $dom->$property;
    }

    public function testSettingVirtualPropertyToDom(): void
    {
        $xmlVersion = '1.0';
        $dom = $this->getSerializableDomDocument($xmlVersion);

        $this->assertEquals($xmlVersion, $dom->xmlVersion);

        $dom->xmlVersion = '1.1';
        $this->assertEquals('1.1', $dom->xmlVersion);
    }

    public function testCheckingIfPropertyExists(): void
    {
        $dom = $this->getSerializableDomDocument();

        $this->assertTrue(isset($dom->xmlVersion));
    }

    public function testCallingVirtualMethods(): void
    {
        $dom = $this->getSerializableDomDocument();

        $this->assertNotEmpty($dom->saveXML());
        $this->assertNotEmpty((string)$dom);
    }

    public function testCallingNotExistedVirtualMethods(): void
    {
        $dom = $this->getSerializableDomDocument();
        $method = 'saveXML2';

        $this->expectError();
        $this->expectErrorMessage(
            sprintf('Call to undefined method %s::%s()', SerializableDomDocument::class, $method)
        );

        $dom->$method();
    }

    public function testCheckThatUnsetIsWorkingSimilarToRealDomObject(): void
    {
        $serializableDOM = $this->getSerializableDomDocument();
        $coreDom = new DOMDocument($serializableDOM->xmlVersion, $serializableDOM->encoding);

        $this->assertEquals($coreDom->xmlVersion, $serializableDOM->version);
        $this->assertEquals($coreDom->encoding, $serializableDOM->encoding);

        unset($coreDom->xmlVersion);
        unset($coreDom->encoding);

        unset($serializableDOM->xmlVersion);
        unset($serializableDOM->encoding);

        $this->assertEquals($coreDom->xmlVersion, $serializableDOM->version);
        $this->assertEquals($coreDom->encoding, $serializableDOM->encoding);
    }

    private function getSerializableDomDocument(string $version = '1.0', string $encoding = 'UTF-8'): SerializableDomDocument
    {
        $dom = new SerializableDomDocument($version, $encoding);
        $dom->load(self::samplesDir() . 'ims/items/2_2_1/choice.xml');

        return $dom;
    }
}
