<?php
namespace qtismtest\common\datatypes;

use qtism\common\datatypes\Integer;
use qtismtest\QtiSmTestCase;

class IntegerTest extends QtiSmTestCase {
    
    public function testWrongValue() {
        $this->setExpectedException('\\InvalidArgumentException');
        $integer = new Integer(13.37);
    }
}