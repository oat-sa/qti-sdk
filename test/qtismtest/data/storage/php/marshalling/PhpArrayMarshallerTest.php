<?php

namespace qtismtest\data\storage\php\marshalling;

use qtism\data\storage\php\marshalling\PhpArrayMarshaller;
use qtismtest\QtiSmPhpMarshallerTestCase;

/**
 * Class PhpArrayMarshallerTest
 */
class PhpArrayMarshallerTest extends QtiSmPhpMarshallerTestCase
{
    public function testEmptyArray()
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpArrayMarshaller($ctx, []);
        $marshaller->marshall();

        $this::assertEquals("\$array_0 = array();\n", $this->getStream()->getBinary());
    }

    public function testIntegerArray()
    {
        $ctx = $this->createMarshallingContext();
        $arrayMarshaller = new PhpArrayMarshaller($ctx, [0, 1, 2]);
        $arrayMarshaller->marshall();

        $expected = "\$array_0 = array(0, 1, 2);\n";
        $this::assertEquals($expected, $this->getStream()->getBinary());
    }
}
