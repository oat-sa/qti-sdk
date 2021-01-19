<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\GapText;
use qtism\data\content\PrintedVariable;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtism\data\content\xhtml\text\Strong;

/**
 * Class GapTextMarshallerTest
 */
class GapTextMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $gapText = new GapText('gapText1', 1);
        $gapText->setContent(new FlowStaticCollection([new TextRun('My var is '), new PrintedVariable('var1')]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gapText);
        $element = $marshaller->marshall($gapText);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<gapText identifier="gapText1" matchMax="1">My var is <printedVariable identifier="var1" base="10" powerForm="false" delimiter=";" mappingIndicator="="/></gapText>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <gapText identifier="gapText1" matchMax="1">My var is <printedVariable identifier="var1" base="10" powerForm="false" delimiter=";" mappingIndicator="="/></gapText>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $gapText = $marshaller->unmarshall($element);

        $this::assertInstanceOf(GapText::class, $gapText);
        $this::assertEquals('gapText1', $gapText->getIdentifier());
        $this::assertEquals(1, $gapText->getMatchMax());
        $this::assertEquals(0, $gapText->getMatchMin());
        $this::assertFalse($gapText->isFixed());
        $this::assertFalse($gapText->hasTemplateIdentifier());
        $this::assertEquals(ShowHide::SHOW, $gapText->getShowHide());

        $content = $gapText->getContent();
        $this::assertInstanceOf(FlowStaticCollection::class, $content);
        $this::assertInstanceOf(TextRun::class, $content[0]);
        $this::assertInstanceOf(PrintedVariable::class, $content[1]);
        $this::assertEquals('My var is ', $content[0]->getContent());
        $this::assertEquals('var1', $content[1]->getIdentifier());
    }

    public function testUnmarshallComplexContentForQti22()
    {
        $element = $this->createDOMElement('
	        <gapText identifier="gapText1" matchMax="1">My var is <strong>invalid</strong>!</gapText>
	    ');

        $gapText = $this->getMarshallerFactory('2.2.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(GapText::class, $gapText);
        $this::assertEquals('gapText1', $gapText->getIdentifier());
        $this::assertEquals(1, $gapText->getMatchMax());
        $this::assertEquals(0, $gapText->getMatchMin());
        $this::assertFalse($gapText->isFixed());
        $this::assertFalse($gapText->hasTemplateIdentifier());
        $this::assertEquals(ShowHide::SHOW, $gapText->getShowHide());

        $this::assertCount(3, $gapText->getContent());
        $this::assertInstanceOf(TextRun::class, $gapText->getContent()[0]);
        $this::assertInstanceOf(Strong::class, $gapText->getContent()[1]);
        $this::assertInstanceOf(TextRun::class, $gapText->getContent()[2]);
    }

    public function testUnmarshallInvalid21()
    {
        // Only textRun and/or printedVariable.
        $this->expectException(UnmarshallingException::class);
        $element = $this->createDOMElement('
	        <gapText identifier="gapText1" matchMax="1">My var is <div>invalid</div>!</gapText>
	    ');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
