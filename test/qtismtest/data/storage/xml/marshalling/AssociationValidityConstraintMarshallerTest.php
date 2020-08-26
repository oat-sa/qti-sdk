<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\state\AssociationValidityConstraint;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class AssociationValidityConstraintMarshallerTest
 */
class AssociationValidityConstraintMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshallSimple()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<associationValidityConstraint identifier="IDENTIFIER" minConstraint="0" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $component = $factory->createMarshaller($element)->unmarshall($element);

        $this->assertInstanceOf(AssociationValidityConstraint::class, $component);
        $this->assertEquals('IDENTIFIER', $component->getIdentifier());
        $this->assertEquals(0, $component->getMinConstraint());
        $this->assertEquals(1, $component->getMaxConstraint());
    }

    public function testUnmarshallNoIdentifier()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<associationValidityConstraint minConstraint="0" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'identifier' attribute is missing from element 'associationValididtyConstraint'.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallNoMinConstraint()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<associationValidityConstraint identifier="IDENTIFIER" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'minConstraint' attribute is missing from element 'associationValididtyConstraint'.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallNoMaxConstraint()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<associationValidityConstraint identifier="IDENTIFIER" minConstraint="0"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'maxConstraint' attribute is missing from element 'associationValididtyConstraint'.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallInvalidMaxConstraintOne()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<associationValidityConstraint identifier="RESPONSE" minConstraint="0" maxConstraint="-2"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("An error occurred while unmarshalling an 'associationValidityConstraint' element. See chained exceptions for more information.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallInvalidMaxConstraintTwo()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<associationValidityConstraint identifier="IDENTIFIER" minConstraint="2" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("An error occurred while unmarshalling an 'associationValidityConstraint' element. See chained exceptions for more information.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testMarshallSimple()
    {
        $component = new AssociationValidityConstraint('IDENTIFIER', 0, 1);
        $factory = new Compact21MarshallerFactory();

        $element = $factory->createMarshaller($component)->marshall($component);
        $this->assertEquals('IDENTIFIER', $element->getAttribute('identifier'));
        $this->assertEquals('0', $element->getAttribute('minConstraint'));
        $this->assertEquals('1', $element->getAttribute('maxConstraint'));
    }
}
