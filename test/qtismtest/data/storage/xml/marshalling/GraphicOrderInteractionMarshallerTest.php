<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\GraphicOrderInteraction;
use qtism\data\content\interactions\HotspotChoice;
use qtism\data\content\interactions\HotspotChoiceCollection;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

class GraphicOrderInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));

        $choice1 = new HotspotChoice('choice1', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [0, 0, 15]));
        $choice2 = new HotspotChoice('choice2', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [2, 2, 15]));
        $choice3 = new HotspotChoice('choice3', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [4, 4, 15]));
        $choices = new HotspotChoiceCollection([$choice1, $choice2, $choice3]);

        $object = new ObjectElement('my-img.png', 'image/png');

        $graphicOrderInteraction = new GraphicOrderInteraction('RESPONSE', $object, $choices, 'my-graphicOrder');
        $graphicOrderInteraction->setPrompt($prompt);
        $graphicOrderInteraction->setMinChoices(2);
        $graphicOrderInteraction->setMaxChoices(3);
        $graphicOrderInteraction->setXmlBase('/home/jerome');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($graphicOrderInteraction)->marshall($graphicOrderInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<graphicOrderInteraction id="my-graphicOrder" responseIdentifier="RESPONSE" minChoices="2" maxChoices="3" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="my-img.png" type="image/png"/><hotspotChoice identifier="choice1" shape="circle" coords="0,0,15"/><hotspotChoice identifier="choice2" shape="circle" coords="2,2,15"/><hotspotChoice identifier="choice3" shape="circle" coords="4,4,15"/></graphicOrderInteraction>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshall20()
    {
        // make sure minChoices and maxChoices attributes
        // are not in the output in a QTI 2.0 context.
        $choice1 = new HotspotChoice('choice1', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [0, 0, 15]));
        $choice2 = new HotspotChoice('choice2', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [2, 2, 15]));
        $choice3 = new HotspotChoice('choice3', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [4, 4, 15]));
        $choices = new HotspotChoiceCollection([$choice1, $choice2, $choice3]);

        $object = new ObjectElement('my-img.png', 'image/png');

        $graphicOrderInteraction = new GraphicOrderInteraction('RESPONSE', $object, $choices);
        $graphicOrderInteraction->setMinChoices(2);
        $graphicOrderInteraction->setMaxChoices(3);

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($graphicOrderInteraction)->marshall($graphicOrderInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<graphicOrderInteraction responseIdentifier="RESPONSE"><object data="my-img.png" type="image/png"/><hotspotChoice identifier="choice1" shape="circle" coords="0,0,15"/><hotspotChoice identifier="choice2" shape="circle" coords="2,2,15"/><hotspotChoice identifier="choice3" shape="circle" coords="4,4,15"/></graphicOrderInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <graphicOrderInteraction id="my-graphicOrder" responseIdentifier="RESPONSE" minChoices="2" maxChoices="3" xml:base="/home/jerome">
              <prompt>Prompt...</prompt>
              <object data="my-img.png" type="image/png"/>
              <hotspotChoice identifier="choice1" shape="circle" coords="0,0,15"/>
              <hotspotChoice identifier="choice2" shape="circle" coords="2,2,15"/>
              <hotspotChoice identifier="choice3" shape="circle" coords="4,4,15"/>
            </graphicOrderInteraction>
         ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(GraphicOrderInteraction::class, $component);
        $this->assertEquals('my-graphicOrder', $component->getId());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals(2, $component->getMinChoices());
        $this->assertEquals(3, $component->getMaxChoices());
        $this->assertEquals('/home/jerome', $component->getXmlBase());

        $this->assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());

        $object = $component->getObject();
        $this->assertEquals('my-img.png', $object->getData());
        $this->assertEquals('image/png', $object->getType());

        $choices = $component->getHotspotChoices();
        $this->assertEquals(3, count($choices));
        $this->assertEquals('choice1', $choices[0]->getIdentifier());
        $this->assertEquals('choice2', $choices[1]->getIdentifier());
        $this->assertEquals('choice3', $choices[2]->getIdentifier());
    }

    public function testUnmarshall21SomethingElseThanHotspotChoice()
    {
        $element = $this->createDOMElement('
            <graphicOrderInteraction id="my-graphicOrder" responseIdentifier="RESPONSE" minChoices="2" maxChoices="3">
              <prompt>Prompt...</prompt>
              <object data="my-img.png" type="image/png"/>
              <hotspotChoice identifier="choice1" shape="circle" coords="0,0,15"/>
              <simpleChoice identifier="choice2">Choice 2</simpleChoice>
              <hotspotChoice identifier="choice3" shape="circle" coords="4,4,15"/>
            </graphicOrderInteraction>
         ');

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);

        // Correctly filtered...
        $this->assertTrue(true);
    }

    public function testUnmarshall21NoHotspotChoice()
    {
        $element = $this->createDOMElement('
            <graphicOrderInteraction id="my-graphicOrder" responseIdentifier="RESPONSE" minChoices="2" maxChoices="3">
              <prompt>Prompt...</prompt>
              <object data="my-img.png" type="image/png"/>
            </graphicOrderInteraction>
         ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'graphicOrderInteraction' must contain at least one 'hotspotChoice' element, none given.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshall21NoObject()
    {
        $element = $this->createDOMElement('
            <graphicOrderInteraction id="my-graphicOrder" responseIdentifier="RESPONSE" minChoices="2" maxChoices="3">
              <prompt>Prompt...</prompt>
              <hotspotChoice identifier="choice1" shape="circle" coords="0,0,15"/>
              <hotspotChoice identifier="choice2" shape="circle" coords="4,4,15"/>
            </graphicOrderInteraction>
         ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'graphicOrderInteraction' element must contain exactly one 'object' element, none given.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshall21NoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <graphicOrderInteraction id="my-graphicOrder" minChoices="2" maxChoices="3" xml:base="/home/jerome">
              <prompt>Prompt...</prompt>
              <object data="my-img.png" type="image/png"/>
              <hotspotChoice identifier="choice1" shape="circle" coords="0,0,15"/>
              <hotspotChoice identifier="choice2" shape="circle" coords="2,2,15"/>
              <hotspotChoice identifier="choice3" shape="circle" coords="4,4,15"/>
            </graphicOrderInteraction>
         ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'graphicOrderInteraction' element.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21MinChoicesIs0()
    {
        // graphicOrderInteraction->minChoices = 0 is an endless debate:
        // The Information models says: If specfied, minChoices must be 1 or greater but ...
        // The XSD 2.1 says: xs:integer, [-inf, +inf], optional
        // The XSD 2.1.1 says: xs:nonNegativeInteger, [0, +inf]
        //
        // --> Let's say that if <= 0, we consider it not specfied!

        $element = $this->createDOMElement('
            <graphicOrderInteraction id="my-graphicOrder" responseIdentifier="RESPONSE" minChoices="0" maxChoices="3">
              <prompt>Prompt...</prompt>
              <object data="my-img.png" type="image/png"/>
              <hotspotChoice identifier="choice1" shape="circle" coords="0,0,15"/>
              <hotspotChoice identifier="choice2" shape="circle" coords="2,2,15"/>
              <hotspotChoice identifier="choice3" shape="circle" coords="4,4,15"/>
            </graphicOrderInteraction>
         ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(GraphicOrderInteraction::class, $component);
        $this->assertEquals('my-graphicOrder', $component->getId());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertFalse($component->hasMinChoices());
        $this->assertEquals(3, $component->getMaxChoices());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall20()
    {
        // Make sure minChoices and maxChoices attributes are not taken into account in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <graphicOrderInteraction responseIdentifier="RESPONSE" minChoices="2" maxChoices="3">
              <object data="my-img.png" type="image/png"/>
              <hotspotChoice identifier="choice1" shape="circle" coords="0,0,15"/>
              <hotspotChoice identifier="choice2" shape="circle" coords="2,2,15"/>
              <hotspotChoice identifier="choice3" shape="circle" coords="4,4,15"/>
            </graphicOrderInteraction>
         ');

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);

        $this->assertFalse($component->hasMinChoices());
        $this->assertFalse($component->hasMaxChoices());
    }
}
