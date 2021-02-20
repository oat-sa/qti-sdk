<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Strong;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

/**
 * Class SimpleChoiceMarshallerTest
 */
class SimpleChoiceMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $simpleChoice = new SimpleChoice('choice_1');
        $simpleChoice->setClass('qti-simpleChoice');
        $strong = new Strong();
        $strong->setContent(new InlineCollection([new TextRun('strong')]));
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('This is ... '), $strong, new TextRun('!')]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleChoice class="qti-simpleChoice" identifier="choice_1">This is ... <strong>strong</strong>!</simpleChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <simpleChoice class="qti-simpleChoice" identifier="choice_1">This is ... <strong>strong</strong>!</simpleChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(SimpleChoice::class, $component);
        $this::assertEquals('qti-simpleChoice', $component->getClass());
        $this::assertEquals('choice_1', $component->getIdentifier());

        $content = $component->getContent();
        $this::assertInstanceOf(FlowStaticCollection::class, $content);
        $this::assertCount(3, $content);
    }

    public function testMarshallSimple20()
    {
        $simpleChoice = new SimpleChoice('choice_1');
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleChoice identifier="choice_1">Choice #1</simpleChoice>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshallSimple20
     */
    public function testMarshallNoTemplateIdentifierNoShowHide20()
    {
        // Aims at testing that templateIdentifier and showHide attributes
        // are not taken into accoun in a QTI 2.0 context.
        $simpleChoice = new SimpleChoice('choice_1');
        $simpleChoice->setFixed(true);
        $simpleChoice->setTemplateIdentifier('XTEMPLATE');
        $simpleChoice->setShowHide(ShowHide::HIDE);
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleChoice identifier="choice_1" fixed="true">Choice #1</simpleChoice>', $dom->saveXML($element));
    }

    public function testUnmarshallSimple20()
    {
        $element = $this->createDOMElement('
	        <simpleChoice identifier="choice_1" fixed="true">Choice #1</simpleChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(SimpleChoice::class, $component);
        $this::assertEquals('choice_1', $component->getIdentifier());
        $this::assertEquals(ShowHide::SHOW, $component->getShowHide());
        $this::assertFalse($component->hasTemplateIdentifier());

        $content = $component->getContent();
        $this::assertInstanceOf(FlowStaticCollection::class, $content);
        $this::assertCount(1, $content);
    }

    /**
     * @depends testUnmarshallSimple20
     */
    public function testUnmarshallNoTemplateIdentifierNoShowHide20()
    {
        // Aims at testing that templateIdentifier and showHide attribute have
        // no effect in a QTI 2.0 context.
        $element = $this->createDOMElement('
	        <simpleChoice identifier="choice_1" fixed="true" showHide="hide" templateIdentifier="XTEMPLATE">Choice #1</simpleChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(SimpleChoice::class, $component);
        $this::assertEquals('choice_1', $component->getIdentifier());
        $this::assertEquals(ShowHide::SHOW, $component->getShowHide());
        $this::assertFalse($component->hasTemplateIdentifier());
    }
}
