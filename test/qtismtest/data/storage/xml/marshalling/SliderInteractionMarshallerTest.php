<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\Orientation;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\SliderInteraction;
use qtism\data\content\TextRun;
use qtismtest\QtiSmTestCase;

class SliderInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $sliderInteraction = new SliderInteraction('RESPONSE', 0.0, 100.0, 'my-slider', 'slide-it');
        $sliderInteraction->setStep(1);
        $sliderInteraction->setStepLabel(true);
        $sliderInteraction->setOrientation(Orientation::VERTICAL);
        $sliderInteraction->setReverse(true);
        $sliderInteraction->setXmlBase('/home/jerome');

        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $sliderInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($sliderInteraction)->marshall($sliderInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<sliderInteraction id="my-slider" class="slide-it" responseIdentifier="RESPONSE" lowerBound="0" upperBound="100" step="1" stepLabel="true" orientation="vertical" reverse="true" xml:base="/home/jerome"><prompt>Prompt...</prompt></sliderInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
            <sliderInteraction id="my-slider" class="slide-it" responseIdentifier="RESPONSE" lowerBound="0" upperBound="100" step="1" stepLabel="true" orientation="vertical" reverse="true">
                <prompt>Prompt...</prompt>
            </sliderInteraction>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\SliderInteraction', $component);
        $this->assertEquals('my-slider', $component->getId());
        $this->assertEquals('slide-it', $component->getClass());
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals(0.0, $component->getLowerBound());
        $this->assertEquals(100.0, $component->getUpperBound());
        $this->assertEquals(1, $component->getStep());
        $this->assertTrue($component->mustStepLabel());
        $this->assertEquals(Orientation::VERTICAL, $component->getOrientation());
        $this->assertTrue($component->mustReverse());
        $this->assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallWrongOrientationValue()
    {
        $element = $this->createDOMElement('
            <sliderInteraction id="my-slider" class="slide-it" responseIdentifier="RESPONSE" lowerBound="0" upperBound="100" step="1" stepLabel="true" orientation="vertital" reverse="true">
                <prompt>Prompt...</prompt>
            </sliderInteraction>
        ');

        $this->setExpectedException(
            'qtism\\data\storage\\xml\\marshalling\\UnmarshallingException',
            "The value of the 'orientation' attribute of the 'sliderInteraction' is invalid."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoUpperBound()
    {
        $element = $this->createDOMElement('
            <sliderInteraction id="my-slider" class="slide-it" responseIdentifier="RESPONSE" lowerBound="0" step="1" stepLabel="true" orientation="vertical" reverse="true">
                <prompt>Prompt...</prompt>
            </sliderInteraction>
        ');

        $this->setExpectedException(
            'qtism\\data\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'upperBound' attribute is missing from the 'sliderInteraction' element."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoLowerBound()
    {
        $element = $this->createDOMElement('
            <sliderInteraction id="my-slider" class="slide-it" responseIdentifier="RESPONSE" upperBound="100" step="1" stepLabel="true" orientation="vertical" reverse="true">
                <prompt>Prompt...</prompt>
            </sliderInteraction>
        ');

        $this->setExpectedException(
            'qtism\\data\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'lowerBound' attribute is missing from the 'sliderInteraction' element."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <sliderInteraction id="my-slider" class="slide-it" lowerBound="0" upperBound="100" step="1" stepLabel="true" orientation="vertical" reverse="true">
                <prompt>Prompt...</prompt>
            </sliderInteraction>
        ');

        $this->setExpectedException(
            'qtism\\data\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'responseIdentifier' attribute is missing from the 'sliderInteraction' element."
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
