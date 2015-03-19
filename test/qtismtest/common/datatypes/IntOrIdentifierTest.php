<?php
namespace qtismtest\common\datatypes;

use qtism\common\datatypes\IntOrIdentifier;
use qtismtest\QtiSmTestCase;

class IntOrIdentifierTest extends QtiSmTestCase {
    
    public function testWrongValue() {
        $this->setExpectedException('\\InvalidArgumentException');
        $intOrIdentifier = new IntOrIdentifier(13.37);
    }
}