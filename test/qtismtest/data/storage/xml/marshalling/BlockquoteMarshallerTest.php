<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\BlockCollection;
use qtism\data\content\FlowCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Blockquote;
use qtism\data\content\xhtml\text\Div;
use qtism\data\content\xhtml\text\H4;
use qtismtest\QtiSmTestCase;

/**
 * Class BlockquoteMarshallerTest
 */
class BlockquoteMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        $blockquote = $this->createComponentFromXml('
	        <blockquote class="physics" cite="http://www.world.com/einstein" xml:base="/home/jerome">
                <h4>Albert Einstein</h4>
	            <div class="description">An old Physicist.</div>
	        </blockquote>
	    ');

        $this::assertInstanceOf(Blockquote::class, $blockquote);
        $this::assertEquals('physics', $blockquote->getClass());
        $this::assertEquals('http://www.world.com/einstein', $blockquote->getCite());
        $this::assertEquals('/home/jerome', $blockquote->getXmlBase());

        $blockquoteContent = $blockquote->getContent();
        $this::assertEquals(2, count($blockquoteContent));

        $h4 = $blockquoteContent[0];
        $this::assertInstanceOf(H4::class, $h4);
        $h4Content = $h4->getContent();
        $this::assertEquals(1, count($h4Content));
        $this::assertEquals('Albert Einstein', $h4Content[0]->getContent());

        $div = $blockquoteContent[1];
        $this::assertInstanceOf(Div::class, $div);
        $this::assertEquals('description', $div->getClass());
        $divContent = $div->getContent();
        $this::assertEquals(1, count($divContent));
        $this::assertEquals('An old Physicist.', $divContent[0]->getContent());
    }

    public function testMarshall()
    {
        $div = new Div();
        $div->setClass('description');
        $div->setContent(new FlowCollection([new TextRun('An old Physicist.')]));

        $h4 = new H4();
        $h4->setContent(new InlineCollection([new TextRun('Albert Einstein')]));

        $blockquote = new Blockquote();
        $blockquote->setClass('physics');
        $blockquote->setCite('http://www.world.com/einstein');
        $blockquote->setXmlBase('/home/jerome');
        $blockquote->setContent(new BlockCollection([$h4, $div]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($blockquote)->marshall($blockquote);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<blockquote cite="http://www.world.com/einstein" xml:base="/home/jerome" class="physics"><h4>Albert Einstein</h4><div class="description">An old Physicist.</div></blockquote>', $dom->saveXML($element));
    }
}
