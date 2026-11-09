<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\Math;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtism\data\content\PrintedVariable;
use qtism\data\content\TextOrVariableCollection;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

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

    public function testMarshallMath()
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

    public function testUnmarshallMath()
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

    public function testMarshallMathErrorBefore22()
    {
        $this->expectException(MarshallingException::class);
        $this->expectExceptionMessage("An 'inlineChoice' element must not contain 'math' elements before QTI 2.2.");

        $math = '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML">'
            . '<m:mrow><m:mi>x</m:mi><m:mo>=</m:mo><m:mi>y</m:mi></m:mrow></m:math>';
        $choice = new InlineChoice('choice1');
        $choice->setContent(new TextOrVariableCollection([new TextRun('value: '), new Math($math)]));

        $this->getMarshallerFactory('2.1.0')->createMarshaller($choice)->marshall($choice);
    }

    public function testUnmarshallMathErrorBefore22()
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
}
