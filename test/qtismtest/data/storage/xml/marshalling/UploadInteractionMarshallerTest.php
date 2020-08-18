<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\UploadInteraction;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;

class UploadInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $uploadInteraction = new UploadInteraction('RESPONSE', 'my-upload');
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $uploadInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory()->createMarshaller($uploadInteraction)->marshall($uploadInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<uploadInteraction id="my-upload" responseIdentifier="RESPONSE"><prompt>Prompt...</prompt></uploadInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
            <uploadInteraction id="my-upload" responseIdentifier="RESPONSE"><prompt>Prompt...</prompt></uploadInteraction>    
        ');

        $component = $this->getMarshallerFactory()->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(UploadInteraction::class, $component);
        $this->assertEquals('my-upload', $component->getId());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());

        $this->assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());
    }
}
