<?php

declare(strict_types=1);

namespace qtismtest\data\content;

use InvalidArgumentException;
use qtism\data\content\PrintedVariable;
use qtismtest\QtiSmTestCase;

/**
 * Class PrintedVariableTest
 */
class PrintedVariableTest extends QtiSmTestCase
{
    public function testCreatePrintedVariableWrongIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'identifier' argument must be a valid QTI identifier, '999' given.");

        $printedVariable = new PrintedVariable('999');
    }

    /**
     * @dataProvider tooLongStrings
     * @param string $string
     */
    public function testSetFormatWrongType(string $string): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'format' argument must be a string with at most 256 characters, '" . $string . "' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setFormat($string);
    }

    public function testSetPowerFormWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'powerForm' argument must be a boolean value, 'integer' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setPowerForm(999);
    }

    public function testSetBaseWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'base' argument must be an integer or a variable reference, '999.9' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setBase(999.9);
    }

    public function testSetIndexWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'index' argument must be an integer or a variable reference, '999.9' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setIndex(999.9);
    }

    /**
     * @dataProvider emptyOrTooLongStrings
     * @param string $string
     */
    public function testSetDelimiterWrongType(string $string): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'delimiter' argument must be a non-empty string with at most 256 characters, '" . $string . "' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setDelimiter($string);
    }

    /**
     * @dataProvider tooLongStrings
     * @param string $string
     */
    public function testSetFieldWrongType(string $string): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'field' argument must be a non-empty string, '" . $string . "' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setField($string);
    }

    /**
     * @dataProvider emptyOrTooLongStrings
     * @param string $string
     */
    public function testSetMappingIndicatorWrongType(string $string): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'mappingIndicator' argument must be a non-empty string with at most 256 characters, '" . $string . "' given.");

        $printedVariable = new PrintedVariable('ABC');
        $printedVariable->setMappingIndicator($string);
    }

    public function tooLongStrings(): array
    {
        return [
            [str_repeat('too_long', 33)],
        ];
    }

    public function emptyOrTooLongStrings(): array
    {
        return [
            [''],
            [str_repeat('too_long', 33)],
        ];
    }
}
