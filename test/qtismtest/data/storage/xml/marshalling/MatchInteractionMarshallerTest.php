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
use qtism\data\storage\xml\marshalling\UnmarshallingException;

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
        $matchInteraction->setShuffle(false);
        $matchInteraction->setXmlBase('/home/jerome');
        $matchInteraction->setMaxAssociations(2);
        $matchInteraction->setMinAssociations(1);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($matchInteraction);
        $element = $marshaller->marshall($matchInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals(
            '<matchInteraction responseIdentifier="RESPONSE" maxAssociations="2" minAssociations="1" xml:base="/home/jerome"><prompt>Prompt...</prompt><simpleMatchSet><simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice><simpleAssociableChoice identifier="choice1B" matchMax="1">choice1B</simpleAssociableChoice></simpleMatchSet><simpleMatchSet><simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice><simpleAssociableChoice identifier="choice2B" matchMax="1">choice2B</simpleAssociableChoice></simpleMatchSet></matchInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <matchInteraction responseIdentifier="RESPONSE" shuffle="true" xml:base="/home/jerome">
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
        $this->assertEquals('/home/jerome', $component->getXmlBase());

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

    public function testUnmarshall21NoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <matchInteraction shuffle="true" xml:base="/home/jerome">
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

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'matchInteraction' element.");

        $marshaller->unmarshall($element);
    }

    public function testUnmarshall21SingleMatchSet()
    {
        $element = $this->createDOMElement('
            <matchInteraction responseIdentifier="RESPONSE" shuffle="true" xml:base="/home/jerome">
              <prompt>Prompt...</prompt>
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice>
                <simpleAssociableChoice identifier="choice1B" matchMax="1">choice1B</simpleAssociableChoice>
              </simpleMatchSet>
            </matchInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A matchInteraction element must contain exactly 2 simpleMatchSet elements, 1' given.");

        $marshaller->unmarshall($element);
    }

    public function testMarshall20()
    {
        $choice1A = new SimpleAssociableChoice('choice1A', 1);
        $choice1A->setContent(new FlowStaticCollection([new TextRun('choice1A')]));

        $choice2A = new SimpleAssociableChoice('choice2A', 1);
        $choice2A->setContent(new FlowStaticCollection([new TextRun('choice2A')]));

        $set1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$choice1A]));
        $set2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$choice2A]));

        $matchInteraction = new MatchInteraction('RESPONSE', new SimpleMatchSetCollection([$set1, $set2]));
        $matchInteraction->setShuffle(true);

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($matchInteraction);
        $element = $marshaller->marshall($matchInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals(
            '<matchInteraction responseIdentifier="RESPONSE" shuffle="true" maxAssociations="1"><simpleMatchSet><simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice></simpleMatchSet><simpleMatchSet><simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice></simpleMatchSet></matchInteraction>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshall20
     */
    public function testMarshallMaxAssociationsShuffleInOutput20()
    {
        // Aims at testing that maxAssociations and shuffle attributes
        // are always in the output while in a QTI 2.0 context.
        $choice1A = new SimpleAssociableChoice('choice1A', 1);
        $choice1A->setContent(new FlowStaticCollection([new TextRun('choice1A')]));

        $choice2A = new SimpleAssociableChoice('choice2A', 1);
        $choice2A->setContent(new FlowStaticCollection([new TextRun('choice2A')]));

        $set1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$choice1A]));
        $set2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$choice2A]));

        $matchInteraction = new MatchInteraction('RESPONSE', new SimpleMatchSetCollection([$set1, $set2]));

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($matchInteraction);
        $element = $marshaller->marshall($matchInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals(
            '<matchInteraction responseIdentifier="RESPONSE" shuffle="false" maxAssociations="1"><simpleMatchSet><simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice></simpleMatchSet><simpleMatchSet><simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice></simpleMatchSet></matchInteraction>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshall20
     */
    public function testMarshallNoMinAssociations()
    {
        // Aims at testing that minAssociations is never in the output
        // in a QTI 2.0 context.
        $choice1A = new SimpleAssociableChoice('choice1A', 1);
        $choice1A->setContent(new FlowStaticCollection([new TextRun('choice1A')]));

        $choice2A = new SimpleAssociableChoice('choice2A', 1);
        $choice2A->setContent(new FlowStaticCollection([new TextRun('choice2A')]));

        $set1 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$choice1A]));
        $set2 = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$choice2A]));

        $matchInteraction = new MatchInteraction('RESPONSE', new SimpleMatchSetCollection([$set1, $set2]));
        $matchInteraction->setMinAssociations(1);

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($matchInteraction);
        $element = $marshaller->marshall($matchInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals(
            '<matchInteraction responseIdentifier="RESPONSE" shuffle="false" maxAssociations="1"><simpleMatchSet><simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice></simpleMatchSet><simpleMatchSet><simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice></simpleMatchSet></matchInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall20()
    {
        $element = $this->createDOMElement('
            <matchInteraction responseIdentifier="RESPONSE" shuffle="true" maxAssociations="2">
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

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(MatchInteraction::class, $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertTrue($component->mustShuffle());
        $this->assertSame(2, $component->getMaxAssociations());
        $this->assertSame(0, $component->getMinAssociations());

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

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallMandatoryShuffle20()
    {
        // This test aims at testing that shuffle
        // is a mandatory attribute in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <matchInteraction responseIdentifier="RESPONSE" maxAssociations="1">
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice>
              </simpleMatchSet>
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice>
              </simpleMatchSet>
            </matchInteraction>
        ');

        $expectedMsg = "The mandatory attribute 'shuffle' is missing from the 'matchInteraction' element.";
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage($expectedMsg);
        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallMandatoryMaxAssociations20()
    {
        // Aims at testing that the maxAssociations attribute is
        // mandatory in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <matchInteraction responseIdentifier="RESPONSE" shuffle="false">
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice>
              </simpleMatchSet>
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice>
              </simpleMatchSet>
            </matchInteraction>
        ');

        $expectedMsg = "The mandatory attribute 'maxAssociations' is missing from the 'matchInteraction' element.";
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage($expectedMsg);
        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallNoMinAssociationsInfluence20()
    {
        // Aims at testing that the maxAssociations attribute is
        // mandatory in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <matchInteraction responseIdentifier="RESPONSE" shuffle="false" maxAssociations="1" minAssociations="1">
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice1A" matchMax="1">choice1A</simpleAssociableChoice>
              </simpleMatchSet>
              <simpleMatchSet>
                <simpleAssociableChoice identifier="choice2A" matchMax="1">choice2A</simpleAssociableChoice>
              </simpleMatchSet>
            </matchInteraction>
        ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertSame(0, $component->getMinAssociations());
    }
}
