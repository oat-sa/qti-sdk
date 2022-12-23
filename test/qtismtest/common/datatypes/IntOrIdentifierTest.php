<?php

namespace qtismtest\common\datatypes;

use InvalidArgumentException;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtismtest\QtiSmTestCase;

/**
 * Class IntOrIdentifierTest
 */
class IntOrIdentifierTest extends QtiSmTestCase
{
    public function testWrongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $intOrIdentifier = new QtiIntOrIdentifier(13.37);
    }
}
