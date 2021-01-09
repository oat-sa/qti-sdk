<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\Direction;
use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\A;
use qtism\data\content\xhtml\text\Bdo;
use qtism\data\content\xhtml\text\Em;
use qtism\data\content\xhtml\text\Q;
use qtism\data\content\xhtml\text\Span;
use qtism\data\content\xhtml\text\Strong;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class SimpleInlineMarshallerTest
 */
class SimpleInlineMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $strong = new Strong('john');
        $strong->setLabel('His name');
        $strong->setContent(new InlineCollection([new TextRun('John Dunbar')]));

        $em = new Em('sentence', 'introduction', 'en-US');
        $em->setContent(new InlineCollection([new TextRun('He is '), $strong, new TextRun('.')]));
        $em->setXmlBase('/home/jerome');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($em);
        $element = $marshaller->marshall($em);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<em id="sentence" class="introduction" xml:lang="en-US" xml:base="/home/jerome">He is <strong id="john" label="His name">John Dunbar</strong>.</em>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<em id="sentence" class="introduction" xml:lang="en-US">He is <strong id="john" label="His name">John Dunbar</strong>.</em>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $em = $marshaller->unmarshall($element);
        $this::assertInstanceOf(Em::class, $em);
        $this::assertEquals('sentence', $em->getId());
        $this::assertEquals('introduction', $em->getClass());
        $this::assertEquals('en-US', $em->getLang());

        $sentence = $em->getContent();
        $this::assertInstanceOf(InlineCollection::class, $sentence);
        $this::assertEquals(3, count($sentence));

        $this::assertInstanceOf(TextRun::class, $sentence[0]);
        $this::assertEquals('He is ', $sentence[0]->getContent());

        $this::assertInstanceOf(Strong::class, $sentence[1]);
        $strongContent = $sentence[1]->getContent();
        $this::assertEquals('John Dunbar', $strongContent[0]->getContent());
        $this::assertEquals('john', $sentence[1]->getId());
        $this::assertEquals('His name', $sentence[1]->getLabel());

        $this::assertInstanceOf(TextRun::class, $sentence[2]);
        $this::assertEquals('.', $sentence[2]->getContent());
    }

    public function testUnmarshall21MissingHref()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<a>QTI-SDK</a>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'href' attribute of the 'a' element is missing.");

        $a = $marshaller->unmarshall($element);
    }

    public function testMarshallQandA21()
    {
        $q = new Q('albert-einstein');

        $a = new A('http://en.wikipedia.org/wiki/Physicist');
        $a->setType('text/html');
        $a->setContent(new InlineCollection([new TextRun('physicist')]));
        $q->setContent(new InlineCollection([new TextRun('Albert Einstein is a '), $a, new TextRun('.')]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($q);
        $element = $marshaller->marshall($q);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<q id="albert-einstein">Albert Einstein is a <a href="http://en.wikipedia.org/wiki/Physicist" type="text/html">physicist</a>.</q>', $dom->saveXML($element));
    }

    public function testUnmarshallQandA21()
    {
        $q = $this->createComponentFromXml('<q id="albert-einstein" cite="http://en.wikipedia.org/wiki/Physicist" xml:base="/home/jerome">Albert Einstein is a <a href="http://en.wikipedia.org/wiki/Physicist" type="text/html">physicist</a>.</q>');
        $this::assertInstanceOf(Q::class, $q);
        $this::assertEquals('http://en.wikipedia.org/wiki/Physicist', $q->getCite());
        $this::assertEquals('/home/jerome', $q->getXmlBase());
    }

    public function testUnmarshall22Ltr()
    {
        $q = $this->createComponentFromXml('
	        <q id="albert-einstein" class="albie yeah" dir="ltr">
	            I am Albert Einstein!
	        </q>
	    ', '2.2.0');

        $this::assertEquals('albie yeah', $q->getClass());
        $this::assertEquals('albert-einstein', $q->getId());
        $this::assertEquals(Direction::LTR, $q->getDir());
    }

    public function testUnmarshall22Rtl()
    {
        $q = $this->createComponentFromXml('
	        <q dir="rtl">
	            I am Albert Einstein!
	        </q>
	    ', '2.2.0');

        $this::assertEquals(Direction::RTL, $q->getDir());
    }

    public function testUnmarshall22DirAuto()
    {
        $q = $this->createComponentFromXml('
	        <q>
	            I am Albert Einstein!
	        </q>
	    ', '2.2.0');

        $this::assertEquals(Direction::AUTO, $q->getDir());
    }

    public function testUnmarshall21DirAuto()
    {
        $q = $this->createComponentFromXml('
	        <q>
	            I am Albert Einstein!
	        </q>
	    ', '2.1.0');

        $this::assertEquals(Direction::AUTO, $q->getDir());
    }

    public function testMarshall22Rtl()
    {
        $q = new Q('albert');
        $q->setDir(Direction::RTL);

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($q);
        $element = $marshaller->marshall($q);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<q id="albert" dir="rtl"/>', $dom->saveXML($element));
    }

    public function testMarshall21Rtl()
    {
        $q = new Q('albert');
        $q->setDir(Direction::RTL);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($q);
        $element = $marshaller->marshall($q);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<q id="albert"/>', $dom->saveXML($element));
    }

    public function testMarshall20Rtl()
    {
        $q = new Q('albert');
        $q->setDir(Direction::RTL);

        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($q);
        $element = $marshaller->marshall($q);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<q id="albert"/>', $dom->saveXML($element));
    }

    public function testMarshall22Ltr()
    {
        $q = new Q('albert');
        $q->setDir(Direction::LTR);

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($q);
        $element = $marshaller->marshall($q);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<q id="albert" dir="ltr"/>', $dom->saveXML($element));
    }

    public function testMarshall22DirAuto()
    {
        $q = new Q('albert');
        $q->setDir(Direction::AUTO);

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($q);
        $element = $marshaller->marshall($q);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<q id="albert"/>', $dom->saveXML($element));
    }

    public function testMarshallBdo22()
    {
        $bdo = new Bdo('bido');
        $bdo->setDir(Direction::RTL);
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($bdo);
        $element = $marshaller->marshall($bdo);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<bdo id="bido" dir="rtl"/>', $dom->saveXML($element));
    }

    public function testUnmarshallBdo22()
    {
        $bdo = $this->createComponentFromXml('<bdo dir="rtl">I am reversed!</bdo>', '2.2.0');
        $this::assertEquals(Direction::RTL, $bdo->getDir());
        $this::assertInstanceOf(Bdo::class, $bdo);

        $content = $bdo->getContent();
        $this::assertSame(1, count($content));
        $this::assertEquals('I am reversed!', $content[0]->getContent());
    }

    /**
     * @throws MarshallerNotFoundException|MarshallingException
     */
    public function testMarshallSpan21()
    {
        $span = new Span('myspan', 'myclass');
        $span->setAriaControls('IDREF1');
        $span->setAriaDescribedBy('IDREF2');
        $span->setAriaFlowTo('IDREF3');
        $span->setAriaLabelledBy('IDREF4');
        $span->setAriaOwns('IDREF5');
        $span->setAriaLevel('5');
        $span->setAriaLive(AriaLive::ASSERTIVE);
        $span->setAriaOrientation(AriaOrientation::VERTICAL);
        $span->setAriaLabel('my aria label');
        $span->setDir(Direction::LTR);

        // aria-* and dir must be ignored in QTI 2.1
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($span);
        $element = $marshaller->marshall($span);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<span id="myspan" class="myclass"/>', $dom->saveXML($element));
    }

    public function testUnmarshallSpan21()
    {
        // In QTI 2.1, aria-* and dir must be ignored.

        /** @var Span $span */
        $span = $this->createComponentFromXml(
            '<span id="myspan" class="myclass" dir="rtl" aria-controls="IDREF1 IDREF2" aria-describedby="IDREF3" aria-flowto="IDREF4" aria-labelledby="IDREF5" aria-owns="IDREF6" aria-level="5" aria-live="off" aria-orientation="horizontal" aria-label="my aria label">I am a span</span>',
            '2.1.0'
        );
        $this::assertInstanceOf(Span::class, $span);
        $this::assertEquals(Direction::AUTO, $span->getDir());
        $this::assertEquals('', $span->getAriaControls());
        $this::assertFalse($span->hasAriaControls());
        $this::assertEquals('', $span->getAriaDescribedBy());
        $this::assertFalse($span->hasAriaDescribedBy());
        $this::assertEquals('', $span->getAriaFlowTo());
        $this::assertFalse($span->hasAriaFlowTo());
        $this::assertEquals('', $span->getAriaLabelledBy());
        $this::assertFalse($span->hasAriaLabelledBy());
        $this::assertEquals('', $span->getAriaOwns());
        $this::assertFalse($span->hasAriaOwns());
        $this::assertEquals('', $span->getAriaLive());
        $this::assertFalse($span->hasAriaLive());
        $this::assertEquals('', $span->getAriaOrientation());
        $this::assertFalse($span->hasAriaOrientation());
        $this::assertEquals('', $span->getAriaLabel());
        $this::assertFalse($span->hasAriaLabel());
        $this::assertEquals('', $span->getAriaLevel());
        $this::assertFalse($span->hasAriaLevel());
        $this::assertFalse($span->hasAriaHidden());
        $this::assertFalse($span->getAriaHidden());

        $content = $span->getContent();
        $this::assertSame(1, count($content));
        $this::assertEquals('I am a span', $content[0]->getContent());
    }

    /**
     * @throws MarshallerNotFoundException|MarshallingException
     */
    public function testMarshallSpan22()
    {
        $span = new Span('myspan', 'myclass');
        $span->setAriaLabel('my aria label');
        $span->setAriaFlowTo('IDREF1');
        $span->setAriaControls('IDREF2');
        $span->setAriaDescribedBy('IDREF3');
        $span->setAriaLabelledBy('IDREF4');
        $span->setAriaLevel('1');
        $span->setAriaLive(AriaLive::OFF);
        $span->setAriaOrientation(AriaOrientation::HORIZONTAL);
        $span->setAriaOwns('IDREF5');
        $span->setDir(Direction::LTR);
        $span->setAriaHidden(true);

        // aria-* and dir must NOT be ignored in QTI 2.2
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($span);
        $element = $marshaller->marshall($span);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals(
            '<span id="myspan" class="myclass" dir="ltr" aria-flowto="IDREF1" aria-controls="IDREF2" aria-describedby="IDREF3" aria-labelledby="IDREF4" aria-owns="IDREF5" aria-level="1" aria-live="off" aria-orientation="horizontal" aria-label="my aria label" aria-hidden="true"/>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshallSpan22()
    {
        /** @var Span $span */
        $span = $this->createComponentFromXml(
            '<span id="myspan" class="myclass" aria-controls="IDREF1 IDREF2" aria-describedby="IDREF3" aria-flowto="IDREF4" aria-labelledby="IDREF5" aria-owns="IDREF6" aria-level="5" aria-live="off" aria-orientation="horizontal" aria-label="my aria label" aria-flowsto="not-considered-here" aria-hidden="true">I am a span</span>',
            '2.2.0'
        );
        $this::assertInstanceOf(Span::class, $span);
        $this::assertEquals(Direction::AUTO, $span->getDir());
        $this::assertEquals('IDREF1 IDREF2', $span->getAriaControls());
        $this::assertTrue($span->hasAriaControls());
        $this::assertEquals('IDREF3', $span->getAriaDescribedBy());
        $this::assertTrue($span->hasAriaDescribedBy());
        $this::assertEquals('IDREF4', $span->getAriaFlowTo());
        $this::assertTrue($span->hasAriaFlowTo());
        $this::assertEquals('IDREF5', $span->getAriaLabelledBy());
        $this::assertTrue($span->hasAriaLabelledBy());
        $this::assertEquals('IDREF6', $span->getAriaOwns());
        $this::assertTrue($span->hasAriaOwns());
        $this::assertEquals(AriaLive::OFF, $span->getAriaLive());
        $this::assertTrue($span->hasAriaLive());
        $this::assertEquals(AriaOrientation::HORIZONTAL, $span->getAriaOrientation());
        $this::assertTrue($span->hasAriaOrientation());
        $this::assertEquals('my aria label', $span->getAriaLabel());
        $this::assertTrue($span->hasAriaLabel());
        $this::assertEquals('5', $span->getAriaLevel());
        $this::assertTrue($span->hasAriaLevel());
        $this::assertTrue($span->hasAriaHidden());
        $this::assertTrue($span->getAriaHidden());

        $content = $span->getContent();
        $this::assertSame(1, count($content));
        $this::assertEquals('I am a span', $content[0]->getContent());
    }
}
