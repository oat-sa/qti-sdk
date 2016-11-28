<?php

use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiShape;
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
            array("\$array_0 = array(10, 10, 5);\n\$qticoords_0 = new qtism\\common\\datatypes\\QtiCoords(2, \$array_0);\n", new QtiCoords(QtiShape::CIRCLE, array(10, 10, 5))),
            array("\$qtipair_0 = new qtism\\common\\datatypes\\QtiPair(\"A\", \"B\");\n", new QtiPair('A', 'B')),
            array("\$qtidirectedpair_0 = new qtism\\common\\datatypes\\QtiDirectedPair(\"A\", \"B\");\n", new QtiDirectedPair('A', 'B')),
            array("\$qtiduration_0 = new qtism\\common\\datatypes\\QtiDuration(\"PT30S\");\n", new QtiDuration("PT30S")),
            array("\$qtipoint_0 = new qtism\\common\\datatypes\\QtiPoint(10, 15);\n", new QtiPoint(10, 15))
        );
    }
}
