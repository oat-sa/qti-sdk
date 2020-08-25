<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\lists\Dd;
use qtism\data\content\xhtml\lists\Dl;
use qtism\data\content\xhtml\lists\DlElementCollection;
use qtism\data\content\xhtml\lists\Dt;
use qtismtest\QtiSmTestCase;

/**
 * Class DlMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class DlMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        $dl = $this->createComponentFromXml('
            <dl id="my-description-list">
               <dt>Cola</dt>
               <dd class="soda">Black sparkling soda.</dd>
               <dt class="beverage">Tea</dt>
               <dd>Hot water with something.</dd>
            </dl>
        ');

        $this->assertInstanceOf(Dl::class, $dl);
        $this->assertEquals('my-description-list', $dl->getId());
        $dlContent = $dl->getContent();
        $this->assertEquals(4, count($dlContent));

        $dt1 = $dlContent[0];
        $this->assertInstanceOf(Dt::class, $dt1);
        $dt1Content = $dt1->getContent();
        $this->assertEquals(1, count($dt1Content));
        $this->assertEquals('Cola', $dt1Content[0]->getContent());

        $dd1 = $dlContent[1];
        $this->assertInstanceOf(Dd::class, $dd1);
        $this->assertEquals('soda', $dd1->getClass());
        $dd1Content = $dd1->getContent();
        $this->assertEquals(1, count($dd1Content));
        $this->assertEquals('Black sparkling soda.', $dd1Content[0]->getContent());

        $dt2 = $dlContent[2];
        $this->assertInstanceOf(Dt::class, $dt2);
        $this->assertEquals('beverage', $dt2->getClass());
        $dt2Content = $dt2->getContent();
        $this->assertEquals(1, count($dt2Content));
        $this->assertEquals('Tea', $dt2Content[0]->getContent());

        $dd2 = $dlContent[3];
        $this->assertInstanceOf(Dd::class, $dd2);
        $dd2Content = $dd2->getContent();
        $this->assertEquals(1, count($dd2Content));
        $this->assertEquals('Hot water with something.', $dd2Content[0]->getContent());
    }

    public function testMarshall()
    {
        $dt1 = new Dt();
        $dt1->setContent(new InlineCollection([new TextRun('Cola')]));

        $dd1 = new Dd('', 'soda');
        $dd1->setContent(new FlowCollection([new TextRun('Black sparkling soda.')]));

        $dt2 = new Dt();
        $dt2->setClass('beverage');
        $dt2->setContent(new InlineCollection([new TextRun('Tea')]));

        $dd2 = new Dd();
        $dd2->setContent(new FlowCollection([new TextRun('Hot water with something')]));

        $dl = new Dl('my-description-list');
        $dl->setContent(new DlElementCollection([$dt1, $dd1, $dt2, $dd2]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($dl)->marshall($dl);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals('<dl id="my-description-list"><dt>Cola</dt><dd class="soda">Black sparkling soda.</dd><dt class="beverage">Tea</dt><dd>Hot water with something</dd></dl>', $dom->saveXML($element));
    }
}
