<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\InlineCollection;
use qtism\data\content\ssml\Sub as SsmlSub;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\presentation\Sub;
use qtism\data\content\xhtml\text\Em;
use qtism\data\content\xhtml\text\P;
use qtism\data\content\xhtml\text\Span;
use qtismtest\QtiSmTestCase;

/**
 * Class AtomicBlockMarshallerTest
 */
class AtomicBlockMarshallerTest extends QtiSmTestCase
{
    public function testMarshallP()
    {
        $p = new P('my-p');
        $em = new Em();
        $em->setContent(new InlineCollection([new TextRun('simple')]));
        $p->setContent(new InlineCollection([new TextRun('This text is a '), $em, new TextRun(' test.')]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($p);
        $element = $marshaller->marshall($p);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<p id="my-p">This text is a <em>simple</em> test.</p>', $dom->saveXML($element));
    }

    public function testUnmarshallP()
    {
        $p = $this->createComponentFromXml('
	        <p id="my-p">
                This text is
                a <em>simple</em> test.
            </p>
	    ');

        $this::assertInstanceOf(P::class, $p);
        $this::assertEquals('my-p', $p->getId());
        $this::assertEquals(3, count($p->getContent()));

        $content = $p->getContent();
        $this::assertEquals("\n                This text is\n                a ", $content[0]->getContent());
        $em = $content[1];
        $this::assertInstanceOf(Em::class, $em);
        $emContent = $em->getContent();
        $this::assertEquals('simple', $emContent[0]->getContent());
        $this::assertEquals(" test.\n            ", $content[2]->getContent());
    }

    public function testUnmarshallP30()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
            <p>
                <span data-catalog-idref="cat1">Grace</span> walks to and from her <span data-catalog-idref="cat2">harmonica</span> lessons once a week. Her house on Maple Dr. is a 2 kilometre walk to her teacher&apos;s house on Chestnut St.
            </p>
        ');

        $element = $dom->documentElement;
        $marshaller = $this->getMarshallerFactory('3.0.0')->createMarshaller($element);

        $component = $marshaller->unmarshall($element);
        $this::assertInstanceOf(P::class, $component);
        $this::assertCount(5, $component->getContent());

        $this::assertInstanceOf(TextRun::class, $component->getContent()[0]);
        $this::assertEquals('', trim($component->getContent()[0]->getContent()));

        $this::assertInstanceOf(Span::class, $component->getContent()[1]);
        $this::assertEquals('Grace', $component->getContent()[1]->getContent()[0]->getContent());

        $this::assertInstanceOf(TextRun::class, $component->getContent()[2]);
        $this::assertEquals(' walks to and from her ', $component->getContent()[2]->getContent());

        $this::assertInstanceOf(Span::class, $component->getContent()[3]);
        $this::assertEquals('harmonica', $component->getContent()[3]->getContent()[0]->getContent());

        $this::assertInstanceOf(TextRun::class, $component->getContent()[4]);
        $this::assertEquals('lessons once a week. Her house on Maple Dr. is a 2 kilometre walk to her teacher\'s house on Chestnut St.', trim($component->getContent()[4]->getContent()));
    }

    public function testUnmarshallP30SsmlSub()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
            <p>
                <span data-catalog-idref="cat1">Grace</span> walks to and from her <span data-catalog-idref="cat2">harmonica</span> lessons once a week. Her house on Maple <sub xmlns="http://www.w3.org/2010/10/synthesis" alias="Drive">Dr.</sub> is a 2 kilometre walk to her teacher&apos;s house on Chestnut <sub xmlns="http://www.w3.org/2010/10/synthesis" alias="Street">St.</sub><sub>this is sub-script</sub>
            </p>
        ');

        $element = $dom->documentElement;
        $marshaller = $this->getMarshallerFactory('3.0.0')->createMarshaller($element);

        $component = $marshaller->unmarshall($element);
        $this::assertInstanceOf(P::class, $component);
        $this::assertCount(10, $component->getContent());

        $this::assertInstanceOf(TextRun::class, $component->getContent()[0]);
        $this::assertEquals('', trim($component->getContent()[0]->getContent()));

        $this::assertInstanceOf(Span::class, $component->getContent()[1]);
        $this::assertEquals('Grace', $component->getContent()[1]->getContent()[0]->getContent());

        $this::assertInstanceOf(TextRun::class, $component->getContent()[2]);
        $this::assertEquals(' walks to and from her ', $component->getContent()[2]->getContent());

        $this::assertInstanceOf(Span::class, $component->getContent()[3]);
        $this::assertEquals('harmonica', $component->getContent()[3]->getContent()[0]->getContent());

        $this::assertInstanceOf(TextRun::class, $component->getContent()[4]);
        $this::assertEquals(' lessons once a week. Her house on Maple ', $component->getContent()[4]->getContent());

        $this::assertInstanceOf(SsmlSub::class, $component->getContent()[5]);
        $this::assertEquals('<sub xmlns="http://www.w3.org/2010/10/synthesis" alias="Drive">Dr.</sub>', $component->getContent()[5]->getXmlString());

        $this::assertInstanceOf(TextRun::class, $component->getContent()[6]);
        $this::assertEquals(' is a 2 kilometre walk to her teacher\'s house on Chestnut ', $component->getContent()[6]->getContent());

        $this::assertInstanceOf(SsmlSub::class, $component->getContent()[7]);
        $this::assertEquals('<sub xmlns="http://www.w3.org/2010/10/synthesis" alias="Street">St.</sub>', $component->getContent()[7]->getXmlString());

        $this::assertInstanceOf(Sub::class, $component->getContent()[8]);
        $this::assertEquals('this is sub-script', $component->getContent()[8]->getContent()[0]->getContent());

        $this::assertInstanceOf(TextRun::class, $component->getContent()[9]);
        $this::assertEquals('', trim($component->getContent()[9]->getContent()));
    }

    public function testMarshallP30SsmlSub()
    {
        $span1 = new Span();
        $span1->setContent(new InlineCollection([new TextRun('Grace')]));
        $textRun1 = new TextRun(' walks to and from her ');
        $span2 = new Span();
        $span2->setContent(new InlineCollection([new TextRun('harmonica')]));
        $textRun2 = new TextRun(' lessons once a week. Her house on Maple ');
        $ssmlSub1 = new SsmlSub('<sub xmlns="http://www.w3.org/2010/10/synthesis" alias="Drive">Dr.</sub>');
        $textRun3 = new TextRun(' is a 2 kilometre walk to her teacher\'s house on Chestnut ');
        $ssmlSub2 = new SsmlSub('<sub xmlns="http://www.w3.org/2010/10/synthesis" alias="Street">St.</sub>');
        $sub = new Sub();
        $sub->setContent(new InlineCollection([new TextRun('this is sub-script')]));

        $p = new P();
        $p->setContent(new InlineCollection([
            $span1,
            $textRun1,
            $span2,
            $textRun2,
            $ssmlSub1,
            $textRun3,
            $ssmlSub2,
            $sub,
        ]));

        $marshaller = $this->getMarshallerFactory('3.0.0')->createMarshaller($p);
        $element = $marshaller->marshall($p);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $element = $doc->importNode($element, true);
        $this::assertEquals(
            '<p><span>Grace</span> walks to and from her <span>harmonica</span> lessons once a week. Her house on Maple <sub xmlns="http://www.w3.org/2010/10/synthesis" alias="Drive">Dr.</sub> is a 2 kilometre walk to her teacher\'s house on Chestnut <sub xmlns="http://www.w3.org/2010/10/synthesis" alias="Street">St.</sub><sub>this is sub-script</sub></p>',
            $doc->saveXML($element)
        );
    }
}
