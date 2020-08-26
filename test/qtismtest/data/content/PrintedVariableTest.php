<?php

namespace qtismtest\data\content;

use InvalidArgumentException;
use qtism\data\content\PrintedVariable;
use qtismtest\QtiSmTestCase;

/**
 * Class PrintedVariableTest
 */
class PrintedVariableTest extends QtiSmTestCase
{
    public function testCreatePrintedVariableWrongIdentifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'identifier' argument must be a valid QTI identifier, '999' given.");

        $printedVariable = new PrintedVariable('999');
    }

    public function testSetFormatWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'format' argument must be a string with at most 256 characters, '999' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setFormat(999);
    }

    public function testSetPowerFormWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'powerForm' argument must be a boolean value, 'integer' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setPowerForm(999);
    }

    public function testSetBaseWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'base' argument must be an integer or a variable reference, '999.9' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setBase(999.9);
    }

    public function testSetIndexWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'index' argument must be an integer or a variable reference, '999.9' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setIndex(999.9);
    }

    public function testSetDelimiterWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'delimiter' argument must be a non-empty string, 'double' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setDelimiter(999.9);
    }

    public function testSetFieldWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'field' argument must be a non-empty string, 'double' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setField(999.9);
    }

    public function testSetMappingIndicatorWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'mappingIndicator' argument must be a non-empty string with at most 256 characters, 'double' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setMappingIndicator(999.9);
    }
}
