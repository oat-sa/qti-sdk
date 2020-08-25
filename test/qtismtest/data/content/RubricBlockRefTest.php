<?php

namespace qtismtest\data\content;

use InvalidArgumentException;
use qtism\data\content\RubricBlockRef;
use qtismtest\QtiSmTestCase;

/**
 * Class RubricBlockRefTest
 *
 * @package qtismtest\data\content
 */
class RubricBlockRefTest extends QtiSmTestCase
{
    public function testCreateWrongIdentifierType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'identifier' argument must be a valid QTI identifier, '999' given.");
        $rubricBlockRef = new RubricBlockRef('999', 'href.ref');
    }

    public function testCreateWrongHrefType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'href' argument must be a valid URI, '999' given.");
        $rubricBlockRef = new RubricBlockRef('ref-1', 999);
    }
}
