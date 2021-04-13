<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\AssociableHotspot;
use qtism\data\content\interactions\AssociableHotspotCollection;
use qtism\data\content\interactions\GraphicAssociateInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class GraphicAssociateInteractionMarshallerTest
 */
class GraphicAssociateInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));

        $object = new ObjectElement('myimg.png', 'image/png');

        $choice1 = new AssociableHotspot('choice1', 2, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [0, 0, 15]));
        $choice1->setMatchMin(1);
        $choice2 = new AssociableHotspot('choice2', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [2, 2, 15]));
        $choice3 = new AssociableHotspot('choice3', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [4, 4, 15]));
        $choices = new AssociableHotspotCollection([$choice1, $choice2, $choice3]);

        $graphicAssociateInteraction = new GraphicAssociateInteraction('RESPONSE', $object, $choices, 'prout');
        $graphicAssociateInteraction->setPrompt($prompt);
        $graphicAssociateInteraction->setMaxAssociations(3);
        $graphicAssociateInteraction->setMinAssociations(2);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($graphicAssociateInteraction)->marshall($graphicAssociateInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals(
            '<graphicAssociateInteraction id="prout" responseIdentifier="RESPONSE" minAssociations="2" maxAssociations="3"><prompt>Prompt...</prompt><object data="myimg.png" type="image/png"/><associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/><associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/><associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/></graphicAssociateInteraction>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshall21XmlBase()
    {
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));

        $object = new ObjectElement('myimg.png', 'image/png');

        $choice1 = new AssociableHotspot('choice1', 2, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [0, 0, 15]));
        $choice1->setMatchMin(1);
        $choice2 = new AssociableHotspot('choice2', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [2, 2, 15]));
        $choice3 = new AssociableHotspot('choice3', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [4, 4, 15]));
        $choices = new AssociableHotspotCollection([$choice1, $choice2, $choice3]);

        $graphicAssociateInteraction = new GraphicAssociateInteraction('RESPONSE', $object, $choices, 'prout');
        $graphicAssociateInteraction->setPrompt($prompt);
        $graphicAssociateInteraction->setMaxAssociations(3);
        $graphicAssociateInteraction->setMinAssociations(2);
        $graphicAssociateInteraction->setXmlBase('/home/jerome');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($graphicAssociateInteraction)->marshall($graphicAssociateInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals(
            '<graphicAssociateInteraction id="prout" responseIdentifier="RESPONSE" minAssociations="2" maxAssociations="3" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="myimg.png" type="image/png"/><associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/><associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/><associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/></graphicAssociateInteraction>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshall20()
    {
        // Make sure that maxAssociations is always in the output but never minAssociations.
        $object = new ObjectElement('myimg.png', 'image/png');

        $choice1 = new AssociableHotspot('choice1', 2, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [0, 0, 15]));
        $choice1->setMatchMin(1);
        $choice2 = new AssociableHotspot('choice2', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [2, 2, 15]));
        $choice3 = new AssociableHotspot('choice3', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [4, 4, 15]));
        $choices = new AssociableHotspotCollection([$choice1, $choice2, $choice3]);

        $graphicAssociateInteraction = new GraphicAssociateInteraction('RESPONSE', $object, $choices);
        $graphicAssociateInteraction->setMaxAssociations(3);
        $graphicAssociateInteraction->setMinAssociations(2);

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($graphicAssociateInteraction)->marshall($graphicAssociateInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals(
            '<graphicAssociateInteraction responseIdentifier="RESPONSE" maxAssociations="3"><object data="myimg.png" type="image/png"/><associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMax="2"/><associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/><associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/></graphicAssociateInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" id="prout" minAssociations="2" maxAssociations="3">
              <prompt>Prompt...</prompt>
              <object data="myimg.png" type="image/png"/>
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/>
            </graphicAssociateInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(GraphicAssociateInteraction::class, $component);
        $this::assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this::assertEquals('prout', $component->getId());
        $this::assertSame(2, $component->getMinAssociations());
        $this::assertSame(3, $component->getMaxAssociations());

        $this::assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this::assertEquals('Prompt...', $promptContent[0]->getContent());

        $object = $component->getObject();
        $this::assertEquals('myimg.png', $object->getData());
        $this::assertEquals('image/png', $object->getType());

        $choices = $component->getAssociableHotspots();
        $this::assertCount(3, $choices);

        $this::assertEquals('choice1', $choices[0]->getIdentifier());
        $this::assertEquals(2, $choices[0]->getMatchMax());
        $this::assertEquals(1, $choices[0]->getMatchMin());
        $this::assertEquals(QtiShape::CIRCLE, $choices[0]->getShape());
        $this::assertTrue($choices[0]->getCoords()->equals(new QtiCoords(QtiShape::CIRCLE, [0, 0, 15])));

        $this::assertEquals('choice2', $choices[1]->getIdentifier());
        $this::assertEquals(1, $choices[1]->getMatchMax());
        $this::assertEquals(0, $choices[1]->getMatchMin());
        $this::assertEquals(QtiShape::CIRCLE, $choices[1]->getShape());
        $this::assertTrue($choices[1]->getCoords()->equals(new QtiCoords(QtiShape::CIRCLE, [2, 2, 15])));

        $this::assertEquals('choice3', $choices[2]->getIdentifier());
        $this::assertEquals(1, $choices[2]->getMatchMax());
        $this::assertEquals(0, $choices[2]->getMatchMin());
        $this::assertEquals(QtiShape::CIRCLE, $choices[2]->getShape());
        $this::assertTrue($choices[2]->getCoords()->equals(new QtiCoords(QtiShape::CIRCLE, [4, 4, 15])));
    }

    public function testUnmarshall21NoAssociableHotspot()
    {
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" id="prout" minAssociations="2" maxAssociations="3">
              <prompt>Prompt...</prompt>
              <object data="myimg.png" type="image/png"/>
            </graphicAssociateInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'graphicAssociateInteraction' element must contain at lease one 'associableHotspot' element, none given.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshall21InvalidContentIgnored()
    {
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" id="prout" minAssociations="2" maxAssociations="3">
              <prompt>Prompt...</prompt>
              <object data="myimg.png" type="image/png"/>
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <simpleChoice identifier="choice3">choice3</simpleChoice>
            </graphicAssociateInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(GraphicAssociateInteraction::class, $component);
        $choices = $component->getAssociableHotspots();
        $this::assertCount(2, $choices);
    }

    public function testUnmarshall20()
    {
        // Make sure minAssociations is not taken into account
        // in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" minAssociations="2" maxAssociations="3">
              <object data="myimg.png" type="image/png"/>
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/>
            </graphicAssociateInteraction>
        ');

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);

        $this::assertSame(0, $component->getMinAssociations());
        $this::assertSame(3, $component->getMaxAssociations());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshall20NoMaxAssociations()
    {
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" minAssociations="2">
              <object data="myimg.png" type="image/png"/>
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/>
            </graphicAssociateInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'maxAssociations' attribute is missing from the 'graphicAssociateInteraction' element.");

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshall20NoAssociableHotspot()
    {
        // Make sure minAssociations is not taken into account
        // in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" minAssociations="2">
              <object data="myimg.png" type="image/png"/>
            </graphicAssociateInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'graphicAssociateInteraction' element must contain at lease one 'associableHotspot' element, none given.");

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshall20NoObject()
    {
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" minAssociations="2">
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/>
            </graphicAssociateInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'graphicAssociateInteraction' element must contain exactly one 'object' element, none given.");

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshall20XmlBase()
    {
        // Make sure minAssociations is not taken into account
        // in a QTI 2.0 context.
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" minAssociations="2" maxAssociations="3" xml:base="/home/jerome">
              <object data="myimg.png" type="image/png"/>
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/>
            </graphicAssociateInteraction>
        ');

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
        $this::assertEquals('/home/jerome', $component->getXmlBase());
    }

    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshall20NoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <graphicAssociateInteraction minAssociations="2" maxAssociations="3">
              <object data="myimg.png" type="image/png"/>
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/>
            </graphicAssociateInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'graphicAssociateInteraction' element");

        $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }
}
