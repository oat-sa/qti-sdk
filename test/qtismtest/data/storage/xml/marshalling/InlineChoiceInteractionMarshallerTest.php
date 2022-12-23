<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\interactions\InlineChoiceCollection;
use qtism\data\content\interactions\InlineChoiceInteraction;
use qtism\data\content\TextOrVariableCollection;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class InlineChoiceInteractionMarshallerTest
 */
class InlineChoiceInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21(): void
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
        $inlineChoiceInteraction->setXmlBase('/home/jerome');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($inlineChoiceInteraction)->marshall($inlineChoiceInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true" xml:base="/home/jerome"><inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice><inlineChoice identifier="inlineChoice2">Option2</inlineChoice><inlineChoice identifier="inlineChoice3">Option3</inlineChoice></inlineChoiceInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testMarshall20(): void
    {
        // check that suffled systematically out and no required attribute.
        $inlineChoices = new InlineChoiceCollection();

        $choice = new InlineChoice('inlineChoice1');
        $choice->setFixed(true);
        $choice->setContent(new TextOrVariableCollection([new TextRun('Option1')]));
        $inlineChoices[] = $choice;

        $inlineChoiceInteraction = new InlineChoiceInteraction('RESPONSE', $inlineChoices);
        $inlineChoiceInteraction->setShuffle(false);
        $inlineChoiceInteraction->setRequired(true);

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($inlineChoiceInteraction)->marshall($inlineChoiceInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="false"><inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice></inlineChoiceInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall21(): void
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true" xml:base="/home/jerome">
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
        $this::assertCount(3, $inlineChoiceInteraction->getComponentsByClassName('inlineChoice'));
        $this::assertEquals('/home/jerome', $inlineChoiceInteraction->getXmlBase());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoInlineChoices(): void
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true">
            </inlineChoiceInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("An 'inlineChoiceInteraction' element must contain at least 1 'inlineChoice' elements, none given.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21InvalidResponseIdentifier(): void
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="9_RESPONSE" shuffle="true" required="true">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
                <inlineChoice identifier="inlineChoice2">Option2</inlineChoice>
                <inlineChoice identifier="inlineChoice1">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The value of the attribute 'responseIdentifier' for element 'inlineChoiceInteraction' is not a valid identifier.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoResponseIdentifier(): void
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction shuffle="true" required="true">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
                <inlineChoice identifier="inlineChoice2">Option2</inlineChoice>
                <inlineChoice identifier="inlineChoice1">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'inlineChoiceInteraction' element.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshall20(): void
    {
        // Check required is not taken into account.
        // Check shuffle is always in the output.
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
                <inlineChoice identifier="inlineChoice2">Option2</inlineChoice>
                <inlineChoice identifier="inlineChoice1">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');

        $inlineChoiceInteraction = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(InlineChoiceInteraction::class, $inlineChoiceInteraction);
        $this::assertEquals('RESPONSE', $inlineChoiceInteraction->getResponseIdentifier());
        $this::assertTrue($inlineChoiceInteraction->mustShuffle());
        $this::assertFalse($inlineChoiceInteraction->isRequired());
        $this::assertCount(3, $inlineChoiceInteraction->getComponentsByClassName('inlineChoice'));
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallErrorIfoShuffle20(): void
    {
        $expectedMsg = "The mandatory 'shuffle' attribute is missing from the 'inlineChoiceInteraction' element.";
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage($expectedMsg);

        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');

        $inlineChoiceInteraction = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }
}
