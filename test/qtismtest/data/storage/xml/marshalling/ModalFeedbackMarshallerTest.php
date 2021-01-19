<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\ModalFeedback;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class ModalFeedbackMarshallerTest
 */
class ModalFeedbackMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $content = new FlowStaticCollection([new TextRun('Please show me!')]);
        $modalFeedback = new ModalFeedback('outcome1', 'hello', $content, 'Modal Feedback Example');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($modalFeedback)->marshall($modalFeedback);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<modalFeedback outcomeIdentifier="outcome1" identifier="hello" showHide="show" title="Modal Feedback Example">Please show me!</modalFeedback>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <modalFeedback outcomeIdentifier="outcome1" identifier="hello" showHide="show" title="Modal Feedback Example">Please show me!</modalFeedback>
	    ');

        $modalFeedback = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(ModalFeedback::class, $modalFeedback);
        $this::assertEquals('outcome1', $modalFeedback->getOutcomeIdentifier());
        $this::assertEquals('hello', $modalFeedback->getIdentifier());
        $this::assertEquals(ShowHide::SHOW, $modalFeedback->getShowHide());
        $this::assertEquals('Modal Feedback Example', $modalFeedback->getTitle());

        $content = $modalFeedback->getContent();
        $this::assertCount(1, $content);
        $this::assertEquals('Please show me!', $content[0]->getContent());
    }

    public function testUnmarshallShowHideInvalid()
    {
        $element = $this->createDOMElement('
	        <modalFeedback outcomeIdentifier="outcome1" identifier="hello" showHide="shower" title="Modal Feedback Example">Please show me!</modalFeedback>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("'shower' is not a valid value for the 'showHide' attribute of element 'modalFeedback'.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallInvalidContent()
    {
        $element = $this->createDOMElement('
	        <modalFeedback outcomeIdentifier="outcome1" identifier="hello" showHide="show" title="Modal Feedback Example">
                <choiceInteraction responseIdentifier="RESPONSE">
                    <simpleChoice identifier="IDENTIFIER"></simpleChoice>
                </choiceInteraction>
            </modalFeedback>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The content of the 'modalFeedback' is invalid. It must only contain 'flowStatic' elements.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallNoShowHide()
    {
        $element = $this->createDOMElement('
	        <modalFeedback outcomeIdentifier="outcome1" identifier="hello" title="Modal Feedback Example">Please show me!</modalFeedback>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'showHide' attribute is missing from element 'modalFeedback'.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallNoOutcomeIdentifier()
    {
        $element = $this->createDOMElement('
	        <modalFeedback identifier="hello" showHide="show" title="Modal Feedback Example">Please show me!</modalFeedback>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'outcomeIdentifier' attribute is missing from element 'modalFeedback'.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallNoIdentifier()
    {
        $element = $this->createDOMElement('
	        <modalFeedback outcomeIdentifier="outcome1" showHide="show" title="Modal Feedback Example">Please show me!</modalFeedback>
	    ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'identifier' attribute is missing from element 'modalFeedback'.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
