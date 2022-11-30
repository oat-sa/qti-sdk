<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\ExtendedAssessmentItemRefMarshaller;

/**
 * Class CompactMarshallerFactoryTest
 */
class CompactMarshallerFactoryTest extends QtiSmTestCase
{
    public function testInstantiation(): void
    {
        $factory = new Compact21MarshallerFactory();
        $this::assertInstanceOf(Compact21MarshallerFactory::class, $factory);

        $this::assertTrue($factory->hasMappingEntry('assessmentItemRef'));
        $this::assertEquals(ExtendedAssessmentItemRefMarshaller::class, $factory->getMappingEntry('assessmentItemRef'));
    }

    public function testFromDomElement(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml"/>');
        $element = $dom->documentElement;

        $factory = new Compact21MarshallerFactory();
        $marshaller = $factory->createMarshaller($element);
        $this::assertInstanceOf(ExtendedAssessmentItemRefMarshaller::class, $marshaller);
    }

    public function testFromComponent(): void
    {
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');
        $this::assertTrue(true);
    }
}
