<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FeedbackInline;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

/**
 * Class FeedbackInlineMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class FeedbackInlineMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $content = new InlineCollection([new TextRun('This is text...')]);
        $feedback = new FeedbackInline('outcome1', 'please_hide_me', ShowHide::HIDE, 'my-feedback', 'super feedback');
        $feedback->setContent($content);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($feedback)->marshall($feedback);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<feedbackInline id="my-feedback" class="super feedback" outcomeIdentifier="outcome1" identifier="please_hide_me" showHide="hide">This is text...</feedbackInline>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <feedbackInline id="my-feedback" class="super feedback" outcomeIdentifier="outcome1" identifier="please_hide_me" showHide="hide">This is text...</feedbackInline>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(FeedbackInline::class, $component);
        $this->assertEquals('my-feedback', $component->getId());
        $this->assertEquals('super feedback', $component->getClass());
        $this->assertEquals('outcome1', $component->getOutcomeIdentifier());
        $this->assertEquals('please_hide_me', $component->getIdentifier());
        $this->assertEquals(ShowHide::HIDE, $component->getShowHide());

        $content = $component->getContent();
        $this->assertEquals(1, count($content));
        $this->assertEquals('This is text...', $content[0]->getContent());
    }
}
