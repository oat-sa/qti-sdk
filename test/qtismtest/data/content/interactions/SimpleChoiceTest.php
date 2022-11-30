<?php

declare(strict_types=1);

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\SimpleChoice;
use qtismtest\QtiSmTestCase;

/**
 * Class ChoiceTest
 */
class ChoiceTest extends QtiSmTestCase
{
    public function testCreateChoiceWrongIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'identifier' argument must be a valid QTI identifier");

        $choice = new SimpleChoice('999');
    }

    public function testSetFixedWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'fixed' argument must be a boolean value, 'string' given.");

        $choice = new SimpleChoice('ABC');
        $choice->setFixed('bla');
    }

    public function testSetTemplateIdentifierWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'templateIdentifier' must be an empty string or a valid QTI identifier, 'integer' given.");

        $choice = new SimpleChoice('ABC');
        $choice->setTemplateIdentifier(999);
    }

    public function testSetShowHideWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'showHide' argument must be a value from the ShowHide enumeration.");

        $choice = new SimpleChoice('ABC');
        $choice->setShowHide(999);
    }
}
