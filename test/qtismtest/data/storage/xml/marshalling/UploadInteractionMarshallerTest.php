<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\UploadInteraction;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class UploadInteractionMarshallerTest
 */
class UploadInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $uploadInteraction = new UploadInteraction('RESPONSE', 'my-upload');
        $uploadInteraction->setType('image/png');
        $uploadInteraction->setXmlBase('/home/jerome');
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $uploadInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($uploadInteraction)->marshall($uploadInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<uploadInteraction id="my-upload" responseIdentifier="RESPONSE" type="image/png" xml:base="/home/jerome"><prompt>Prompt...</prompt></uploadInteraction>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
            <uploadInteraction id="my-upload" responseIdentifier="RESPONSE" xml:base="/home/jerome"><prompt>Prompt...</prompt></uploadInteraction>    
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(UploadInteraction::class, $component);
        $this->assertEquals('my-upload', $component->getId());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals('/home/jerome', $component->getXmlBase());

        $this->assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());
    }

    public function testUnmarshallNoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <uploadInteraction id="my-upload" xml:base="/home/jerome"><prompt>Prompt...</prompt></uploadInteraction>    
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'uploadInteraction' element.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
