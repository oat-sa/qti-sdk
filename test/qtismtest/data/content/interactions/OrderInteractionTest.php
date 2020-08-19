<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\OrderInteraction;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtismtest\QtiSmTestCase;

class OrderInteractionTest extends QtiSmTestCase
{
    public function testNotEnoughChoices()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "An OrderInteraction object must be composed of at lease one SimpleChoice object, none given"
        );

        $orderInteraction = new OrderInteraction(
            'RESPONSE',
            new SimpleChoiceCollection([])
        );
    }

    public function testSetShuffleWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'shuffle' argument must be a boolean value, 'string' given"
        );

        $orderInteraction = new OrderInteraction(
            'RESPONSE',
            new SimpleChoiceCollection(
                [
                    new SimpleChoice('ChoiceA'),
                    new SimpleChoice('ChoiceB'),
                ]
            )
        );

        $orderInteraction->setShuffle('true');
    }

    public function testSetMinChoicesWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'minChoices' argument must be a strictly positive (> 0) integer or -1, 'string' given."
        );

        $orderInteraction = new OrderInteraction(
            'RESPONSE',
            new SimpleChoiceCollection(
                [
                    new SimpleChoice('ChoiceA'),
                    new SimpleChoice('ChoiceB'),
                ]
            )
        );

        $orderInteraction->setMinChoices('true');
    }

    public function testSetMinChoicesChoicesOverflow()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The value of 'minChoices' cannot exceed the number of available choices."
        );

        $orderInteraction = new OrderInteraction(
            'RESPONSE',
            new SimpleChoiceCollection(
                [
                    new SimpleChoice('ChoiceA'),
                    new SimpleChoice('ChoiceB'),
                ]
            )
        );

        $orderInteraction->setMinChoices(3);
    }

    public function testSetMaxChoicesWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'maxChoices' argument must be a strictly positive (> 0) integer or -1, 'string' given."
        );

        $orderInteraction = new OrderInteraction(
            'RESPONSE',
            new SimpleChoiceCollection(
                [
                    new SimpleChoice('ChoiceA'),
                    new SimpleChoice('ChoiceB'),
                ]
            )
        );

        $orderInteraction->setMaxChoices('true');
    }

    public function testSetMaxChoicesOverflow()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'maxChoices' argument cannot exceed the number of available choices."
        );

        $orderInteraction = new OrderInteraction(
            'RESPONSE',
            new SimpleChoiceCollection(
                [
                    new SimpleChoice('ChoiceA'),
                    new SimpleChoice('ChoiceB'),
                ]
            )
        );

        $orderInteraction->setMinChoices(1);
        $orderInteraction->setMaxChoices(3);
    }

    public function testSetOrientationWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'orientation' argument must be a value from the Orientation enumeration, 'string' given."
        );

        $orderInteraction = new OrderInteraction(
            'RESPONSE',
            new SimpleChoiceCollection(
                [
                    new SimpleChoice('ChoiceA'),
                    new SimpleChoice('ChoiceB'),
                ]
            )
        );

        $orderInteraction->setOrientation('true');
    }
}
