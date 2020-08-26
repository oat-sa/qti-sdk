<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\DrawingInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\QtiObject;
use qtismtest\QtiSmTestCase;

/**
 * Class DrawingInteractionMarshallerTest
 */
class DrawingInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $object = new QtiObject('my-canvas.png', 'image/png');
        $drawingInteraction = new DrawingInteraction('RESPONSE', $object, 'my-drawings', 'draw-it');
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $drawingInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory()->createMarshaller($drawingInteraction)->marshall($drawingInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<drawingInteraction id="my-drawings" class="draw-it" responseIdentifier="RESPONSE"><prompt>Prompt...</prompt><object data="my-canvas.png" type="image/png"/></drawingInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = self::createDOMElement('
            <drawingInteraction id="my-drawings" class="draw-it" responseIdentifier="RESPONSE">
                <prompt>Prompt...</prompt>
                <object data="my-canvas.png" type="image/png"/>
            </drawingInteraction>
        ');

        $component = $this->getMarshallerFactory()->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(DrawingInteraction::class, $component);
        $this->assertEquals('my-drawings', $component->getId());
        $this->assertEquals('draw-it', $component->getClass());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());

        $object = $component->getObject();
        $this->assertEquals('my-canvas.png', $object->getData());
        $this->assertEquals('image/png', $object->getType());

        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());
    }
}
