<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\ExtendedTextInteraction;
use qtismtest\QtiSmTestCase;

/**
 * Class ExtendedTextInteractionTest
 */
class ExtendedTextInteractionTest extends QtiSmTestCase
{
    public function testSetBaseWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'base' argument must be a positive (>= 0) integer value, 'string' given.");

        $extendedTextInteraction->setBase('wrong');
    }

    public function testSetResponseIdentifierWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'responseIdentifier' argument must be a valid QTI identifier.");

        $extendedTextInteraction->setResponseIdentifier(1337);
    }

    public function testSetExpectedLengthWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLength" argument must be a non-negative integer (>= 0) or null, "boolean" given.');

        $extendedTextInteraction->setExpectedLength(true);
    }

    /**
     * @dataProvider nonNegativeIntegersForExpectedLengthAndLines
     * @param integer $expectedLength
     */
    public function testSetExpectedLengthToNonNegativeInteger($expectedLength)
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLength($expectedLength);

        $this::assertTrue($textEntryInteraction->hasExpectedLength());
        $this::assertEquals($expectedLength, $textEntryInteraction->getExpectedLength());
    }

    public function testSetExpectedLengthToNegativeIntegerThrowsException()
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLength" argument must be a non-negative integer (>= 0) or null, "-1" given.');

        $textEntryInteraction->setExpectedLength(-1);
    }

    public function testUnsetExpectedLengthWithNull()
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLength(null);

        $this::assertFalse($textEntryInteraction->hasExpectedLength());
        $this::assertNull($textEntryInteraction->getExpectedLength());
    }

    public function testSetPatternMaskWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'patternMask' argument must be a string value, 'boolean' given.");

        $extendedTextInteraction->setPatternMask(true);
    }

    public function testSetPlaceholderTextWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'placeholderText' argument must be a string value, 'boolean' given.");

        $extendedTextInteraction->setPlaceholderText(true);
    }

    public function testSetMaxStringsWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxStrings' argument must be a strictly positive (> 0) integer or -1, 'boolean' given.");

        $extendedTextInteraction->setMaxStrings(true);
    }

    public function testSetMinStringsWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minStrings' argument must be a positive (>= 0) integer, 'boolean' given.");

        $extendedTextInteraction->setMinStrings(true);
    }

    public function testSetExpectedLinesWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLines" argument must be a non-negative integer (>= 0) or null, "boolean" given.');

        $extendedTextInteraction->setExpectedLines(true);
    }

    /**
     * @dataProvider nonNegativeIntegersForExpectedLengthAndLines
     * @param integer $expectedLines
     */
    public function testSetExpectedLinesToNonNegativeInteger($expectedLines)
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLines($expectedLines);

        $this::assertTrue($textEntryInteraction->hasExpectedLines());
        $this::assertEquals($expectedLines, $textEntryInteraction->getExpectedLines());
    }

    public function testSetExpectedLinesToNegativeIntegerThrowsException()
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLines" argument must be a non-negative integer (>= 0) or null, "-1" given.');

        $textEntryInteraction->setExpectedLines(-1);
    }

    public function testUnsetExpectedLinesWithNull()
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLines(null);

        $this::assertFalse($textEntryInteraction->hasExpectedLines());
        $this::assertNull($textEntryInteraction->getExpectedLines());
    }

    public function nonNegativeIntegersForExpectedLengthAndLines(): array
    {
        return [
            [0],
            [1],
            [1012],
            [2 ** 31 - 1],
        ];
    }

    public function testSetFormatWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'format' argument must be a value from the TextFormat enumeration, 'integer' given.");

        $extendedTextInteraction->setFormat(999);
    }
}
