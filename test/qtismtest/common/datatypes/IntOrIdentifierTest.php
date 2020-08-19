<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiIntOrIdentifier;
use qtismtest\QtiSmTestCase;

class IntOrIdentifierTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $intOrIdentifier = new QtiIntOrIdentifier(13.37);
    }
}
