<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Strong;
use qtismtest\QtiSmTestCase;

class SimpleChoiceMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $simpleChoice = new SimpleChoice('choice_1');
        $simpleChoice->setClass('qti-simpleChoice');
        $strong = new Strong();
        $strong->setContent(new InlineCollection([new TextRun('strong')]));
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('This is ... '), $strong, new TextRun('!')]));

        $marshaller = $this->getMarshallerFactory()->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<simpleChoice class="qti-simpleChoice" identifier="choice_1">This is ... <strong>strong</strong>!</simpleChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <simpleChoice class="qti-simpleChoice" identifier="choice_1">This is ... <strong>strong</strong>!</simpleChoice>
	    ');

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(SimpleChoice::class, $component);
        $this->assertEquals('qti-simpleChoice', $component->getClass());
        $this->assertEquals('choice_1', $component->getIdentifier());

        $content = $component->getContent();
        $this->assertInstanceOf(FlowStaticCollection::class, $content);
        $this->assertEquals(3, count($content));
    }
}
