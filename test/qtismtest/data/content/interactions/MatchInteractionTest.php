<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\MatchInteraction;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\interactions\SimpleAssociableChoiceCollection;
use qtism\data\content\interactions\SimpleMatchSet;
use qtism\data\content\interactions\SimpleMatchSetCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class MatchInteractionTest
 */
class MatchInteractionTest extends QtiSmTestCase
{
    public function testSetShuffleWrongType(): void
    {
        $matchSet1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceA', 1)]));
        $matchSet2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceB', 1)]));

        $matchInteraction = new MatchInteraction(
            'RESPONSE',
            new SimpleMatchSetCollection([
                $matchSet1,
                $matchSet2,
            ])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'shuffle' argument must be a boolean value, 'string' given.");

        $matchInteraction->setShuffle('true');
    }

    public function testSetMaxAssociationsWrongType(): void
    {
        $matchSet1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceA', 1)]));
        $matchSet2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceB', 1)]));

        $matchInteraction = new MatchInteraction(
            'RESPONSE',
            new SimpleMatchSetCollection([
                $matchSet1,
                $matchSet2,
            ])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'maxAssociations' argument must be a positive (>= 0) integer, 'string' given.");

        $matchInteraction->setMaxAssociations('true');
    }

    public function testSetMinAssociationsWrongType(): void
    {
        $matchSet1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceA', 1)]));
        $matchSet2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceB', 1)]));

        $matchInteraction = new MatchInteraction(
            'RESPONSE',
            new SimpleMatchSetCollection([
                $matchSet1,
                $matchSet2,
            ])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minAssociations' argument must be a positive (>= 0) integer, 'string' given.");

        $matchInteraction->setMinAssociations('true');
    }

    public function testSetMinAssociationsWrongValue(): void
    {
        $matchSet1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceA', 1)]));
        $matchSet2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceB', 1)]));

        $matchInteraction = new MatchInteraction(
            'RESPONSE',
            new SimpleMatchSetCollection([
                $matchSet1,
                $matchSet2,
            ])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'minAssociations' argument must be less than or equal to the limit imposed by 'maxAssociations'.");

        $matchInteraction->setMaxAssociations(3);
        $matchInteraction->setMinAssociations(4);
    }

    public function testHasMinAssociations(): void
    {
        $matchSet1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceA', 1)]));
        $matchSet2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceB', 1)]));

        $matchInteraction = new MatchInteraction(
            'RESPONSE',
            new SimpleMatchSetCollection([
                $matchSet1,
                $matchSet2,
            ])
        );

        $this::assertFalse($matchInteraction->hasMinAssociations());
    }

    public function testNotEnoughMatchSets(): void
    {
        $matchSet1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceA', 1)]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A MatchInteraction object must be composed of exactly two SimpleMatchSet objects.');

        $matchInteraction = new MatchInteraction(
            'RESPONSE',
            new SimpleMatchSetCollection([
                $matchSet1,
            ])
        );
    }

    public function testGetSourceChoices(): void
    {
        $matchSet1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceA', 1)]));
        $matchSet2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceB', 1)]));

        $matchInteraction = new MatchInteraction(
            'RESPONSE',
            new SimpleMatchSetCollection([
                $matchSet1,
                $matchSet2,
            ])
        );

        $this::assertSame($matchSet1, $matchInteraction->getSourceChoices());
    }

    public function testGetTargetChoices(): void
    {
        $matchSet1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceA', 1)]));
        $matchSet2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([new SimpleAssociableChoice('ChoiceB', 1)]));

        $matchInteraction = new MatchInteraction(
            'RESPONSE',
            new SimpleMatchSetCollection([
                $matchSet1,
                $matchSet2,
            ])
        );

        $this::assertSame($matchSet2, $matchInteraction->getTargetChoices());
    }
}
