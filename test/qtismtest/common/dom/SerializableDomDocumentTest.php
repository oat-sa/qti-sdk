<?php

namespace qtismtest\common\dom;

use qtism\common\dom\SerializableDomDocument;
use qtismtest\QtiSmTestCase;

/**
 * Class VersionTest
 */
class SerializableDomDocumentTest extends QtiSmTestCase
{
    public function testSerialization()
    {
        $ser = serialize($this->getSerializableDomDocument());
        $dom = unserialize($ser);

        $this::assertEquals('http://www.imsglobal.org/xsd/imsqti_v2p2', $dom->documentElement->namespaceURI);
    }


    public function testAccessingProperty()
    {
        $xmlVersion = '1.0';
        $dom = $this->getSerializableDomDocument($xmlVersion);

        $this->assertNotEmpty($dom->xmlVersion);
        $this->assertEquals($xmlVersion, $dom->xmlVersion);
    }

    public function testAccessingInexistentProperty()
    {
        $dom = $this->getSerializableDomDocument();
        $property = 'test';

        $this->expectError();

        $dom->$property;
    }

    public function testSettingVirtualPropertyToDom()
    {
        $xmlVersion = '1.0';
        $dom = $this->getSerializableDomDocument($xmlVersion);

        $this->assertEquals($xmlVersion, $dom->xmlVersion);

        $dom->xmlVersion = '1.1';
        $this->assertEquals('1.1', $dom->xmlVersion);
    }

    public function testCheckingIfPropertyExists()
    {
        $dom = $this->getSerializableDomDocument();

        $this->assertTrue(isset($dom->xmlVersion));
    }

    public function testCallingVirtualMethods()
    {
        $dom = $this->getSerializableDomDocument();

        $this->assertNotEmpty($dom->saveXML());
        $this->assertNotEmpty((string)$dom);
    }

    public function testCallingNotExistedVirtualMethods()
    {
        $dom = $this->getSerializableDomDocument();
        $method = 'saveXML2';

        $this->expectError();

        $dom->$method();
    }

    private function getSerializableDomDocument(string $version = '1.0', string $encoding = 'UTF-8'): SerializableDomDocument
    {
        $dom = new SerializableDomDocument($version, $encoding);
        $dom->load(self::samplesDir() . 'ims/items/2_2_1/choice.xml');

        return $dom;
    }
}
