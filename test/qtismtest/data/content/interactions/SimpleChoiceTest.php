<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\SimpleChoice;
use qtismtest\QtiSmTestCase;

class ChoiceTest extends QtiSmTestCase
{
    public function testCreateChoiceWrongIdentifier()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'identifier' argument must be a valid QTI identifier"
        );

        $choice = new SimpleChoice('999');
    }

    public function testSetFixedWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'fixed' argument must be a boolean value, 'string' given."
        );

        $choice = new SimpleChoice('ABC');
        $choice->setFixed('bla');
    }

    public function testSetTemplateIdentifierWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'templateIdentifier' must be an empty string or a valid QTI identifier, 'integer' given."
        );

        $choice = new SimpleChoice('ABC');
        $choice->setTemplateIdentifier(999);
    }

    public function testSetShowHideWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'showHide' argument must be a value from the ShowHide enumeration."
        );

        $choice = new SimpleChoice('ABC');
        $choice->setShowHide(999);
    }
}
