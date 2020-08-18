<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiIdentifier;
use qtismtest\QtiSmTestCase;

class IdentifierTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The Identifier Datatype only accepts to store identifier values.'
        );
        $float = new QtiIdentifier(1337);
    }
}
