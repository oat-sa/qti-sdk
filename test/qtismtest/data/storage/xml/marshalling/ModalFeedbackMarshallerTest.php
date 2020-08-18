<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\ModalFeedback;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

class ModalFeedbackMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $content = new FlowStaticCollection([new TextRun('Please show me!')]);
        $modalFeedback = new ModalFeedback('outcome1', 'hello', $content, 'Modal Feedback Example');

        $element = $this->getMarshallerFactory()->createMarshaller($modalFeedback)->marshall($modalFeedback);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals('<modalFeedback outcomeIdentifier="outcome1" identifier="hello" showHide="show" title="Modal Feedback Example">Please show me!</modalFeedback>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <modalFeedback outcomeIdentifier="outcome1" identifier="hello" showHide="show" title="Modal Feedback Example">Please show me!</modalFeedback>
	    ');

        $modalFeedback = $this->getMarshallerFactory()->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(ModalFeedback::class, $modalFeedback);
        $this->assertEquals('outcome1', $modalFeedback->getOutcomeIdentifier());
        $this->assertEquals('hello', $modalFeedback->getIdentifier());
        $this->assertEquals(ShowHide::SHOW, $modalFeedback->getShowHide());
        $this->assertEquals('Modal Feedback Example', $modalFeedback->getTitle());

        $content = $modalFeedback->getContent();
        $this->assertEquals(1, count($content));
        $this->assertEquals('Please show me!', $content[0]->getContent());
    }
}
