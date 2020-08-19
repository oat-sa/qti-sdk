<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FeedbackBlock;
use qtism\data\content\FlowCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Div;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

class FeedbackBlockMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun("This is text...")]));
        $content = new FlowCollection();
        $content[] = $div;
        $feedback = new FeedbackBlock('outcome1', 'please_show_me', ShowHide::SHOW);
        $feedback->setContent($content);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($feedback)->marshall($feedback);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<feedbackBlock outcomeIdentifier="outcome1" identifier="please_show_me" showHide="show"><div>This is text...</div></feedbackBlock>', $dom->saveXML($element));
    }

    /**
     * @depends testMarshall
     */
    public function testMarshallXmlBase()
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun("This is text...")]));
        $content = new FlowCollection();
        $content[] = $div;
        $feedback = new FeedbackBlock('outcome1', 'please_show_me', ShowHide::SHOW);
        $feedback->setContent($content);
        $feedback->setXmlBase('/home/jerome');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($feedback)->marshall($feedback);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<feedbackBlock outcomeIdentifier="outcome1" identifier="please_show_me" showHide="show" xml:base="/home/jerome"><div>This is text...</div></feedbackBlock>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <feedbackBlock outcomeIdentifier="outcome1" identifier="please_show_me" showHide="show"><div>This is text...</div></feedbackBlock>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(FeedbackBlock::class, $component);
        $this->assertEquals('outcome1', $component->getOutcomeIdentifier());
        $this->assertEquals('please_show_me', $component->getIdentifier());
        $this->assertEquals(ShowHide::SHOW, $component->getShowHide());

        $content = $component->getContent();
        $this->assertEquals(1, count($content));
        $div = $content[0];
        $this->assertInstanceOf(Div::class, $div);

        $divContent = $div->getContent();
        $this->assertEquals(1, count($divContent));
        $this->assertEquals('This is text...', $divContent[0]->getContent());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallInvalidShowHide()
    {
        $element = $this->createDOMElement('
	        <feedbackBlock outcomeIdentifier="outcome1" identifier="please_show_me" showHide="snow"><div>This is text...</div></feedbackBlock>
	    ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "'snow' is not a valid value for the 'showHide' attribute of element 'feedbackBlock'."
        );

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallInvalidContent1()
    {
        $element = $this->createDOMElement('
	        <feedbackBlock outcomeIdentifier="outcome1" identifier="please_show_me" showHide="show"><simpleChoice identifier="ChoiceA"/></feedbackBlock>
	    ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "A 'simpleChoice' cannot be contained by a 'feedbackBlock'."
        );

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallInvalidContent2()
    {
        $element = $this->createDOMElement('
	        <feedbackBlock outcomeIdentifier="outcome1" identifier="please_show_me" showHide="show"><endAttemptInteraction responseIdentifier="Check" title="My Title"/></feedbackBlock>
	    ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "A 'endAttemptInteraction' cannot be contained by a 'feedbackBlock'."
        );

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallXmlBase()
    {
        $element = $this->createDOMElement('
	        <feedbackBlock xml:base="/home/jerome" outcomeIdentifier="outcome1" identifier="please_show_me" showHide="show"><div>This is text...</div></feedbackBlock>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertEquals('/home/jerome', $component->getXmlBase());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoIdentifier()
    {
        $element = $this->createDOMElement('
	        <feedbackBlock outcomeIdentifier="outcome1" showHide="snow"><div>This is text...</div></feedbackBlock>
	    ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "The mandatory 'identifier' attribute is missing from element 'feedbackBlock'."
        );

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoOutcomeIdentifier()
    {
        $element = $this->createDOMElement('
	        <feedbackBlock identifier="myidentifier" showHide="snow"><div>This is text...</div></feedbackBlock>
	    ');

        $this->setExpectedException(
            UnmarshallingException::class,
            "The mandatory 'outcomeIdentifier' attribute is missing from element 'feedbackBlock'."
        );

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
