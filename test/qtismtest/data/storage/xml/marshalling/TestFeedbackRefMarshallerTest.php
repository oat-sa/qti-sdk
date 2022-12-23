<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\ShowHide;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtism\data\TestFeedbackAccess;
use qtism\data\TestFeedbackRef;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class TestFeedbackRefMarshallerTest
 */
class TestFeedbackRefMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<testFeedbackRef identifier="showme" outcomeIdentifier="SHOW_FEEDBACK" access="during" showHide="show" href="./TF01.xml"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $ref = $factory->createMarshaller($element)->unmarshall($element);

        $this::assertEquals('showme', $ref->getIdentifier());
        $this::assertEquals('SHOW_FEEDBACK', $ref->getOutcomeIdentifier());
        $this::assertEquals(TestFeedbackAccess::DURING, $ref->getAccess());
        $this::assertEquals(ShowHide::SHOW, $ref->getShowHide());
        $this::assertEquals('./TF01.xml', $ref->getHref());
    }

    public function testMarshall(): void
    {
        $ref = new TestFeedbackRef('showme', 'SHOW_FEEDBACK', TestFeedbackAccess::DURING, ShowHide::SHOW, './TF01.xml');
        $factory = new Compact21MarshallerFactory();
        $marshaller = $factory->createMarshaller($ref);
        $elt = $marshaller->marshall($ref);

        $this::assertEquals('showme', $elt->getAttribute('identifier'));
        $this::assertEquals('SHOW_FEEDBACK', $elt->getAttribute('outcomeIdentifier'));
        $this::assertEquals('during', $elt->getAttribute('access'));
        $this::assertEquals('show', $elt->getAttribute('showHide'));
        $this::assertEquals('./TF01.xml', $elt->getAttribute('href'));
    }

    public function testUnmarshallMissingIdentifier(): void
    {
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'identifier' attribute is missing from element 'testFeedbackRef'");

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<testFeedbackRef outcomeIdentifier="SHOW_FEEDBACK" access="during" showHide="show" href="./TF01.xml"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $ref = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingOutcomeIdentifier(): void
    {
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'outcomeIdentifier' attribute is missing from element 'testFeedbackRef'");

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<testFeedbackRef identifier="showme" access="during" showHide="show" href="./TF01.xml"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $ref = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingAccess(): void
    {
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'access' attribute is missing from element 'testFeedbackRef'");

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<testFeedbackRef identifier="showme" outcomeIdentifier="SHOW_FEEDBACK" showHide="show" href="./TF01.xml"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $ref = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingShowHide(): void
    {
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'showHide' attribute is missing from element 'testFeedbackRef'");

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<testFeedbackRef identifier="showme" outcomeIdentifier="SHOW_FEEDBACK" access="during" href="./TF01.xml"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $ref = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingHref(): void
    {
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'href' attribute is missing from element 'testFeedbackRef'");

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<testFeedbackRef identifier="showme" outcomeIdentifier="SHOW_FEEDBACK" access="during" showHide="show"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $ref = $factory->createMarshaller($element)->unmarshall($element);
    }
}
