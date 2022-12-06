<?php

namespace qtismtest\data\storage\php\marshalling;

use qtism\common\collections\IntegerCollection;
use qtism\data\storage\php\marshalling\PhpCollectionMarshaller;
use qtism\data\storage\php\marshalling\PhpScalarMarshaller;
use qtismtest\QtiSmPhpMarshallerTestCase;

/**
 * Class PhpCollectionMarshallerTest
 */
class PhpCollectionMarshallerTest extends QtiSmPhpMarshallerTestCase
{
    public function testEmptyCollection(): void
    {
        $collection = new IntegerCollection();
        $marshaller = new PhpCollectionMarshaller($this->createMarshallingContext(), $collection);
        $marshaller->marshall();

        $expected = '$array_0 = array();' . "\n";
        $expected .= '$integercollection_0 = new ' . IntegerCollection::class . '($array_0);' . "\n";

        $this::assertEquals($expected, $this->getStream()->getBinary());
    }

    public function testIntegerCollection(): void
    {
        $collection = new IntegerCollection([10, 11, 12]);
        $ctx = $this->createMarshallingContext();
        $scalarMarshaller = new PhpScalarMarshaller($ctx, $collection[0]);
        $scalarMarshaller->marshall();
        $scalarMarshaller->setToMarshall($collection[1]);
        $scalarMarshaller->marshall();
        $scalarMarshaller->setToMarshall($collection[2]);
        $scalarMarshaller->marshall();

        $collectionMarshaller = new PhpCollectionMarshaller($ctx, $collection);
        $collectionMarshaller->marshall();

        $expected = '$integer_0 = 10;' . "\n";
        $expected .= '$integer_1 = 11;' . "\n";
        $expected .= '$integer_2 = 12;' . "\n";
        $expected .= '$array_0 = array($integer_0, $integer_1, $integer_2);' . "\n";
        $expected .= '$integercollection_0 = new ' . IntegerCollection::class . '($array_0);' . "\n";

        $this::assertEquals($expected, $this->getStream()->getBinary());
    }
}
