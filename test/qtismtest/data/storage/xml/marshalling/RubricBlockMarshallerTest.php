<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\RubricBlock;
use qtism\data\content\Stylesheet;
use qtism\data\content\StylesheetCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\H3;
use qtism\data\content\xhtml\text\P;
use qtism\data\View;
use qtism\data\ViewCollection;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class RubricBlockMarshallerTest
 */
class RubricBlockMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        $rubricBlock = $this->createComponentFromXml('
            <rubricBlock class="warning" view="candidate tutor" xml:base="/home/jerome">
                <h3>Be carefull kiddo !</h3>inner text<p>Read the instructions twice.</p>
                <stylesheet href="./stylesheet.css" type="text/css" media="screen"/>
            </rubricBlock>
        ');

        $this::assertInstanceOf(RubricBlock::class, $rubricBlock);
        $this::assertEquals('warning', $rubricBlock->getClass());
        $this::assertEquals(2, count($rubricBlock->getViews()));
        $this::assertEquals('/home/jerome', $rubricBlock->getXmlBase());

        $rubricBlockContent = $rubricBlock->getContent();
        $this::assertEquals(6, count($rubricBlockContent));
        $this::assertInstanceOf(H3::class, $rubricBlockContent[1]);
        $this::assertEquals('Be carefull kiddo !', $rubricBlockContent[1]->getContent()[0]->getContent());
        $this::assertInstanceOf(P::class, $rubricBlockContent[3]);
        $this::assertEquals('Read the instructions twice.', $rubricBlockContent[3]->getContent()[0]->getContent());
        $this::assertEquals('inner text', $rubricBlockContent[2]->getContent());

        $stylesheets = $rubricBlock->getStylesheets();
        $this::assertEquals(1, count($stylesheets));
        $this::assertEquals('./stylesheet.css', $stylesheets[0]->getHref());
        $this::assertEquals('text/css', $stylesheets[0]->getType());
        $this::assertEquals('screen', $stylesheets[0]->getMedia());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoViewsAttribute()
    {
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'views' is missing.");

        $rubricBlock = $this->createComponentFromXml('
            <rubricBlock class="warning" xml:base="/home/jerome">
                <h3>Be carefull kiddo !</h3>inner text<p>Read the instructions twice.</p>
                <stylesheet href="./stylesheet.css" type="text/css" media="screen"/>
            </rubricBlock>
        ');
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallInvalidContent()
    {
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The 'rubricBlock' cannot contain 'choiceInteraction' elements.");

        $rubricBlock = $this->createComponentFromXml('
            <rubricBlock view="tutor" class="warning" xml:base="/home/jerome">
                <choiceInteraction responseIdentifier="RESPONSE">
                    <simpleChoice identifier="identifier"/>
                </choiceInteraction>
            </rubricBlock>
        ');
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallApipAccessibilityInRubricBlock()
    {
        $rubricBlock = $this->createComponentFromXml('
            <rubricBlock class="warning" view="candidate tutor" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
                <h3>Be carefull kiddo !</h3>
                <p>Read the instructions twice.</p>
                <stylesheet href="./stylesheet.css" type="text/css" media="screen"/>
                <apipAccessibility xmlns="http://www.imsglobal.org/xsd/apip/apipv1p0/imsapip_qtiv1p0"/>
            </rubricBlock>
        ');

        $this::assertInstanceOf(RubricBlock::class, $rubricBlock);
    }

    public function testMarshall()
    {
        $stylesheet = new Stylesheet('./stylesheet.css');

        $h3 = new H3();
        $h3->setContent(new InlineCollection([new TextRun('Be carefull kiddo!')]));

        $p = new P();
        $p->setContent(new InlineCollection([new TextRun('Read the instructions twice.')]));

        $rubricBlock = new RubricBlock(new ViewCollection([View::CANDIDATE, View::TUTOR]));
        $rubricBlock->setClass('warning');
        $rubricBlock->setContent(new FlowStaticCollection(([$h3, $p])));
        $rubricBlock->setStylesheets(new StylesheetCollection([$stylesheet]));
        $rubricBlock->setXmlBase('/home/jerome');
        $rubricBlock->setUse('Some use!');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($rubricBlock)->marshall($rubricBlock);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<rubricBlock view="candidate tutor" use="Some use!" xml:base="/home/jerome" class="warning"><h3>Be carefull kiddo!</h3><p>Read the instructions twice.</p><stylesheet href="./stylesheet.css" media="screen" type="text/css"/></rubricBlock>', $dom->saveXML($element));
    }
}
