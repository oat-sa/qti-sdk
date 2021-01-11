<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\interactions\SimpleAssociableChoiceCollection;
use qtism\data\content\interactions\SimpleMatchSet;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;

/**
 * Class SimpleMatchSetMarshallerTest
 */
class SimpleMatchSetMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $associableChoice1 = new SimpleAssociableChoice('choice1', 1);
        $associableChoice1->setContent(new FlowStaticCollection([new TextRun('This is choice1')]));
        $associableChoice2 = new SimpleAssociableChoice('choice2', 2);
        $associableChoice2->setMatchMin(1);
        $associableChoice2->setContent(new FlowStaticCollection([new TextRun('This is choice2')]));

        $simpleMatchSet = new SimpleMatchSet(new SimpleAssociableChoiceCollection([$associableChoice1, $associableChoice2]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($simpleMatchSet);
        $element = $marshaller->marshall($simpleMatchSet);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleMatchSet><simpleAssociableChoice identifier="choice1" matchMax="1">This is choice1</simpleAssociableChoice><simpleAssociableChoice identifier="choice2" matchMax="2" matchMin="1">This is choice2</simpleAssociableChoice></simpleMatchSet>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <simpleMatchSet><simpleAssociableChoice identifier="choice1" matchMax="1">This is choice1</simpleAssociableChoice><simpleAssociableChoice identifier="choice2" matchMax="2" matchMin="1">This is choice2</simpleAssociableChoice></simpleMatchSet>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(SimpleMatchSet::class, $component);

        $choices = $component->getSimpleAssociableChoices();
        $this::assertCount(2, $choices);
    }
}
