<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\HotspotChoice;
use qtism\data\content\interactions\HotspotChoiceCollection;
use qtism\data\content\interactions\HotspotInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class HotspotInteractionMarshallerTest
 */
class HotspotInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));

        $choice1 = new HotspotChoice('hotspotchoice1', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [77, 115, 8]));
        $choice2 = new HotspotChoice('hotspotchoice2', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [118, 184, 8]));
        $choice3 = new HotspotChoice('hotspotchoice3', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [150, 235, 8]));

        $object = new ObjectElement('./img/img.png', 'image/png');
        $hotspotInteraction = new HotspotInteraction('RESPONSE', $object, new HotspotChoiceCollection([$choice1, $choice2, $choice3]), 'my-hotspot');
        $hotspotInteraction->setMaxChoices(1);
        $hotspotInteraction->setPrompt($prompt);
        $hotspotInteraction->setMinChoices(1);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($hotspotInteraction)->marshall($hotspotInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals(
            '<hotspotInteraction id="my-hotspot" responseIdentifier="RESPONSE" maxChoices="1" minChoices="1"><prompt>Prompt...</prompt><object data="./img/img.png" type="image/png"/><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/><hotspotChoice identifier="hotspotchoice2" shape="circle" coords="118,184,8"/><hotspotChoice identifier="hotspotchoice3" shape="circle" coords="150,235,8"/></hotspotInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testMarshall21XmlBase()
    {
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));

        $choice1 = new HotspotChoice('hotspotchoice1', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [77, 115, 8]));
        $choice2 = new HotspotChoice('hotspotchoice2', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [118, 184, 8]));
        $choice3 = new HotspotChoice('hotspotchoice3', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [150, 235, 8]));

        $object = new ObjectElement('./img/img.png', 'image/png');
        $hotspotInteraction = new HotspotInteraction('RESPONSE', $object, new HotspotChoiceCollection([$choice1, $choice2, $choice3]), 'my-hotspot');
        $hotspotInteraction->setMaxChoices(1);
        $hotspotInteraction->setPrompt($prompt);
        $hotspotInteraction->setMinChoices(1);
        $hotspotInteraction->setXmlBase('/home/jerome');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($hotspotInteraction)->marshall($hotspotInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals(
            '<hotspotInteraction id="my-hotspot" responseIdentifier="RESPONSE" maxChoices="1" minChoices="1" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="./img/img.png" type="image/png"/><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/><hotspotChoice identifier="hotspotchoice2" shape="circle" coords="118,184,8"/><hotspotChoice identifier="hotspotchoice3" shape="circle" coords="150,235,8"/></hotspotInteraction>',
            $dom->saveXML($element)
        );
    }

    /**
     * @depends testMarshall21
     */
    public function testMarshall20()
    {
        // minChoices must be ignored.
        $choice1 = new HotspotChoice('hotspotchoice1', QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, [77, 115, 8]));

        $object = new ObjectElement('./img/img.png', 'image/png');
        $hotspotInteraction = new HotspotInteraction('RESPONSE', $object, new HotspotChoiceCollection([$choice1]));
        $hotspotInteraction->setMaxChoices(1);
        $hotspotInteraction->setMinChoices(1);

        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($hotspotInteraction)->marshall($hotspotInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<hotspotInteraction responseIdentifier="RESPONSE" maxChoices="1"><object data="./img/img.png" type="image/png"/><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/></hotspotInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <hotspotInteraction id="my-hotspot" responseIdentifier="RESPONSE" maxChoices="1"><prompt>Prompt...</prompt><object data="./img/img.png" type="image/png"/><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/><hotspotChoice identifier="hotspotchoice2" shape="circle" coords="118,184,8"/><hotspotChoice identifier="hotspotchoice3" shape="circle" coords="150,235,8"/></hotspotInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(HotspotInteraction::class, $component);
        $this::assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this::assertEquals('my-hotspot', $component->getId());
        $this::assertEquals(1, $component->getMaxChoices());
        $this::assertEquals(0, $component->getMinChoices());

        $this::assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this::assertEquals('Prompt...', $promptContent[0]->getContent());

        $object = $component->getObject();
        $this::assertEquals('./img/img.png', $object->getData());
        $this::assertEquals('image/png', $object->getType());

        $choices = $component->getHotspotChoices();
        $this::assertCount(3, $choices);
        $this::assertEquals('hotspotchoice1', $choices[0]->getIdentifier());
        $this::assertEquals('hotspotchoice2', $choices[1]->getIdentifier());
        $this::assertEquals('hotspotchoice3', $choices[2]->getIdentifier());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21XmlBase()
    {
        $element = $this->createDOMElement('
            <hotspotInteraction id="my-hotspot" responseIdentifier="RESPONSE" maxChoices="1" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="./img/img.png" type="image/png"/><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/><hotspotChoice identifier="hotspotchoice2" shape="circle" coords="118,184,8"/><hotspotChoice identifier="hotspotchoice3" shape="circle" coords="150,235,8"/></hotspotInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(HotspotInteraction::class, $component);
        $this::assertEquals('/home/jerome', $component->getXmlBase());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21InvalidContentIgnored()
    {
        $element = $this->createDOMElement('
            <hotspotInteraction id="my-hotspot" responseIdentifier="RESPONSE" maxChoices="1"><prompt>Prompt...</prompt><object data="./img/img.png" type="image/png"/><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/><simpleChoice identifier="simplechoice"/></hotspotInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(HotspotInteraction::class, $component);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoChoices()
    {
        $element = $this->createDOMElement('
            <hotspotInteraction id="my-hotspot" responseIdentifier="RESPONSE" maxChoices="1"><prompt>Prompt...</prompt><object data="./img/img.png" type="image/png"/></hotspotInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("An 'hotspotInteraction' element must contain at least one 'hotspotChoice' element, none given");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoObject()
    {
        $element = $this->createDOMElement('
            <hotspotInteraction id="my-hotspot" responseIdentifier="RESPONSE" maxChoices="1"><prompt>Prompt...</prompt><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/><hotspotChoice identifier="hotspotchoice2" shape="circle" coords="118,184,8"/><hotspotChoice identifier="hotspotchoice3" shape="circle" coords="150,235,8"/></hotspotInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'hotspotInteraction' element must contain exactly one 'object' element, none given.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <hotspotInteraction id="my-hotspot" maxChoices="1"><prompt>Prompt...</prompt><object data="./img/img.png" type="image/png"/><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/><hotspotChoice identifier="hotspotchoice2" shape="circle" coords="118,184,8"/><hotspotChoice identifier="hotspotchoice3" shape="circle" coords="150,235,8"/></hotspotInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'hotspotInteraction' element.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshall20MissingMaxChoices()
    {
        $element = $this->createDOMElement('
            <hotspotInteraction id="my-hotspot" responseIdentifier="RESPONSE"><prompt>Prompt...</prompt><object data="./img/img.png" type="image/png"/><hotspotChoice identifier="hotspotchoice1" shape="circle" coords="77,115,8"/><simpleChoice identifier="simplechoice"/></hotspotInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'maxChoices' attribute is missing from the 'hotspotInteraction' element.");

        $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }
}
