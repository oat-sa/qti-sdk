<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\DrawingInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class DrawingInteractionMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class DrawingInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $object = new ObjectElement('my-canvas.png', 'image/png');
        $drawingInteraction = new DrawingInteraction('RESPONSE', $object, 'my-drawings', 'draw-it');
        $drawingInteraction->setXmlBase('/home/jerome');
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $drawingInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($drawingInteraction)->marshall($drawingInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<drawingInteraction id="my-drawings" class="draw-it" responseIdentifier="RESPONSE" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="my-canvas.png" type="image/png"/></drawingInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
            <drawingInteraction id="my-drawings" class="draw-it" responseIdentifier="RESPONSE" xml:base="/home/jerome">
                <prompt>Prompt...</prompt>
                <object data="my-canvas.png" type="image/png"/>
            </drawingInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(DrawingInteraction::class, $component);
        $this->assertEquals('my-drawings', $component->getId());
        $this->assertEquals('draw-it', $component->getClass());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals('/home/jerome', $component->getXmlBase());

        $object = $component->getObject();
        $this->assertEquals('my-canvas.png', $object->getData());
        $this->assertEquals('image/png', $object->getType());

        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoObject()
    {
        $element = $this->createDOMElement('
            <drawingInteraction id="my-drawings" class="draw-it" responseIdentifier="RESPONSE" xml:base="/home/jerome">
                <prompt>Prompt...</prompt>
            </drawingInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'drawingInteraction' element must contain exactly one 'object' element, none given.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <drawingInteraction id="my-drawings" class="draw-it" xml:base="/home/jerome">
                <prompt>Prompt...</prompt>
                <object data="my-canvas.png" type="image/png"/>
            </drawingInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'drawingInteraction' element.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
