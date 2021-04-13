<?php

namespace qtismtest\data\storage\php\marshalling;

use InvalidArgumentException;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiDatatype;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiShape;
use qtism\data\storage\php\marshalling\PhpMarshallingException;
use qtism\data\storage\php\marshalling\PhpQtiDatatypeMarshaller;
use qtismtest\QtiSmPhpMarshallerTestCase;
use stdClass;

/**
 * Class PhpQtiDatatypeMarshallerTest
 */
class PhpQtiDatatypeMarshallerTest extends QtiSmPhpMarshallerTestCase
{
    /**
     * @dataProvider marshallDataProvider
     * @param string $expectedInStream
     * @param QtiDatatype $qtiDatatype
     * @throws PhpMarshallingException
     */
    public function testMarshall($expectedInStream, QtiDatatype $qtiDatatype)
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, $qtiDatatype);
        $marshaller->marshall();

        $this::assertEquals($expectedInStream, $this->getStream()->getBinary());
    }

    public function testMarshallWrongDataType()
    {
        $this->expectException(InvalidArgumentException::class);
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, new stdClass());
    }

    /**
     * @return array
     */
    public function marshallDataProvider()
    {
        return [
            ['$array_0 = array(10, 10, 5);' . "\n" . '$qticoords_0 = new ' . QtiCoords::class . '(2, $array_0);' . "\n", new QtiCoords(QtiShape::CIRCLE, [10, 10, 5])],
            ['$qtipair_0 = new ' . QtiPair::class . '("A", "B");' . "\n", new QtiPair('A', 'B')],
            ['$qtidirectedpair_0 = new ' . QtiDirectedPair::class . '("A", "B");' . "\n", new QtiDirectedPair('A', 'B')],
            ['$qtiduration_0 = new ' . QtiDuration::class . '("PT30S");' . "\n", new QtiDuration('PT30S')],
            ['$qtipoint_0 = new ' . QtiPoint::class . '(10, 15);' . "\n", new QtiPoint(10, 15)],
            ['$qtiidentifier_0 = new ' . QtiIdentifier::class . '("my_id");' . "\n", new QtiIdentifier('my_id')],
        ];
    }

    public function testMarshallUnsupported()
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, new QtiInteger(1337));

        $this->expectException(PhpMarshallingException::class);
        $this->expectExceptionMessage("Cannot deal with QtiDatatype '" . QtiInteger::class . "'");

        $marshaller->marshall();
    }

    public function testMarshallCoordsClosedStream()
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, new QtiCoords(QtiShape::CIRCLE, [10, 10, 5]));

        $this->getStream()->close();
        $this->expectException(PhpMarshallingException::class);
        $this->expectExceptionMessage('An error occurred while marshalling a QtiDatatype object.');

        $marshaller->marshall();
    }

    public function testMarshallPairClosedStream()
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, new QtiPair('A', 'B'));

        $this->getStream()->close();
        $this->expectException(PhpMarshallingException::class);
        $this->expectExceptionMessage('An error occurred while marshalling a QtiDatatype object.');

        $marshaller->marshall();
    }

    public function testMarshallDurationClosedStream()
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, new QtiDuration('PT30S'));

        $this->getStream()->close();
        $this->expectException(PhpMarshallingException::class);
        $this->expectExceptionMessage('An error occurred while marshalling a QtiDatatype object.');

        $marshaller->marshall();
    }

    public function testMarshallIdentifierClosedStream()
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, new QtiIdentifier('MYID'));

        $this->getStream()->close();
        $this->expectException(PhpMarshallingException::class);
        $this->expectExceptionMessage('An error occurred while marshalling a QtiDatatype object.');

        $marshaller->marshall();
    }

    public function testMarshallPointClosedStream()
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, new QtiPoint(9, 9));

        $this->getStream()->close();
        $this->expectException(PhpMarshallingException::class);
        $this->expectExceptionMessage('An error occurred while marshalling a QtiDatatype object.');

        $marshaller->marshall();
    }
}
