<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\interactions\InlineChoiceCollection;
use qtism\data\content\interactions\InlineChoiceInteraction;
use qtism\data\content\TextOrVariableCollection;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;

/**
 * Class InlineChoiceInteractionMarshallerTest
 */
class InlineChoiceInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $inlineChoices = new InlineChoiceCollection();

        $choice = new InlineChoice('inlineChoice1');
        $choice->setFixed(true);
        $choice->setContent(new TextOrVariableCollection([new TextRun('Option1')]));
        $inlineChoices[] = $choice;

        $choice = new InlineChoice('inlineChoice2');
        $choice->setContent(new TextOrVariableCollection([new TextRun('Option2')]));
        $inlineChoices[] = $choice;

        $choice = new InlineChoice('inlineChoice3');
        $choice->setContent(new TextOrVariableCollection([new TextRun('Option3')]));
        $inlineChoices[] = $choice;

        $inlineChoiceInteraction = new InlineChoiceInteraction('RESPONSE', $inlineChoices);
        $inlineChoiceInteraction->setShuffle(true);
        $inlineChoiceInteraction->setRequired(true);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($inlineChoiceInteraction)->marshall($inlineChoiceInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true"><inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice><inlineChoice identifier="inlineChoice2">Option2</inlineChoice><inlineChoice identifier="inlineChoice3">Option3</inlineChoice></inlineChoiceInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
                <inlineChoice identifier="inlineChoice2">Option2</inlineChoice>
                <inlineChoice identifier="inlineChoice1">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');

        $inlineChoiceInteraction = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(InlineChoiceInteraction::class, $inlineChoiceInteraction);
        $this::assertEquals('RESPONSE', $inlineChoiceInteraction->getResponseIdentifier());
        $this::assertTrue($inlineChoiceInteraction->mustShuffle());
        $this::assertTrue($inlineChoiceInteraction->isRequired());
        $this::assertEquals(3, count($inlineChoiceInteraction->getComponentsByClassName('inlineChoice')));
    }
}
