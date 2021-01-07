<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\Orientation;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;

/**
 * Class ChoiceInteractionMarshallerTest
 */
class ChoiceInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $choice1 = new SimpleChoice('choice_1');
        $choice1->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));
        $choice2 = new SimpleChoice('choice_2');
        $choice2->setContent(new FlowStaticCollection([new TextRun('Choice #2')]));
        $choices = new SimpleChoiceCollection([$choice1, $choice2]);

        $component = new ChoiceInteraction('RESPONSE', $choices);
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $component->setPrompt($prompt);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<choiceInteraction responseIdentifier="RESPONSE"><prompt>Prompt...</prompt><simpleChoice identifier="choice_1">Choice #1</simpleChoice><simpleChoice identifier="choice_2">Choice #2</simpleChoice></choiceInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <choiceInteraction responseIdentifier="RESPONSE"><prompt>Prompt...</prompt><simpleChoice identifier="choice_1">Choice #1</simpleChoice><simpleChoice identifier="choice_2">Choice #2</simpleChoice></choiceInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(ChoiceInteraction::class, $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertFalse($component->mustShuffle());
        $this->assertEquals(Orientation::VERTICAL, $component->getOrientation());
        $this->assertTrue($component->hasPrompt());

        $prompt = $component->getPrompt();
        $content = $prompt->getContent();
        $this->assertEquals('Prompt...', $content[0]->getContent());

        $simpleChoices = $component->getSimpleChoices();
        $this->assertEquals(2, count($simpleChoices));
    }
}
