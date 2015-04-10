<?php
namespace qtismtest\common\datatypes;

use qtism\common\datatypes\Boolean;
use qtismtest\QtiSmTestCase;

class BooleanTest extends QtiSmTestCase {
    
    public function testWrongValue() {
        $this->setExpectedException('\\InvalidArgumentException');
        $boolean = new Boolean('string');
    }
    
    public function testClone() {
        $boolean = new Boolean(true);
        $otherBoolean = clone $boolean;
        
        $this->assertEquals($boolean->getValue(), $otherBoolean->getValue());
        $this->assertNotSame($boolean, $otherBoolean);
        
        $otherBoolean->setValue(false);
        $this->assertNotEquals($boolean->getValue(), $otherBoolean->getValue());
    }
}