<?php
namespace qtismtest\common\datatypes;

use qtism\common\datatypes\Identifier;
use qtismtest\QtiSmTestCase;

class IdentifierTest extends QtiSmTestCase {
    
    public function testWrongValue() {
        $this->setExpectedException('\\InvalidArgumentException');
        $float = new Identifier(1337);
    }
}