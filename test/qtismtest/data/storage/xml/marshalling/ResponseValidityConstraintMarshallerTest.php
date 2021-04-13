<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\state\AssociationValidityConstraint;
use qtism\data\state\AssociationValidityConstraintCollection;
use qtism\data\state\ResponseValidityConstraint;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class ResponseValidityConstraintMarshallerTest
 */
class ResponseValidityConstraintMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshallSimple()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $component = $factory->createMarshaller($element)->unmarshall($element);

        $this::assertInstanceOf(ResponseValidityConstraint::class, $component);
        $this::assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this::assertEquals(0, $component->getMinConstraint());
        $this::assertEquals(1, $component->getMaxConstraint());
        $this::assertEquals('', $component->getPatternMask());
    }

    /**
     * @depends testUnmarshallSimple
     */
    public function testUnmarshallWithAssociationConstraints()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
            <responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0" maxConstraint="1">
                <associationValidityConstraint identifier="ID1" minConstraint="0" maxConstraint="1"/>
                <associationValidityConstraint identifier="ID2" minConstraint="1" maxConstraint="2"/>
            </responseValidityConstraint>
            ');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $component = $factory->createMarshaller($element)->unmarshall($element);

        $associationValidityConstraints = $component->getAssociationValidityConstraints();
        $this::assertCount(2, $associationValidityConstraints);

        $this::assertEquals('ID1', $associationValidityConstraints[0]->getIdentifier());
        $this::assertEquals(0, $associationValidityConstraints[0]->getMinConstraint());
        $this::assertEquals(1, $associationValidityConstraints[0]->getMaxConstraint());

        $this::assertEquals('ID2', $associationValidityConstraints[1]->getIdentifier());
        $this::assertEquals(1, $associationValidityConstraints[1]->getMinConstraint());
        $this::assertEquals(2, $associationValidityConstraints[1]->getMaxConstraint());
    }

    /**
     * @depends testUnmarshallSimple
     */
    public function testUnmarshallWithPatternMask()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0" maxConstraint="1" patternMask="/.+/ui"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $component = $factory->createMarshaller($element)->unmarshall($element);

        $this::assertEquals('/.+/ui', $component->getPatternMask());
    }

    /**
     * @depends testUnmarshallSimple
     */
    public function testUnmarshallNoResponseIdentifier()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint minConstraint="0" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from element 'responseValididtyConstraint'.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshallSimple
     */
    public function testUnmarshallNoMinConstraint()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'minConstraint' attribute is missing from element 'responseValididtyConstraint'.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshallSimple
     */
    public function testUnmarshallNoMaxConstraint()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'maxConstraint' attribute is missing from element 'responseValididtyConstraint'.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshallSimple
     */
    public function testUnmarshallInvalidMaxConstraintOne()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0" maxConstraint="-2"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("An error occurred while unmarshalling a 'responseValidityConstraint'. See chained exceptions for more information.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    /**
     * @depends testUnmarshallSimple
     */
    public function testUnmarshallInvalidMaxConstraintTwo()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="2" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("An error occurred while unmarshalling a 'responseValidityConstraint'. See chained exceptions for more information.");
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }

    public function testMarshallSimple()
    {
        $component = new ResponseValidityConstraint('RESPONSE', 0, 1, '/.+/ui');
        $factory = new Compact21MarshallerFactory();

        $element = $factory->createMarshaller($component)->marshall($component);
        $this::assertEquals('RESPONSE', $element->getAttribute('responseIdentifier'));
        $this::assertEquals('0', $element->getAttribute('minConstraint'));
        $this::assertEquals('1', $element->getAttribute('maxConstraint'));
        $this::assertEquals('/.+/ui', $element->getAttribute('patternMask'));
    }

    /**
     * @depends testMarshallSimple
     */
    public function testMarshallWithAssociationConstraints()
    {
        $component = new ResponseValidityConstraint('RESPONSE', 0, 1);
        $component->setAssociationValidityConstraints(
            new AssociationValidityConstraintCollection(
                [
                    new AssociationValidityConstraint('ID1', 0, 1),
                    new AssociationValidityConstraint('ID2', 0, 2),
                ]
            )
        );
        $factory = new Compact21MarshallerFactory();

        $element = $factory->createMarshaller($component)->marshall($component);
        $associationValidityConstraintElts = $element->getElementsByTagName('associationValidityConstraint');

        $this::assertEquals(2, $associationValidityConstraintElts->length);
        $this::assertEquals('ID1', $associationValidityConstraintElts->item(0)->getAttribute('identifier'));
        $this::assertEquals('0', $associationValidityConstraintElts->item(0)->getAttribute('minConstraint'));
        $this::assertEquals('1', $associationValidityConstraintElts->item(0)->getAttribute('maxConstraint'));
        $this::assertEquals('ID2', $associationValidityConstraintElts->item(1)->getAttribute('identifier'));
        $this::assertEquals('0', $associationValidityConstraintElts->item(1)->getAttribute('minConstraint'));
        $this::assertEquals('2', $associationValidityConstraintElts->item(1)->getAttribute('maxConstraint'));
    }
}
