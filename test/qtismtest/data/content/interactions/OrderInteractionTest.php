<?php

declare(strict_types=1);

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\OrderInteraction;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class OrderInteractionTest
 */
class OrderInteractionTest extends QtiSmTestCase
{
    public function testNotEnoughChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An OrderInteraction object must be composed of at lease one SimpleChoice object, none given');

        $orderInteraction = new OrderInteraction(
            'RESPONSE',
            new SimpleChoiceCollection([])
        );
    }

    public function testSetShuffleWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'shuffle' argument must be a boolean value, 'string' given");

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

    public function testSetMinChoicesWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minChoices' argument must be a strictly positive (> 0) integer or -1, 'string' given.");

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

    public function testSetMinChoicesChoicesOverflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The value of 'minChoices' cannot exceed the number of available choices.");

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

    public function testSetMaxChoicesWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxChoices' argument must be a strictly positive (> 0) integer or -1, 'string' given.");

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

    public function testSetMaxChoicesOverflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxChoices' argument cannot exceed the number of available choices.");

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

    public function testSetOrientationWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'orientation' argument must be a value from the Orientation enumeration, 'string' given.");

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
