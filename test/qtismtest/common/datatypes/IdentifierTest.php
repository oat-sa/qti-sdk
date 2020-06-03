<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiIdentifier;
use qtismtest\QtiSmTestCase;

class IdentifierTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            'The Identifier Datatype only accepts to store identifier values.'
        );
        $float = new QtiIdentifier(1337);
    }

    public function testEmptyIdentifier()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            'The Identifier Datatype do not accept empty strings as valid identifiers.'
        );
        $float = new QtiIdentifier('');
    }
}
