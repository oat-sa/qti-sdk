<?php

declare(strict_types=1);

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class ChoiceInteractionTest
 */
class ChoiceInteractionTest extends QtiSmTestCase
{
    public function testCreateEmptyChoiceList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A ChoiceInteraction object must be composed of at lease one SimpleChoice object, none given.');

        new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection());
    }

    public function testSetShuffleWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'shuffle' argument must be a boolean value, 'string' given.");

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setShuffle('true');
    }

    public function testSetMaxChoicesWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxChoices' argument must be a positive (>= 0) integer, 'string' given.");

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setMaxChoices('3');
    }

    public function testSetMinChoicesWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minChoices' argument must be a positive (>= 0) integer, 'string' given.");

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setMinChoices('3');
    }

    public function testSetOrientationWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'orientation' argument must be a value from the Orientation enumeration, 'boolean' given.");

        $choiceInteraction = new ChoiceInteraction('RESPONSE', new SimpleChoiceCollection([new SimpleChoice('identifier')]));
        $choiceInteraction->setOrientation(true);
    }
}
