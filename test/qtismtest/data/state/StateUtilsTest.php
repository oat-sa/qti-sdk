<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\content\BlockStaticCollection;
use qtism\data\content\interactions\AssociateInteraction;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\GapChoiceCollection;
use qtism\data\content\interactions\GapMatchInteraction;
use qtism\data\content\interactions\GapText;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\interactions\InlineChoiceCollection;
use qtism\data\content\interactions\InlineChoiceInteraction;
use qtism\data\content\interactions\MatchInteraction;
use qtism\data\content\interactions\OrderInteraction;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\interactions\SimpleAssociableChoiceCollection;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtism\data\content\interactions\SimpleMatchSet;
use qtism\data\content\interactions\SimpleMatchSetCollection;
use qtism\data\content\interactions\TextEntryInteraction;
use qtism\data\content\xhtml\text\Div;
use qtism\data\state\Utils as StateUtils;
use qtismtest\QtiSmTestCase;

/**
 * Class StateUtilsTest
 */
class StateUtilsTest extends QtiSmTestCase
{
    public function testCreateShufflingFromInteractionChoice(): void
    {
        $choice1 = new SimpleChoice('id1');
        $choice2 = new SimpleChoice('id2');
        $choice3 = new SimpleChoice('id3');
        $choice1->setFixed(true);
        $choice3->setFixed(true);
        $choiceCollection = new SimpleChoiceCollection([$choice1, $choice2, $choice3]);

        $choiceInteraction = new ChoiceInteraction('RESPONSE', $choiceCollection);
        $choiceInteraction->setShuffle(true);

        $shuffling = StateUtils::createShufflingFromInteraction($choiceInteraction);
        $this::assertEquals('RESPONSE', $shuffling->getResponseIdentifier());

        $shufflingGroups = $shuffling->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['id1', 'id2', 'id3'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this::assertEquals(['id1', 'id3'], $shufflingGroups[0]->getFixedIdentifiers()->getArrayCopy());
    }

    public function testCreateShufflingFromOrder(): void
    {
        $choiceCollection = new SimpleChoiceCollection();
        $choiceCollection[] = new SimpleChoice('id1');
        $choiceCollection[] = new SimpleChoice('id2');
        $choiceCollection[] = new SimpleChoice('id3');
        $orderInteraction = new OrderInteraction('RESPONSE', $choiceCollection);
        $orderInteraction->setShuffle(true);

        $shuffling = StateUtils::createShufflingFromInteraction($orderInteraction);
        $this::assertEquals('RESPONSE', $shuffling->getResponseIdentifier());

        $shufflingGroups = $shuffling->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['id1', 'id2', 'id3'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
    }

    public function testCreateShufflingFromAssociateInteraction(): void
    {
        $choiceCollection = new SimpleAssociableChoiceCollection();
        $choice1 = new SimpleAssociableChoice('id1', 1);
        $choice2 = new SimpleAssociableChoice('id2', 1);
        $choice2->setFixed(true);
        $choice3 = new SimpleAssociableChoice('id3', 1);
        $choiceCollection[] = $choice1;
        $choiceCollection[] = $choice2;
        $choiceCollection[] = $choice3;
        $associateInteraction = new AssociateInteraction('RESPONSE', $choiceCollection);
        $associateInteraction->setShuffle(true);

        $shuffling = StateUtils::createShufflingFromInteraction($associateInteraction);
        $this::assertEquals('RESPONSE', $shuffling->getResponseIdentifier());

        $shufflingGroups = $shuffling->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['id1', 'id2', 'id3'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this::assertEquals(['id2'], $shufflingGroups[0]->getFixedIdentifiers()->getArrayCopy());
    }

    public function testCreateShufflingFromMatchInteraction(): void
    {
        $choiceCollection1 = new SimpleAssociableChoiceCollection();
        $choice11 = new SimpleAssociableChoice('id1', 1);
        $choice22 = new SimpleAssociableChoice('id2', 1);
        $choice22->setFixed(true);
        $choiceCollection1[] = $choice11;
        $choiceCollection1[] = $choice22;

        $choiceCollection2 = new SimpleAssociableChoiceCollection();
        $choice21 = new SimpleAssociableChoice('id3', 1);
        $choice22 = new SimpleAssociableChoice('id4', 1);
        $choiceCollection2[] = $choice21;
        $choiceCollection2[] = $choice22;

        $matchSets = new SimpleMatchSetCollection();
        $matchSets[] = new SimpleMatchSet($choiceCollection1);
        $matchSets[] = new SimpleMatchSet($choiceCollection2);

        $matchInteraction = new MatchInteraction('RESPONSE', $matchSets);
        $matchInteraction->setShuffle(true);
        $shuffling = StateUtils::createShufflingFromInteraction($matchInteraction);
        $this::assertEquals('RESPONSE', $shuffling->getResponseIdentifier());

        $shufflingGroups = $shuffling->getShufflingGroups();
        $this::assertCount(2, $shufflingGroups);
        $this::assertEquals(['id1', 'id2'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this::assertEquals(['id2'], $shufflingGroups[0]->getFixedIdentifiers()->getArrayCopy());
        $this::assertEquals(['id3', 'id4'], $shufflingGroups[1]->getIdentifiers()->getArrayCopy());
    }

    public function testCreateShufflingFromGapMatchInteraction(): void
    {
        $choiceCollection = new GapChoiceCollection();
        $gapText1 = new GapText('id1', 1);
        $gapText2 = new GapText('id2', 1);
        $gapText3 = new GapText('id3', 1);
        $gapText3->setFixed(true);
        $choiceCollection[] = $gapText1;
        $choiceCollection[] = $gapText2;
        $choiceCollection[] = $gapText3;
        $blockCollection = new BlockStaticCollection([new Div()]);

        $gapMatchInteraction = new GapMatchInteraction('RESPONSE', $choiceCollection, $blockCollection);
        $gapMatchInteraction->setShuffle(true);

        $shuffling = StateUtils::createShufflingFromInteraction($gapMatchInteraction);
        $this::assertEquals('RESPONSE', $shuffling->getResponseIdentifier());

        $shufflingGroups = $shuffling->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['id1', 'id2', 'id3'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this::assertEquals(['id3'], $shufflingGroups[0]->getFixedIdentifiers()->getArrayCopy());
    }

    public function testCreateShufflingFromInlineChoiceInteraction(): void
    {
        $choiceCollection = new InlineChoiceCollection();
        $choice1 = new InlineChoice('id1');
        $choice2 = new InlineChoice('id2');
        $choice3 = new InlineChoice('id3');
        $choice3->setFixed(true);
        $choiceCollection[] = $choice1;
        $choiceCollection[] = $choice2;
        $choiceCollection[] = $choice3;
        $inlineChoiceInteraction = new InlineChoiceInteraction('RESPONSE', $choiceCollection);
        $inlineChoiceInteraction->setShuffle(true);

        $shuffling = StateUtils::createShufflingFromInteraction($inlineChoiceInteraction);
        $this::assertEquals('RESPONSE', $shuffling->getResponseIdentifier());

        $shufflingGroups = $shuffling->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['id1', 'id2', 'id3'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this::assertEquals(['id3'], $shufflingGroups[0]->getFixedIdentifiers()->getArrayCopy());
    }

    public function testCreateShufflingFromNonShufflableInteraction(): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');
        $shuffling = StateUtils::createShufflingFromInteraction($textEntryInteraction);
        $this::assertFalse($shuffling);
    }

    public function testCreateShufflingWithShuffleFalse(): void
    {
        $choiceCollection = new SimpleChoiceCollection();
        $choiceCollection[] = new SimpleChoice('id1');
        $choiceCollection[] = new SimpleChoice('id2');
        $choiceCollection[] = new SimpleChoice('id3');
        $choiceInteraction = new ChoiceInteraction('RESPONSE', $choiceCollection);
        $choiceInteraction->setShuffle(false);

        $shuffling = StateUtils::createShufflingFromInteraction($choiceInteraction);
        $this::assertFalse($shuffling);
    }
}
