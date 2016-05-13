<?php

use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\Shape;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiDatatype;
use qtism\data\storage\php\marshalling\PhpQtiDatatypeMarshaller;

require_once (dirname(__FILE__) . '/../../../../../QtiSmPhpMarshallerTestCase.php');

class PhpQtiDatatypeMarshallerTest extends QtiSmPhpMarshallerTestCase {
	
    /**
     * 
     * @dataProvider marshallDataProvider
     * @param string $expectedInStream
     * @param QtiDatatype $qtiDatatype
     */
    public function testMarshall($expectedInStream, QtiDatatype $qtiDatatype) {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, $qtiDatatype);
        $marshaller->marshall();
        
        $this->assertEquals($expectedInStream, $this->getStream()->getBinary());
    }
    
    public function testMarshallWrongDataType() {
        $this->setExpectedException('\\InvalidArgumentException');
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpQtiDatatypeMarshaller($ctx, new stdClass());
    }

    public function marshallDataProvider() {
        return array(
            array("\$array_0 = array(10, 10, 5);\n\$coords_0 = new qtism\\common\\datatypes\\Coords(2, \$array_0);\n", new QtiCoords(Shape::CIRCLE, array(10, 10, 5))),
            array("\$pair_0 = new qtism\\common\\datatypes\\Pair(\"A\", \"B\");\n", new QtiPair('A', 'B')),
            array("\$directedpair_0 = new qtism\\common\\datatypes\\DirectedPair(\"A\", \"B\");\n", new QtiDirectedPair('A', 'B')),
            array("\$duration_0 = new qtism\\common\\datatypes\\Duration(\"PT30S\");\n", new QtiDuration("PT30S")),
            array("\$point_0 = new qtism\\common\\datatypes\\Point(10, 15);\n", new QtiPoint(10, 15))
        );
    }
}
