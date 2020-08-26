<?php

namespace qtismtest\common\dom;

use qtism\common\dom\SerializableDomDocument;
use qtismtest\QtiSmTestCase;

/**
 * Class VersionTest
 */
class VersionTest extends QtiSmTestCase
{
    public function testSerialization()
    {
        $dom = new SerializableDomDocument('1.0', 'UTF-8');
        $dom->load(self::samplesDir() . 'ims/items/2_2/choice.xml');

        $ser = serialize($dom);
        $dom = unserialize($ser);

        $this->assertEquals('http://www.imsglobal.org/xsd/imsqti_v2p2', $dom->documentElement->namespaceURI);
    }
}
