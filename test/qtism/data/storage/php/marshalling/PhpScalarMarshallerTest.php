<?php

use qtism\data\storage\php\marshalling\PhpScalarMarshaller;

require_once (dirname(__FILE__) . '/../../../../../QtiSmPhpMarshallerTestCase.php');

class PhpScalarMarshallerTest extends QtiSmPhpMarshallerTestCase {
	
    /**
     * 
     * @dataProvider marshallDataProvider
     * @param string $expectedInStream
     * @param mixed $scalar
     */
    public function testMarshall($expectedInStream, $scalar) {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpScalarMarshaller($ctx, $scalar);
        $marshaller->marshall();
        
        $this->assertEquals($expectedInStream, $this->getStream()->getBinary());
    }
    
    public function testMarshallWrongDataType() {
        $this->setExpectedException('\\InvalidArgumentException');
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpScalarMarshaller($ctx, new stdClass());
    }

    public function marshallDataProvider() {
        return array(
            array("null", null),
            array("10", 10),
            array("10.44", 10.44),
            array("\"\"", ''),
            array("\"Hello!\"", "Hello!"),
            array("true", true),
            array("false", false)    
        );
    }
}