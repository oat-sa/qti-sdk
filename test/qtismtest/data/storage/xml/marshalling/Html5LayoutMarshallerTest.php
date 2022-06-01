<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use \RuntimeException;
use qtism\data\content\FlowCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\html5\Figcaption;
use qtism\data\content\xhtml\html5\Figure;
use qtism\data\content\xhtml\Img;
use qtismtest\QtiSmTestCase;

/**
 * Class Html5LayoutMarshallerTest supports figure/figcaption marshalling
 */
class Html5LayoutMarshallerTest extends QtiSmTestCase
{
    private const SUBJECT_XML = '
         <qh5:figure xmlns:qh5="http://www.imsglobal.org/xsd/imsqtiv2p2_html5_v1p0" id="figureId" title="title">
            <img src="assets/local_asset.jpg" alt="alt" width="100" class="imgClass"/>
            <qh5:figcaption id="figcaptionId" role="article">caption text</qh5:figcaption>
         </qh5:figure>
        ';

    public function testUnmarshall()
    {
        $figure = $this->createComponentFromXml(self::SUBJECT_XML, '2.2.2');

        $this::assertInstanceOf(Figure::class, $figure);
        $this::assertEquals('figureId', $figure->getId());
        $this::assertEquals('', $figure->getClass());

        $figureContent = $figure->getContent();
        $this::assertCount(5, $figureContent);
        $this::assertInstanceOf(TextRun::class, $figureContent[0]);
        $this::assertInstanceOf(Img::class, $figureContent[1]);
        $this::assertInstanceOf(TextRun::class, $figureContent[2]);
        $this::assertInstanceOf(Figcaption::class, $figureContent[3]);
        $this::assertInstanceOf(TextRun::class, $figureContent[4]);

        $figcaption = $figureContent[3];
        $this::assertEquals('figcaptionId', $figcaption->getId());

        $figcaptionContent = $figcaption->getContent();
        $this::assertCount(1, $figcaptionContent);
        $this::assertInstanceOf(TextRun::class, $figcaptionContent[0]);
        $this::assertEquals('caption text', $figcaptionContent[0]->getContent());
    }

    public function testMarshall()
    {
        $figCaption = new Figcaption(null, 'article', 'figcaptionId');
        $figCaption->setContent(new FlowCollection([
            new TextRun('caption text')
        ]));

        $img = new Img('assets/local_asset.jpg', 'alt', '', 'imgClass');
        $img->setWidth('100');

        $figure = new Figure('title',null, "figureId");
        $figure->setContent(new FlowCollection([$img, $figCaption]));

        $element = $this->getMarshallerFactory('2.2.2')->createMarshaller($figure)->marshall($figure);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $expected = preg_replace('/\s\s+/', '', self::SUBJECT_XML);
        $this::assertEquals($expected, $dom->saveXML($element));
    }

    public function testMarshallerBelow2p2Fails()
    {
        $figCaption = new Figcaption();
        $figure = new Figure();
        $figure->setContent(new FlowCollection([$figCaption]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "No marshaller implementation found while marshalling component with class name 'figure"
        );

        $this->getMarshallerFactory('2.1.0')->createMarshaller($figure)->marshall($figure);
    }
}