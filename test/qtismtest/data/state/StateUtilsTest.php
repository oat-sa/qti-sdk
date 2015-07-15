<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\OrderInteraction;
use qtism\data\content\interactions\AssociateInteraction;
use qtism\data\content\interactions\MatchInteraction;
use qtism\data\content\interactions\GapMatchInteraction;
use qtism\data\content\interactions\InlineChoiceInteraction;
use qtism\data\content\interactions\TextEntryInteraction;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleAssociableChoiceCollection;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\interactions\SimpleMatchSetCollection;
use qtism\data\content\interactions\SimpleMatchSet;
use qtism\data\content\interactions\GapChoiceCollection;
use qtism\data\content\interactions\GapText;
use qtism\data\content\interactions\InlineChoiceCollection;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\BlockStaticCollection;
use qtism\data\content\xhtml\text\Div;
use qtism\data\state\Utils as StateUtils;

class StateUtilsTest extends QtiSmTestCase {
    
    public function testCreateShufflingFromInteractionChoice() {
        
        $choice1 = new SimpleChoice('id1');
        $choice2 = new SimpleChoice('id2');
        $choice3 = new SimpleChoice('id3');
        $choice1->setFixed(true);
        $choice3->setFixed(true);
        $choiceCollection = new SimpleChoiceCollection(array($choice1, $choice2, $choice3));
        
        $choiceInteraction = new ChoiceInteraction('RESPONSE', $choiceCollection);
        $choiceInteraction->setShuffle(true);
        
        $shuffling = StateUtils::createShufflingFromInteraction($choiceInteraction);
        $this->assertEquals('RESPONSE', $shuffling->getResponseIdentifier());
        
        $shufflingGroups = $shuffling->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this->assertEquals(array('id1', 'id3'), $shufflingGroups[0]->getFixedIdentifiers()->getArrayCopy());
    }
    
    public function testCreateShufflingFromOrder() {
        $choiceCollection = new SimpleChoiceCollection();
        $choiceCollection[] = new SimpleChoice('id1');
        $choiceCollection[] = new SimpleChoice('id2');
        $choiceCollection[] = new SimpleChoice('id3');
        $orderInteraction = new OrderInteraction('RESPONSE', $choiceCollection);
        $orderInteraction->setShuffle(true);
        
        $shuffling = StateUtils::createShufflingFromInteraction($orderInteraction);
        $this->assertEquals('RESPONSE', $shuffling->getResponseIdentifier());
        
        $shufflingGroups = $shuffling->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
    }
    
    public function testCreateShufflingFromAssociateInteraction() {
        $choiceCollection = new SimpleAssociableChoiceCollection();
        $choiceCollection[] = new SimpleAssociableChoice('id1', 1);
        $choiceCollection[] = new SimpleAssociableChoice('id2', 1);
        $choiceCollection[] = new SimpleAssociableChoice('id3', 1);
        $associateInteraction = new AssociateInteraction('RESPONSE', $choiceCollection);
        $associateInteraction->setShuffle(true);
        
        $shuffling = StateUtils::createShufflingFromInteraction($associateInteraction);
        $this->assertEquals('RESPONSE', $shuffling->getResponseIdentifier());
        
        $shufflingGroups = $shuffling->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
    }
    
    public function testCreateShufflingFromMatchInteraction() {
        $choiceCollection1 = new SimpleAssociableChoiceCollection();
        $choiceCollection1[] = new SimpleAssociableChoice('id1', 1);
        $choiceCollection1[] = new SimpleAssociableChoice('id2', 1);
        
        $choiceCollection2 = new SimpleAssociableChoiceCollection();
        $choiceCollection2[] = new SimpleAssociableChoice('id3', 1);
        $choiceCollection2[] = new SimpleAssociableChoice('id4', 1);
        
        $matchSets = new SimpleMatchSetCollection();
        $matchSets[] = new SimpleMatchSet($choiceCollection1);
        $matchSets[] = new SimpleMatchSet($choiceCollection2);
        
        $matchInteraction = new MatchInteraction('RESPONSE', $matchSets);
        $matchInteraction->setShuffle(true);
        $shuffling = StateUtils::createShufflingFromInteraction($matchInteraction);
        $this->assertEquals('RESPONSE', $shuffling->getResponseIdentifier());
        
        $shufflingGroups = $shuffling->getShufflingGroups();
        $this->assertEquals(2, count($shufflingGroups));
        $this->assertEquals(array('id1', 'id2'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this->assertEquals(array('id3', 'id4'), $shufflingGroups[1]->getIdentifiers()->getArrayCopy());
    }
    
    public function testCreateShufflingFromGapMatchInteraction() {
        $choiceCollection = new GapChoiceCollection();
        $choiceCollection[] = new GapText('id1', 1);
        $choiceCollection[] = new GapText('id2', 1);
        $choiceCollection[] = new GapText('id3', 1);
        $blockCollection = new BlockStaticCollection(array(new Div()));
        
        $gapMatchInteraction = new GapMatchInteraction('RESPONSE', $choiceCollection, $blockCollection);
        $gapMatchInteraction->setShuffle(true);
        
        $shuffling = StateUtils::createShufflingFromInteraction($gapMatchInteraction);
        $this->assertEquals('RESPONSE', $shuffling->getResponseIdentifier());
        
        $shufflingGroups = $shuffling->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
    }
    
    public function testCreateShufflingFromInlineChoiceInteraction() {
        $choiceCollection = new InlineChoiceCollection();
        $choiceCollection[] = new InlineChoice('id1');
        $choiceCollection[] = new InlineChoice('id2');
        $choiceCollection[] = new InlineChoice('id3');
        $inlineChoiceInteraction = new InlineChoiceInteraction('RESPONSE', $choiceCollection);
        $inlineChoiceInteraction->setShuffle(true);
        
        $shuffling = StateUtils::createShufflingFromInteraction($inlineChoiceInteraction);
        $this->assertEquals('RESPONSE', $shuffling->getResponseIdentifier());
        
        $shufflingGroups = $shuffling->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
    }
    
    public function testCreateShufflingFromNonShufflableInteraction() {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');
        $shuffling = StateUtils::createShufflingFromInteraction($textEntryInteraction);
        $this->assertFalse($shuffling);
    }
    
    public function testCreateShufflingWithShuffleFalse() {
        $choiceCollection = new SimpleChoiceCollection();
        $choiceCollection[] = new SimpleChoice('id1');
        $choiceCollection[] = new SimpleChoice('id2');
        $choiceCollection[] = new SimpleChoice('id3');
        $choiceInteraction = new ChoiceInteraction('RESPONSE', $choiceCollection);
        $choiceInteraction->setShuffle(false);
        
        $shuffling = StateUtils::createShufflingFromInteraction($choiceInteraction);
        $this->assertFalse($shuffling);
    }
}
