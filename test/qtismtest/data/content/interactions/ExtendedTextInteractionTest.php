<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\ExtendedTextInteraction;
use qtismtest\QtiSmTestCase;

class ExtendedTextInteractionTest extends QtiSmTestCase
{
    public function testSetBaseWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'base' argument must be a positive (>= 0) integer value, 'string' given."
        );

        $extendedTextInteraction->setBase('wrong');
    }

    public function testSetResponseIdentifierWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'responseIdentifier' argument must be a valid QTI identifier."
        );

        $extendedTextInteraction->setResponseIdentifier(1337);
    }

    public function testSetExpectedLengthWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLength" argument must be a non-negative integer (>= 0), "boolean" given.');

        $extendedTextInteraction->setExpectedLength(true);
    }

    /**
     * @dataProvider nonNegativeIntegersForExpectedLength
     * @param integer $expectedLength
     */
    public function testSetExpectedLengthToNonNegativeInteger($expectedLength)
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLength($expectedLength);

        $this->assertTrue($textEntryInteraction->hasExpectedLength());
        $this->assertEquals($expectedLength, $textEntryInteraction->getExpectedLength());
    }

    public function nonNegativeIntegersForExpectedLength(): array
    {
        return [
            [0],
            [1],
            [1012],
            [2 ** 31 - 1],
        ];
    }

    public function testSetExpectedLengthToNegativeIntegerThrowsException()
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLength" argument must be a non-negative integer (>= 0), "-1" given.');

        $textEntryInteraction->setExpectedLength(-1);
    }

    public function testUnsetExpectedLengthWithNull()
    {
        $textEntryInteraction = new ExtendedTextInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLength(null);

        $this->assertFalse($textEntryInteraction->hasExpectedLength());
        $this->assertNull($textEntryInteraction->getExpectedLength());
    }

    public function testSetPatternMaskWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'patternMask' argument must be a string value, 'boolean' given."
        );

        $extendedTextInteraction->setPatternMask(true);
    }

    public function testSetPlaceholderTextWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'placeholderText' argument must be a string value, 'boolean' given."
        );

        $extendedTextInteraction->setPlaceholderText(true);
    }

    public function testSetMaxStringsWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'maxStrings' argument must be a strictly positive (> 0) integer or -1, 'boolean' given."
        );

        $extendedTextInteraction->setMaxStrings(true);
    }

    public function testSetMinStringsWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'minStrings' argument must be a positive (>= 0) integer, 'boolean' given."
        );

        $extendedTextInteraction->setMinStrings(true);
    }

    public function testSetExpectedLinesWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'expectedLines' argument must be a strictly positive (> 0) intege or -1, 'boolean' given."
        );

        $extendedTextInteraction->setExpectedLines(true);
    }

    public function testSetFormatWrongType()
    {
        $extendedTextInteraction = new ExtendedTextInteraction('RESPONSE');

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'format' argument must be a value from the TextFormat enumeration, 'integer' given."
        );

        $extendedTextInteraction->setFormat(999);
    }
}
