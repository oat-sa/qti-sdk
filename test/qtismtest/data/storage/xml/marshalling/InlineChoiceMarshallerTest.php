<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\PrintedVariable;
use qtism\data\content\TextOrVariableCollection;
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
        $choice->setContent(new TextOrVariableCollection([new PrintedVariable('pr1')]));
        $choice->setFixed(true);
        $choice->setTemplateIdentifier('tpl1');
        $choice->setShowHide(ShowHide::HIDE);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($choice)->marshall($choice);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<inlineChoice id="my-choice1" identifier="choice1" fixed="true" templateIdentifier="tpl1" showHide="hide"><printedVariable identifier="pr1" base="10" powerForm="false" delimiter=";" mappingIndicator="="/></inlineChoice>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('<inlineChoice id="my-choice1" identifier="choice1" fixed="true" templateIdentifier="tpl1" showHide="hide"><printedVariable identifier="pr1" base="10" powerForm="false" delimiter=";" mappingIndicator="="/></inlineChoice>');
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);

        $this->assertInstanceOf(InlineChoice::class, $component);
        $this->assertEquals('my-choice1', $component->getId());
        $this->assertEquals('choice1', $component->getIdentifier());
        $this->assertTrue($component->isFixed());
        $this->assertEquals('tpl1', $component->getTemplateIdentifier());
        $this->assertEquals(ShowHide::HIDE, $component->getShowHide());

        $content = $component->getContent();
        $this->assertEquals(1, count($content));
        $this->assertInstanceOf(PrintedVariable::class, $content[0]);
    }
}
