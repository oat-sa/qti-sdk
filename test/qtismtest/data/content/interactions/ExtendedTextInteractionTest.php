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
    public function testSetBaseWrongType(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'base' argument must be a positive (>= 0) integer value, 'string' given.");

        $extendedTextInteraction->setBase('wrong');
    }

    public function testSetResponseIdentifierWrongType(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'responseIdentifier' argument must be a valid QTI identifier.");

        $extendedTextInteraction->setResponseIdentifier(1337);
    }

    public function testSetExpectedLengthWrongType(): void
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
    public function testSetExpectedLengthToNonNegativeInteger($expectedLength): void
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLength($expectedLength);

        $this::assertTrue($textEntryInteraction->hasExpectedLength());
        $this::assertEquals($expectedLength, $textEntryInteraction->getExpectedLength());
    }

    public function testSetExpectedLengthToNegativeIntegerThrowsException(): void
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLength" argument must be a non-negative integer (>= 0) or null, "-1" given.');

        $textEntryInteraction->setExpectedLength(-1);
    }

    public function testUnsetExpectedLengthWithNull(): void
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLength(null);

        $this::assertFalse($textEntryInteraction->hasExpectedLength());
        $this::assertTrue($textEntryInteraction->getExpectedLength() === -1);
    }

    public function testSetPatternMaskWrongType(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'patternMask' argument must be a string value, 'boolean' given.");

        $extendedTextInteraction->setPatternMask(true);
    }

    public function testPatternMaskIgnoredForDisabledValidation(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');
        $extendedTextInteraction->setPatternMask('pattern');
        $extendedTextInteraction->disabledMaxWordValidation();

        $this->assertEmpty($extendedTextInteraction->getPatternMask());
        $this->assertFalse($extendedTextInteraction->hasPatternMask());
    }

    public function testSetPlaceholderTextWrongType(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'placeholderText' argument must be a string value, 'boolean' given.");

        $extendedTextInteraction->setPlaceholderText(true);
    }

    public function testSetMaxStringsWrongType(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxStrings' argument must be a strictly positive (> 0) integer or -1, 'boolean' given.");

        $extendedTextInteraction->setMaxStrings(true);
    }

    public function testMaxStringsIgnoredForDisableValidation(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');
        $extendedTextInteraction->setMaxStrings(10);
        $extendedTextInteraction->disabledMaxWordValidation();

        $this->assertEquals(-1, $extendedTextInteraction->getMaxStrings());
        $this->assertFalse($extendedTextInteraction->hasMaxStrings());
    }

    public function testSetMinStringsWrongType(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minStrings' argument must be a positive (>= 0) integer, 'boolean' given.");

        $extendedTextInteraction->setMinStrings(true);
    }

    public function testSetExpectedLinesWrongType(): void
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
    public function testSetExpectedLinesToNonNegativeInteger($expectedLines): void
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLines($expectedLines);

        $this::assertTrue($textEntryInteraction->hasExpectedLines());
        $this::assertEquals($expectedLines, $textEntryInteraction->getExpectedLines());
    }

    public function testSetExpectedLinesToNegativeIntegerThrowsException(): void
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLines" argument must be a non-negative integer (>= 0) or null, "-1" given.');

        $textEntryInteraction->setExpectedLines(-1);
    }

    public function testUnsetExpectedLinesWithNull(): void
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

    public function testSetFormatWrongType(): void
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'format' argument must be a value from the TextFormat enumeration, 'integer' given.");

        $extendedTextInteraction->setFormat(999);
    }
}
