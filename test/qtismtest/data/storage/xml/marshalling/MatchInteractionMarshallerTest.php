<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\MatchInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\interactions\SimpleAssociableChoiceCollection;
use qtism\data\content\interactions\SimpleMatchSet;
use qtism\data\content\interactions\SimpleMatchSetCollection;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;

/**
 * Class MatchInteractionMarshallerTest
 */
class MatchInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $choice1A = new SimpleAssociableChoice('choice1A', 1);
        $choice1A->setContent(new FlowStaticCollection([new TextRun('choice1A')]));
        $choice1B = new SimpleAssociableChoice('choice1B', 1);
        $choice1B->setContent(new FlowStaticCollection([new TextRun('choice1B')]));

        $choice2A = new SimpleAssociableChoice('choice2A', 1);
        $choice2A->setContent(new FlowStaticCollection([new TextRun('choice2A')]));
        $choice2B = new SimpleAssociableChoice('choice2B', 1);
        $choice2B->setContent(new FlowStaticCollection([new TextRun('choice2B')]));

        $set1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$choice1A, $choice1B]));
        $set2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$choice2A, $choice2B]));

        $matchInteraction = new MatchInteraction('RESPONSE', new SimpleMatchSetCollection([$set1, $set2]));
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $matchInteraction->setPrompt($prompt);
        $matchInteraction->setShuffle(true);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($matchInteraction);
        $element = $marshaller->marshall($matchInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals(
            '<matchInteraction responseIdentifier="RESPONSE" shuffle="true"><prompt>Prompt...</prompt><simpleMatchSet><simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice><simpleAssociableChoice identifier="choice1B" matchMax="1">choice1B</simpleAssociableChoice></simpleMatchSet><simpleMatchSet><simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice><simpleAssociableChoice identifier="choice2B" matchMax="1">choice2B</simpleAssociableChoice></simpleMatchSet></matchInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <matchInteraction responseIdentifier="RESPONSE" shuffle="true">
              <prompt>Prompt...</prompt>
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice>
                <simpleAssociableChoice identifier="choice1B" matchMax="1">choice1B</simpleAssociableChoice>
              </simpleMatchSet>
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice>
                <simpleAssociableChoice identifier="choice2B" matchMax="1">choice2B</simpleAssociableChoice>
              </simpleMatchSet>
            </matchInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(MatchInteraction::class, $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertTrue($component->mustShuffle());
        $this->assertTrue($component->hasPrompt());

        $matchSets = $component->getSimpleMatchSets();
        $set1 = $matchSets[0];
        $associableChoices = $set1->getSimpleAssociableChoices();
        $this->assertEquals('choice1A', $associableChoices[0]->getIdentifier());
        $this->assertEquals('choice1B', $associableChoices[1]->getIdentifier());

        $set2 = $matchSets[1];
        $associableChoices = $set2->getSimpleAssociableChoices();
        $this->assertEquals('choice2A', $associableChoices[0]->getIdentifier());
        $this->assertEquals('choice2B', $associableChoices[1]->getIdentifier());
    }
}
