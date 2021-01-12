<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\PrintedVariable;
use qtism\data\content\TextOrVariableCollection;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class InlineChoiceMarshallerTest
 */
class InlineChoiceMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $choice = new InlineChoice('choice1', 'my-choice1');
        $choice->setContent(new TextOrVariableCollection([new TextRun('var: '), new PrintedVariable('pr1')]));
        $choice->setFixed(true);
        $choice->setTemplateIdentifier('tpl1');
        $choice->setShowHide(ShowHide::HIDE);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($choice)->marshall($choice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<inlineChoice id="my-choice1" identifier="choice1" fixed="true" templateIdentifier="tpl1" showHide="hide">var: <printedVariable identifier="pr1" base="10" powerForm="false" delimiter=";" mappingIndicator="="/></inlineChoice>', $dom->saveXML($element));
    }

    public function testMarshall20()
    {
        $choice = new InlineChoice('choice1');
        $choice->setContent(new TextOrVariableCollection([new TextRun('var: '), new PrintedVariable('pr1')]));
        $choice->setTemplateIdentifier('tpl1');
        $choice->setShowHide(ShowHide::HIDE);

        // Make sure there is no output for templateIdentifier, showHide, and nested printedVariable elements.

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($choice)->marshall($choice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<inlineChoice identifier="choice1">var: </inlineChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('<inlineChoice id="my-choice1" identifier="choice1" fixed="true" templateIdentifier="tpl1" showHide="hide"><printedVariable identifier="pr1" base="10" powerForm="false" delimiter=";" mappingIndicator="="/></inlineChoice>');
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);

        $this::assertInstanceOf(InlineChoice::class, $component);
        $this::assertEquals('my-choice1', $component->getId());
        $this::assertEquals('choice1', $component->getIdentifier());
        $this::assertTrue($component->isFixed());
        $this::assertEquals('tpl1', $component->getTemplateIdentifier());
        $this::assertEquals(ShowHide::HIDE, $component->getShowHide());

        $content = $component->getContent();
        $this::assertCount(1, $content);
        $this::assertInstanceOf(PrintedVariable::class, $content[0]);
    }

    public function testUnmarshall20()
    {
        // Make sure no values for templateIdentifier and showHide are
        // retrieved in a QTI 2.0 context.
        $element = $this->createDOMElement('<inlineChoice id="my-choice1" identifier="choice1" fixed="true" templateIdentifier="tpl1" showHide="hide">Choice #1</inlineChoice>');
        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);

        $this::assertInstanceOf(InlineChoice::class, $component);
        $this::assertEquals('my-choice1', $component->getId());
        $this::assertEquals('choice1', $component->getIdentifier());
        $this::assertTrue($component->isFixed());
        $this::assertFalse($component->hasTemplateIdentifier());
        $this::assertEquals(ShowHide::SHOW, $component->getShowHide());

        $content = $component->getContent();
        $this::assertCount(1, $content);
        $this::assertInstanceOf(TextRun::class, $content[0]);
        $this::assertEquals('Choice #1', $content[0]->getContent());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallErrorIfPrintedVariable20()
    {
        $expectedMsg = "An 'inlineChoice' element must only contain text. Children elements found.";
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage($expectedMsg);

        $element = $this->createDOMElement('<inlineChoice identifier="choice1">var: <printedVariable identifier="pr1"/></inlineChoice>');
        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }
}
