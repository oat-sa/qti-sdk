<?php

namespace qtismtest\data\content;

use qtism\data\content\PrintedVariable;
use qtismtest\QtiSmTestCase;

class PrintedVariableTest extends QtiSmTestCase
{
    public function testCreatePrintedVariableWrongIdentifier()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'identifier' argument must be a valid QTI identifier, '999' given."
        );

        $printedVariable = new PrintedVariable('999');
    }

    public function testSetFormatWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'format' argument must be a string with at most 256 characters, '999' given."
        );

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setFormat(999);
    }

    public function testSetPowerFormWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'powerForm' argument must be a boolean value, 'integer' given."
        );

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setPowerForm(999);
    }

    public function testSetBaseWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'base' argument must be an integer or a variable reference, '999.9' given."
        );

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setBase(999.9);
    }

    public function testSetIndexWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'index' argument must be an integer or a variable reference, '999.9' given."
        );

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setIndex(999.9);
    }

    public function testSetDelimiterWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'delimiter' argument must be a non-empty string, 'double' given."
        );

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setDelimiter(999.9);
    }

    public function testSetFieldWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'field' argument must be a non-empty string, 'double' given."
        );

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setField(999.9);
    }

    public function testSetMappingIndicatorWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'mappingIndicator' argument must be a non-empty string with at most 256 characters, 'double' given."
        );

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setMappingIndicator(999.9);
    }
}
