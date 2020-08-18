<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtismtest\QtiSmTestCase;

class ChoiceInteractionTest extends QtiSmTestCase
{
    public function testCreateEmptyChoiceList()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'A ChoiceInteraction object must be composed of at lease one SimpleChoice object, none given.'
        );

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection());
    }

    public function testSetShuffleWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'shuffle' argument must be a boolean value, 'string' given."
        );

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setShuffle('true');
    }

    public function testSetMaxChoicesWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'maxChoices' argument must be a positive (>= 0) integer, 'string' given."
        );

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setMaxChoices('3');
    }

    public function testSetMinChoicesWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'minChoices' argument must be a positive (>= 0) integer, 'string' given."
        );

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setMinChoices('3');
    }

    public function testSetOrientationWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'orientation' argument must be a value from the Orientation enumeration, 'boolean' given."
        );

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setOrientation(true);
    }
}
