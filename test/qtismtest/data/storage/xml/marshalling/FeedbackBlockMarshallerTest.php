<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FeedbackBlock;
use qtism\data\content\FlowCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Div;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

/**
 * Class FeedbackBlockMarshallerTest
 */
class FeedbackBlockMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('This is text...')]));
        $content = new FlowCollection();
        $content[] = $div;
        $feedback = new FeedbackBlock('outcome1', 'please_show_me', ShowHide::SHOW);
        $feedback->setContent($content);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($feedback)->marshall($feedback);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<feedbackBlock outcomeIdentifier="outcome1" identifier="please_show_me" showHide="show"><div>This is text...</div></feedbackBlock>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <feedbackBlock outcomeIdentifier="outcome1" identifier="please_show_me" showHide="show"><div>This is text...</div></feedbackBlock>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(FeedbackBlock::class, $component);
        $this::assertEquals('outcome1', $component->getOutcomeIdentifier());
        $this::assertEquals('please_show_me', $component->getIdentifier());
        $this::assertEquals(ShowHide::SHOW, $component->getShowHide());

        $content = $component->getContent();
        $this::assertCount(1, $content);
        $div = $content[0];
        $this::assertInstanceOf(Div::class, $div);

        $divContent = $div->getContent();
        $this::assertCount(1, $divContent);
        $this::assertEquals('This is text...', $divContent[0]->getContent());
    }
}
