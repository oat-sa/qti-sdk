<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\collections\IdentifierCollection;
use qtism\data\expressions\NumberCorrect;
use qtismtest\QtiSmTestCase;

/**
 * Class NumberCorrectMarshallerTest
 */
class NumberCorrectMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $sectionIdentifier = 'mySection1';
        $includeCategory = 'cat1';
        $excludeCategory = 'cat2 cat3';

        $component = new NumberCorrect();
        $component->setSectionIdentifier($sectionIdentifier);
        $component->setIncludeCategories(new IdentifierCollection(explode("\x20", $includeCategory)));
        $component->setExcludeCategories(new IdentifierCollection(explode("\x20", $excludeCategory)));
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('numberCorrect', $element->nodeName);
        $this::assertEquals($sectionIdentifier, $element->getAttribute('sectionIdentifier'));
        $this::assertEquals($includeCategory, $element->getAttribute('includeCategory'));
        $this::assertEquals($excludeCategory, $element->getAttribute('excludeCategory'));
    }

    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<numberCorrect xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" sectionIdentifier="mySection1" includeCategory="cat1" excludeCategory="cat2 cat3"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(NumberCorrect::class, $component);
        $this::assertEquals('mySection1', $component->getSectionIdentifier());
        $this::assertEquals('cat1', implode("\x20", $component->getIncludeCategories()->getArrayCopy()));
        $this::assertEquals('cat2 cat3', implode("\x20", $component->getExcludeCategories()->getArrayCopy()));
    }
}
