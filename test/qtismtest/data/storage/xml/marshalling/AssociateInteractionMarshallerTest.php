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
use qtism\data\storage\xml\marshalling\UnmarshallingException;

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
        $component->setMinAssociations(1);
        $component->setXmlBase('/home/jerome');
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $component->setPrompt($prompt);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<associateInteraction responseIdentifier="RESPONSE" maxAssociations="2" minAssociations="1" xml:base="/home/jerome"><prompt>Prompt...</prompt><simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice><simpleAssociableChoice identifier="choice_2" matchMax="2" matchMin="1">Choice #2</simpleAssociableChoice></associateInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <associateInteraction responseIdentifier="RESPONSE" maxAssociations="2" xml:base="/home/jerome">
              <prompt>Prompt...</prompt>
              <simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>
              <simpleAssociableChoice identifier="choice_2" matchMax="2" matchMin="1">Choice #2</simpleAssociableChoice>
            </associateInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AssociateInteraction::class, $component);
        $this::assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this::assertFalse($component->mustShuffle());
        $this::assertTrue($component->hasPrompt());
        $this::assertEquals(2, $component->getMaxAssociations());
        $this::assertEquals(0, $component->getMinAssociations());
        $this::assertEquals('/home/jerome', $component->getXmlBase());

        $prompt = $component->getPrompt();
        $content = $prompt->getContent();
        $this::assertEquals('Prompt...', $content[0]->getContent());

        $simpleChoices = $component->getSimpleAssociableChoices();
        $this::assertCount(2, $simpleChoices);
    }

    public function testUnmarshall21NoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <associateInteraction maxAssociations="2" xml:base="/home/jerome">
              <prompt>Prompt...</prompt>
              <simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>
              <simpleAssociableChoice identifier="choice_2" matchMax="2" matchMin="1">Choice #2</simpleAssociableChoice>
            </associateInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'associateInteraction' element.");

        $marshaller->unmarshall($element);
    }

    public function testMarshallSimple20()
    {
        $choice1 = new SimpleAssociableChoice('choice_1', 1);
        $choice1->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));
        $choice2 = new SimpleAssociableChoice('choice_2', 2);
        $choice2->setContent(new FlowStaticCollection([new TextRun('Choice #2')]));
        $choices = new SimpleAssociableChoiceCollection([$choice1, $choice2]);

        $component = new AssociateInteraction('RESPONSE', $choices);

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<associateInteraction responseIdentifier="RESPONSE" shuffle="false" maxAssociations="1"><simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice><simpleAssociableChoice identifier="choice_2" matchMax="2">Choice #2</simpleAssociableChoice></associateInteraction>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshallSimple20
     */
    public function testMarshallMinAssociationAvoided20()
    {
        // Aims at testing that minAssociation is not in the output
        // in a QTI 2.0 context.
        $choice1 = new SimpleAssociableChoice('choice_1', 1);
        $choice1->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));
        $choices = new SimpleAssociableChoiceCollection([$choice1]);

        $component = new AssociateInteraction('RESPONSE', $choices);
        $component->setMinAssociations(1);
        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<associateInteraction responseIdentifier="RESPONSE" shuffle="false" maxAssociations="1"><simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice></associateInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall20()
    {
        $element = $this->createDOMElement('
            <associateInteraction responseIdentifier="RESPONSE" maxAssociations="2" shuffle="true">
              <simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>
              <simpleAssociableChoice identifier="choice_2" matchMax="2">Choice #2</simpleAssociableChoice>
            </associateInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AssociateInteraction::class, $component);
        $this::assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this::assertTrue($component->mustShuffle());
        $this::assertEquals(2, $component->getMaxAssociations());
        $this::assertEquals(0, $component->getMinAssociations());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallAvoidMinAssociations20()
    {
        // Aims at testing that minAssociations has no influence
        // in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <associateInteraction responseIdentifier="RESPONSE" maxAssociations="2" minAssociations="1" shuffle="true">
              <simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>
              <simpleAssociableChoice identifier="choice_2" matchMax="2">Choice #2</simpleAssociableChoice>
            </associateInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        // Default value of minAssociations must remain unchanged...
        $this::assertEquals(0, $component->getMinAssociations());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallExceptionWhenNoMaxAssociations20()
    {
        // Aims at testing that minAssociations has no influence
        // in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <associateInteraction responseIdentifier="RESPONSE" shuffle="false">
              <simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>
            </associateInteraction>
        ');

        $expectedMsg = "The mandatory attribute 'maxAssociations' is missing from the 'associateInteraction' element.";
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage($expectedMsg);
        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallExceptionWhenNoShuffle20()
    {
        // Aims at testing that minAssociations has no influence
        // in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <associateInteraction responseIdentifier="RESPONSE" maxAssociations="1">
              <simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>
            </associateInteraction>
        ');

        $expectedMsg = "The mandatory attribute 'shuffle' is missing from the 'associateInteraction' element.";
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage($expectedMsg);
        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
    }
}
