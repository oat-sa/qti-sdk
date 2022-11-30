<?php

declare(strict_types=1);

namespace qtismtest\data\content;

use InvalidArgumentException;
use qtism\data\content\RubricBlockRef;
use qtismtest\QtiSmTestCase;

/**
 * Class RubricBlockRefTest
 */
class RubricBlockRefTest extends QtiSmTestCase
{
    public function testCreateWrongIdentifierType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'identifier' argument must be a valid QTI identifier, '999' given.");
        new RubricBlockRef('999', 'href.ref');
    }

    public function testCreateWrongHrefType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'href' argument must be a valid URI, '' given.");
        new RubricBlockRef('ref-1', '');
    }
}
