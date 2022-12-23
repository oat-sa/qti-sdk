<?php

namespace qtismtest\data\content\xhtml\tables;

use InvalidArgumentException;
use qtism\data\content\xhtml\tables\Td;
use qtismtest\QtiSmTestCase;

/**
 * Class TdTest
 */
class TdTest extends QtiSmTestCase
{
    public function testSetScopeWrongValue(): void
    {
        $td = new Td();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'scope' argument must be a value from the TableCellScope enumeration, '1' given.");

        $td->setScope(true);
    }

    public function testSetAbbrWrongType(): void
    {
        $td = new Td();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'abbr' attribute must be a string, 'boolean' given.");

        $td->setAbbr(true);
    }

    public function testSetAxisWrongType(): void
    {
        $td = new Td();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'axis' argument must be a string, 'boolean' given.");

        $td->setAxis(true);
    }

    public function testSetRowspanWrongType(): void
    {
        $td = new Td();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'rowspan' argument must be an integer, 'boolean' given.");

        $td->setRowspan(true);
    }

    public function testSetColspanWrongType(): void
    {
        $td = new Td();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'colspan' argument must be an integer, 'boolean' given.");

        $td->setColspan(true);
    }
}
