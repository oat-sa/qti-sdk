<?php

use qtism\data\storage\php\marshalling\PhpScalarMarshaller;
use qtism\data\storage\php\marshalling\PhpArrayMarshaller;

require_once (dirname(__FILE__) . '/../../../../../QtiSmPhpMarshallerTestCase.php');

class PhpArrayMarshallerTest extends QtiSmPhpMarshallerTestCase {
	
    public function testEmptyArray() {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpArrayMarshaller($ctx, array());
        $marshaller->marshall();
        
        $this->assertEquals("\$array_0 = array();\n", $this->getStream()->getBinary());
    }
    
    public function testIntegerArray() {
        $ctx = $this->createMarshallingContext();
        $scalarMarshaller = new PhpScalarMarshaller($ctx, 0);
        $scalarMarshaller->marshall();
        $scalarMarshaller->setToMarshall(1);
        $scalarMarshaller->marshall();
        $scalarMarshaller->setToMarshall(2);
        $scalarMarshaller->marshall();
        
        $arrayMarshaller = new PhpArrayMarshaller($ctx, array(0, 1, 2));
        $arrayMarshaller->marshall();
        
        $expected = "\$integer_0 = 0;\n";
        $expected.= "\$integer_1 = 1;\n";
        $expected.= "\$integer_2 = 2;\n";
        $expected.= "\$array_0 = array(\$integer_0, \$integer_1, \$integer_2);\n";
        
        $this->assertEquals($expected, $this->getStream()->getBinary());
    }
}