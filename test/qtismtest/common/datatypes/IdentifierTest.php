<?php
namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiIdentifier;
use qtismtest\QtiSmTestCase;

class IdentifierTest extends QtiSmTestCase {
    
    public function testWrongValue() {
        $this->setExpectedException('\\InvalidArgumentException');
        $float = new QtiIdentifier(1337);
    }
}