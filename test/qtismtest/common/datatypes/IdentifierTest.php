<?php

declare(strict_types=1);

namespace qtismtest\common\datatypes;

use InvalidArgumentException;
use qtism\common\datatypes\QtiIdentifier;
use qtismtest\QtiSmTestCase;

/**
 * Class IdentifierTest
 */
class IdentifierTest extends QtiSmTestCase
{
    public function testWrongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Identifier Datatype only accepts to store identifier values.');
        $float = new QtiIdentifier(1337);
    }

    public function testEmptyIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Identifier Datatype do not accept empty strings as valid identifiers.');
        $float = new QtiIdentifier('');
    }
}
