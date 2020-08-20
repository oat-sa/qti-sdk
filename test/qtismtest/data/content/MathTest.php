<?php

namespace qtismtest\data\content;

use DOMDocument;
use qtism\data\content\Math;
use qtismtest\QtiSmTestCase;
use RuntimeException;

class MathTest extends QtiSmTestCase
{
    public function testMalformedXml()
    {
        $xml = '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML"></m:mathS>';
        $math = new Math($xml);

        $this->expectException(RuntimeException::class);
        $dom = $math->getXml();
    }

    public function testWrongNamespace()
    {
        $xml = '<m:math xmlns:m="http://www.w3.org/1998/Math/YogourtML"></m:math>';
        $math = new Math($xml);

        $this->expectException(RuntimeException::class);
        $dom = $math->getXml();
    }

    public function testCorrect()
    {
        $xml = '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML"></m:math>';
        $math = new Math($xml);
        $this->assertInstanceOf(DOMDocument::class, $math->getXml());
    }
}
