<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\Math;
use qtism\data\content\PrintedVariable;
use qtism\data\content\TextOrVariableCollection;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class InlineChoiceMarshallerTest
 */
class InlineChoiceMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21(): void
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

    public function testMarshall20(): void
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

    public function testUnmarshall21(): void
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

    public function testUnmarshall20(): void
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

    public function testMarshallMath(): void
    {
        $math = '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML">'
            . '<m:mrow><m:mi>x</m:mi><m:mo>=</m:mo><m:mi>y</m:mi></m:mrow></m:math>';
        $choice = new InlineChoice('choice1');
        $choice->setContent(new TextOrVariableCollection([new TextRun('value: '), new Math($math)]));

        $element = $this->getMarshallerFactory('2.2.0')->createMarshaller($choice)->marshall($choice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<inlineChoice identifier="choice1">value: ' . $math . '</inlineChoice>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshallMath(): void
    {
        $element = $this->createDOMElement(
            '<inlineChoice identifier="choice1">value: '
            . '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML">'
            . '<m:mrow><m:mi>x</m:mi><m:mo>=</m:mo><m:mi>y</m:mi></m:mrow></m:math></inlineChoice>'
        );
        $component = $this->getMarshallerFactory('2.2.0')->createMarshaller($element)->unmarshall($element);

        $this::assertInstanceOf(InlineChoice::class, $component);

        $content = $component->getContent();
        $this::assertCount(2, $content);
        $this::assertInstanceOf(TextRun::class, $content[0]);
        $this::assertInstanceOf(Math::class, $content[1]);
    }

    public function testMarshallMathErrorBefore22(): void
    {
        $this->expectException(MarshallingException::class);
        $this->expectExceptionMessage("An 'inlineChoice' element must not contain 'math' elements before QTI 2.2.");

        $math = '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML">'
            . '<m:mrow><m:mi>x</m:mi><m:mo>=</m:mo><m:mi>y</m:mi></m:mrow></m:math>';
        $choice = new InlineChoice('choice1');
        $choice->setContent(new TextOrVariableCollection([new TextRun('value: '), new Math($math)]));

        $this->getMarshallerFactory('2.1.0')->createMarshaller($choice)->marshall($choice);
    }

    public function testUnmarshallMathErrorBefore22(): void
    {
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("An 'inlineChoice' element must not contain 'math' elements before QTI 2.2.");

        $element = $this->createDOMElement(
            '<inlineChoice identifier="choice1">value: '
            . '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML">'
            . '<m:mrow><m:mi>x</m:mi><m:mo>=</m:mo><m:mi>y</m:mi></m:mrow></m:math></inlineChoice>'
        );
        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallErrorIfPrintedVariable20(): void
    {
        $expectedMsg = "An 'inlineChoice' element must only contain text. Children elements found.";
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage($expectedMsg);

        $element = $this->createDOMElement('<inlineChoice identifier="choice1">var: <printedVariable identifier="pr1"/></inlineChoice>');
        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }
}
