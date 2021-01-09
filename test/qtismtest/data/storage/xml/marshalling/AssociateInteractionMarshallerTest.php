<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\AssociateInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\interactions\SimpleAssociableChoiceCollection;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;

/**
 * Class AssociateInteractionMarshallerTest
 */
class AssociateInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $choice1 = new SimpleAssociableChoice('choice_1', 1);
        $choice1->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));
        $choice2 = new SimpleAssociableChoice('choice_2', 2);
        $choice2->setMatchMin(1);
        $choice2->setContent(new FlowStaticCollection([new TextRun('Choice #2')]));
        $choices = new SimpleAssociableChoiceCollection([$choice1, $choice2]);

        $component = new AssociateInteraction('RESPONSE', $choices);
        $component->setMaxAssociations(2);
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $component->setPrompt($prompt);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<associateInteraction responseIdentifier="RESPONSE" maxAssociations="2"><prompt>Prompt...</prompt><simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice><simpleAssociableChoice identifier="choice_2" matchMax="2" matchMin="1">Choice #2</simpleAssociableChoice></associateInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <associateInteraction responseIdentifier="RESPONSE" maxAssociations="2"><prompt>Prompt...</prompt><simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice><simpleAssociableChoice identifier="choice_2" matchMax="2" matchMin="1">Choice #2</simpleAssociableChoice></associateInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AssociateInteraction::class, $component);
        $this::assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this::assertFalse($component->mustShuffle());
        $this::assertTrue($component->hasPrompt());
        $this::assertEquals(2, $component->getMaxAssociations());
        $this::assertEquals(0, $component->getMinAssociations());

        $prompt = $component->getPrompt();
        $content = $prompt->getContent();
        $this::assertEquals('Prompt...', $content[0]->getContent());

        $simpleChoices = $component->getSimpleAssociableChoices();
        $this::assertEquals(2, count($simpleChoices));
    }
}
