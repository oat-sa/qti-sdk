<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\SelectPointInteraction;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

/**
 * Class SelectPointInteractionMarshallerTest
 */
class SelectPointInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $object = new ObjectElement('./myimg.png', 'image/png');
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $selectPointInteraction = new SelectPointInteraction('RESPONSE', $object, 1);
        $selectPointInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($selectPointInteraction)->marshall($selectPointInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<selectPointInteraction responseIdentifier="RESPONSE" maxChoices="1"><prompt>Prompt...</prompt><object data="./myimg.png" type="image/png"/></selectPointInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement(
            '<selectPointInteraction responseIdentifier="RESPONSE" maxChoices="1">
              <prompt>Prompt...</prompt>
              <object data="./myimg.png" type="image/png"/>
            </selectPointInteraction>'
        );

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(SelectPointInteraction::class, $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals(1, $component->getMaxChoices());
        $this->assertEquals(0, $component->getMinChoices());

        $this->assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());

        $object = $component->getObject();
        $this->assertEquals('./myimg.png', $object->getData());
        $this->assertEquals('image/png', $object->getType());
    }
}
