<?php

namespace qtismtest\data\content;

use qtism\common\dom\SerializableDomDocument;
use qtism\data\content\Math;
use qtismtest\QtiSmTestCase;
use RuntimeException;


/**
 * Class MathTest
 */
class MathTest extends QtiSmTestCase
{
    public function testMalformedXml(): void
    {
        $xml = '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML"></m:mathS>';
        $math = new Math($xml);

        $this->expectException(RuntimeException::class);
        $dom = $math->getXml();
    }

    public function testWrongNamespace(): void
    {
        $xml = '<m:math xmlns:m="http://www.w3.org/1998/Math/YogourtML"></m:math>';
        $math = new Math($xml);

        $this->expectException(RuntimeException::class);
        $dom = $math->getXml();
    }

    public function testCorrect(): void
    {
        $xml = '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML"></m:math>';
        $math = new Math($xml);
        $this::assertInstanceOf(SerializableDomDocument::class, $math->getXml());
    }
}
