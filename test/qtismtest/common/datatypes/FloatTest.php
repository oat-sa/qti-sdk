<?php
namespace qtismtest\common\datatypes;

use qtism\common\datatypes\Float;
use qtismtest\QtiSmTestCase;

class FloatTest extends QtiSmTestCase {
    
    public function testWrongValue() {
        $this->setExpectedException('\\InvalidArgumentException');
        $float = new Float(null);
    }
}