<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\collections\IdentifierCollection;
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
    public function testMarshall21(): void
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

    /**
     * @depends testMarshall21
     */
    public function testMarshallMatchMin21(): void
    {
        $simpleChoice = new SimpleAssociableChoice('choice_1', 3);
        $simpleChoice->setMatchMin(2);
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));
        $simpleChoice->setTemplateIdentifier('templateIdentifier');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="3" templateIdentifier="templateIdentifier" matchMin="2">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshallMatchGroup21(): void
    {
        // Aims at testing that matchGroup attribute is not
        // in the output in a QTI 2.1 context.
        $simpleChoice = new SimpleAssociableChoice('choice_1', 0);
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));
        $simpleChoice->setMatchGroup(new IdentifierCollection(['identifier1', 'identifier2']));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        // No matchGroup in the output!
        $this::assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="0">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall21(): void
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
    public function testUnmarshallMatchGroup21(): void
    {
        // Aims at testing that matchGroup attribute
        // as no influence at unmarshalling time in a QTI 2.1 context.
        $element = $this->createDOMElement('
	        <simpleAssociableChoice class="qti-simpleAssociableChoice" identifier="choice_1" matchMax="0" matchGroup="identifier1 identifier2">Choice #1</simpleAssociableChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $matchGroup = $component->getMatchGroup();
        $this::assertCount(0, $matchGroup);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoMatchMax(): void
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
    public function testUnmarshall21NoIdentifier(): void
    {
        $element = $this->createDOMElement('
	        <simpleAssociableChoice matchMax="2">This is ... <strong>strong</strong>!</simpleAssociableChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'identifier' attribute is missing from the 'simpleAssociableChoice' element.");

        $marshaller->unmarshall($element);
    }

    public function testMarshall20(): void
    {
        $simpleChoice = new SimpleAssociableChoice('choice_1', 1);
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall20
     */
    public function testMarshallNoTemplateIdentifierNoShowHideNoMatchMin20(): void
    {
        // Aims at testing that attributes templateIdentifier, showHide, matchMin
        // are never in the output in a QTI 2.0 context.
        $simpleChoice = new SimpleAssociableChoice('choice_1', 3);
        $simpleChoice->setMatchMin(2);
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));
        $simpleChoice->setTemplateIdentifier('XTEMPLATE');
        $simpleChoice->setShowHide(ShowHide::HIDE);

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="3">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall20
     */
    public function testMarshallMatchGroup20(): void
    {
        // Aims at testing that matchGroup is in the output
        // in a QTI 2.0 context.
        $simpleChoice = new SimpleAssociableChoice('choice_1', 0);
        $simpleChoice->setContent(new FlowStaticCollection([new TextRun('Choice #1')]));
        $simpleChoice->setMatchGroup(new IdentifierCollection(['identifier1', 'identifier2']));

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($simpleChoice);
        $element = $marshaller->marshall($simpleChoice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="0" matchGroup="identifier1 identifier2">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall20(): void
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

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallNoTemplateIdentifierShowHideMatchMinInfluence20(): void
    {
        // Aims at testing that matchMin, showHide and templateIdentifier attributes
        // have no influence in a QTI 2.0 context.
        $element = $this->createDOMElement('
	        <simpleAssociableChoice identifier="choice_1" matchMin="2" matchMax="3" templateIdentifier="XTEMPLATE" showHide="hide">Choice #1</simpleAssociableChoice>
	    ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertEquals(0, $component->getMatchMin());
        $this::assertFalse($component->hasTemplateIdentifier());
        $this::assertEquals(ShowHide::SHOW, $component->getShowHide());
    }
}
