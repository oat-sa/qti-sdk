<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
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
        $this::assertCount(3, $p->getContent());

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
}
