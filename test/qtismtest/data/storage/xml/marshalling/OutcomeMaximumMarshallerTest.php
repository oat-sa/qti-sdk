<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\collections\IdentifierCollection;
use qtism\data\expressions\OutcomeMaximum;
use qtismtest\QtiSmTestCase;

/**
 * Class OutcomeMaximumMarshallerTest
 */
class OutcomeMaximumMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $sectionIdentifier = 'mySection1';
        $outcomeIdentifier = 'myOutcome1';
        $includeCategory = 'cat1';
        $excludeCategory = 'cat2 cat3';
        $weightIdentifier = 'myWeight1';

        $component = new OutcomeMaximum($outcomeIdentifier, $weightIdentifier);
        $component->setSectionIdentifier($sectionIdentifier);
        $component->setIncludeCategories(new IdentifierCollection(explode("\x20", $includeCategory)));
        $component->setExcludeCategories(new IdentifierCollection(explode("\x20", $excludeCategory)));
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('outcomeMaximum', $element->nodeName);
        $this::assertEquals($sectionIdentifier, $element->getAttribute('sectionIdentifier'));
        $this::assertEquals($outcomeIdentifier, $element->getAttribute('outcomeIdentifier'));
        $this::assertEquals($weightIdentifier, $element->getAttribute('weightIdentifier'));
        $this::assertEquals($includeCategory, $element->getAttribute('includeCategory'));
        $this::assertEquals($excludeCategory, $element->getAttribute('excludeCategory'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<outcomeMaximum xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" sectionIdentifier="mySection1" outcomeIdentifier="myOutcome1" includeCategory="cat1" excludeCategory="cat2 cat3" weightIdentifier="myWeight1"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeMaximum::class, $component);
        $this::assertEquals('mySection1', $component->getSectionIdentifier());
        $this::assertEquals('myOutcome1', $component->getOutcomeIdentifier());
        $this::assertEquals('myWeight1', $component->getWeightIdentifier());
        $this::assertEquals('cat1', implode("\x20", $component->getIncludeCategories()->getArrayCopy()));
        $this::assertEquals('cat2 cat3', implode("\x20", $component->getExcludeCategories()->getArrayCopy()));
    }
}
