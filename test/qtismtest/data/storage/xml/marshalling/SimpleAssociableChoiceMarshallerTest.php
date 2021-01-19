<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Strong;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

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
        $simpleChoice->setShowHide(ShowHide::HIDE);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleAssociableChoice class="qti-simpleAssociableChoice" identifier="choice_1" matchMax="1" showHide="hide">This is ... <strong>strong</strong>!</simpleAssociableChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <simpleAssociableChoice class="qti-simpleAssociableChoice" identifier="choice_1" matchMin="1" matchMax="2" showHide="hide" templateIdentifier="templateIdentifier">This is ... <strong>strong</strong>!</simpleAssociableChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(SimpleAssociableChoice::class, $component);
        $this::assertEquals('qti-simpleAssociableChoice', $component->getClass());
        $this::assertEquals('choice_1', $component->getIdentifier());
        $this::assertEquals(1, $component->getMatchMin());
        $this::assertEquals(2, $component->getMatchMax());
        $this::assertEquals(ShowHide::HIDE, $component->getShowHide());
        $this::assertEquals('templateIdentifier', $component->getTemplateIdentifier());

        $content = $component->getContent();
        $this::assertInstanceOf(FlowStaticCollection::class, $content);
        $this::assertCount(3, $content);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoMatchMax()
    {
        $element = $this->createDOMElement('
	        <simpleAssociableChoice class="qti-simpleAssociableChoice" identifier="choice_1">Choice #1</simpleAssociableChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'matchMax' attribute is missing from the 'simpleAssociableChoice' element.");

        $marshaller->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoIdentifier()
    {
        $element = $this->createDOMElement('
	        <simpleAssociableChoice matchMax="2">This is ... <strong>strong</strong>!</simpleAssociableChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'identifier' attribute is missing from the 'simpleAssociableChoice' element.");

        $marshaller->unmarshall($element);
    }

    public function testMarshall20()
    {
        $simpleChoice = new SimpleAssociableChoice('choice_1', 1);
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall20()
    {
        $element = $this->createDOMElement('
	        <simpleAssociableChoice identifier="choice_1" matchMax="2">Choice #1</simpleAssociableChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(SimpleAssociableChoice::class, $component);
        $this::assertEquals('choice_1', $component->getIdentifier());
        $this::assertEquals(0, $component->getMatchMin());
        $this::assertEquals(2, $component->getMatchMax());

        $content = $component->getContent();
        $this::assertInstanceOf(FlowStaticCollection::class, $content);
        $this::assertCount(1, $content);
    }
}
