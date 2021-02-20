<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\collections\IdentifierCollection;
use qtism\data\content\interactions\GapText;
use qtism\data\content\PrintedVariable;
use qtism\data\content\TextOrVariableCollection;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class GapTextMarshallerTest
 */
class GapTextMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $gapText = new GapText('gapText1', 1);
        $gapText->setContent(new TextOrVariableCollection([new TextRun('My var is '), new PrintedVariable('var1')]));

        // Make sure there is no output for matchGroup in QTI 2.1.
        $gapText->setMatchGroup(new IdentifierCollection(['identifier1']));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gapText);
        $element = $marshaller->marshall($gapText);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<gapText identifier="gapText1" matchMax="1">My var is <printedVariable identifier="var1" base="10" powerForm="false" delimiter=";" mappingIndicator="="/></gapText>', $dom->saveXML($element));
    }

    public function testMarshall20()
    {
        $gapText = new GapText('gapText1', 3);

        // Make sure that in QTI 2.0, printed variables are not taken into account at output time...
        $gapText->setContent(new TextOrVariableCollection([new TextRun('Some text...'), new PrintedVariable('id1')]));

        // Make sure there is no output for matchMin, templateIdentifier, showHide.
        $gapText->setMatchMin(1);
        $gapText->setTemplateIdentifier('XTEMPLATE');
        $gapText->setShowHide(ShowHide::HIDE);

        // But output for matchGroup attribute which is valid in QTI 2.0.
        $gapText->setMatchGroup(new IdentifierCollection(['identifier1', 'id2']));

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($gapText);
        $element = $marshaller->marshall($gapText);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<gapText identifier="gapText1" matchMax="3" matchGroup="identifier1 id2">Some text...</gapText>', $dom->saveXML($element));
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
        $this::assertInstanceOf(TextOrVariableCollection::class, $content);
        $this::assertInstanceOf(TextRun::class, $content[0]);
        $this::assertInstanceOf(PrintedVariable::class, $content[1]);
        $this::assertEquals('My var is ', $content[0]->getContent());
        $this::assertEquals('var1', $content[1]->getIdentifier());
    }

    public function testUnmarshall20()
    {
        $element = $this->createDOMElement('
	        <gapText identifier="gapText1" matchMax="3" matchMin="1" showHide="hide" templateIdentifier="XTEMPLATE" matchGroup="id1 id2">Some text...</gapText>
	    ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $gapText = $marshaller->unmarshall($element);

        $this::assertInstanceOf(GapText::class, $gapText);
        $this::assertEquals('gapText1', $gapText->getIdentifier());
        $this::assertEquals(3, $gapText->getMatchMax());
        $this::assertEquals(0, $gapText->getMatchMin());
        $this::assertFalse($gapText->isFixed());
        $this::assertFalse($gapText->hasTemplateIdentifier());
        $this::assertEquals(ShowHide::SHOW, $gapText->getShowHide());
        $this::assertEquals(['id1', 'id2'], $gapText->getMatchGroup()->getArrayCopy());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshall20NotOnlyTextContent()
    {
        // Aims at testing that an error is thrown when trying
        // to unmarshall a gapText containing other stuff than plain/text
        // in a QTI 2.0 context.
        $expectedMsg = "A 'gapText' element must only contain text. Children elements found.";
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage($expectedMsg);
        $element = $this->createDOMElement('
	        <gapText identifier="gapText1" matchMax="0">Some text and a <printedVariable identifier="myVar"/></gapText>
	    ');

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $gapText = $marshaller->unmarshall($element);
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
