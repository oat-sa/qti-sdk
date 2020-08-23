<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtismtest\QtiSmTestCase;

class ChoiceInteractionTest extends QtiSmTestCase
{
    public function testCreateEmptyChoiceList()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A ChoiceInteraction object must be composed of at lease one SimpleChoice object, none given.');

        new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection());
    }

    public function testSetShuffleWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'shuffle' argument must be a boolean value, 'string' given.");

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setShuffle('true');
    }

    public function testSetMaxChoicesWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxChoices' argument must be a positive (>= 0) integer, 'string' given.");

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setMaxChoices('3');
    }

    public function testSetMinChoicesWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minChoices' argument must be a positive (>= 0) integer, 'string' given.");

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setMinChoices('3');
    }

    public function testSetOrientationWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'orientation' argument must be a value from the Orientation enumeration, 'boolean' given.");

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setOrientation(true);
    }
}
