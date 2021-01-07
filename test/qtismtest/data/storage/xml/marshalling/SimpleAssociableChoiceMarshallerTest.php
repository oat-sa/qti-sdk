<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Strong;
use qtismtest\QtiSmTestCase;

/**
 * Class SimpleAssociableChoiceMarshallerTest
 */
class SimpleAssociableChoiceMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $simpleChoice = new SimpleAssociableChoice('choice_1', 1);
        $simpleChoice->setClass('qti-simpleAssociableChoice');
        $strong = new Strong();
        $strong->setContent(new InlineCollection([new TextRun('strong')]));
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('This is ... '), $strong, new TextRun('!')]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<simpleAssociableChoice class="qti-simpleAssociableChoice" identifier="choice_1" matchMax="1">This is ... <strong>strong</strong>!</simpleAssociableChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <simpleAssociableChoice class="qti-simpleAssociableChoice" identifier="choice_1" matchMin="1" matchMax="2">This is ... <strong>strong</strong>!</simpleAssociableChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(SimpleAssociableChoice::class, $component);
        $this->assertEquals('qti-simpleAssociableChoice', $component->getClass());
        $this->assertEquals('choice_1', $component->getIdentifier());
        $this->assertEquals(1, $component->getMatchMin());
        $this->assertEquals(2, $component->getMatchMax());

        $content = $component->getContent();
        $this->assertInstanceOf(FlowStaticCollection::class, $content);
        $this->assertEquals(3, count($content));
    }
}
