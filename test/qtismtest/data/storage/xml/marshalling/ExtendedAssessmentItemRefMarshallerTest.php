<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\collections\IdentifierCollection;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\expressions\BaseValue;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\processing\TemplateProcessing;
use qtism\data\rules\SetCorrectResponse;
use qtism\data\rules\TemplateRuleCollection;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\ResponseValidityConstraint;
use qtism\data\state\ResponseValidityConstraintCollection;
use qtism\data\state\Shuffling;
use qtism\data\state\ShufflingCollection;
use qtism\data\state\ShufflingGroup;
use qtism\data\state\ShufflingGroupCollection;
use qtism\data\state\TemplateDeclaration;
use qtism\data\state\TemplateDeclarationCollection;
use qtism\data\state\TemplateDefault;
use qtism\data\state\TemplateDefaultCollection;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;

/**
 * Class ExtendedAssessmentItemRefMarshallerTest
 */
class ExtendedAssessmentItemRefMarshallerTest extends QtiSmTestCase
{
    public function testMarshallMinimal(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');
        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals('Q01', $element->getAttribute('identifier'));
        $this::assertEquals('./q01.xml', $element->getAttribute('href'));
        $this::assertEquals('', $element->getAttribute('endAttemptIdentifiers'));
        $this::assertFalse($element->hasAttribute('title'));
        $this::assertFalse($element->hasAttribute('label'));
    }

    public function testMarshallMinimalWithTitle(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');
        $component->setTitle('A title');
        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertTrue($element->hasAttribute('title'));
        $this::assertEquals('A title', $element->getAttribute('title'));
        $this::assertFalse($element->hasAttribute('label'));
    }

    public function testMarshallMinimalWithLabel(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');
        $component->setLabel('A label');
        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertTrue($element->hasAttribute('label'));
        $this::assertEquals('A label', $element->getAttribute('label'));
    }

    public function testUnmarshallMinimal(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" timeDependent="false"/>');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $component);
        $this::assertFalse($component->isTimeDependent());
        $this::assertFalse($component->isAdaptive());
        $this::assertCount(0, $component->getOutcomeDeclarations());
        $this::assertCount(0, $component->getResponseDeclarations());
        $this::assertEquals('Q01', $component->getIdentifier());
        $this::assertEquals('./q01.xml', $component->getHref());
        $this::assertCount(0, $component->getEndAttemptIdentifiers());
        $this::assertFalse($component->hasTitle());
        $this::assertFalse($component->hasLabel());
    }

    public function testUnmarshallMinimalWithTitle(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" title="A title" timeDependent="false"/>');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $component);
        $this::assertTrue($component->hasTitle());
        $this::assertEquals('A title', $component->getTitle());
    }

    public function testUnmarshallMinimalWithLabel(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" label="A label" timeDependent="false"/>');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $component);
        $this::assertTrue($component->hasLabel());
        $this::assertEquals('A label', $component->getLabel());
    }

    /**
     * @depends testMarshallMinimal
     */
    public function testMarshallModerate(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');
        $weights = new WeightCollection(); // some noise
        $weights[] = new Weight('W01', 1.0);
        $weights[] = new Weight('W02', 2.0);

        $responseDeclarations = new ResponseDeclarationCollection();
        $responseDeclarations[] = new ResponseDeclaration('R01', BaseType::INTEGER, Cardinality::SINGLE);
        $responseDeclarations[] = new ResponseDeclaration('R02', BaseType::BOOLEAN, Cardinality::SINGLE);

        $outcomeDeclarations = new OutcomeDeclarationCollection();
        $outcomeDeclarations[] = new OutcomeDeclaration('O01', BaseType::FLOAT, Cardinality::SINGLE);
        $outcomeDeclarations[] = new OutcomeDeclaration('O02', BaseType::FLOAT, Cardinality::SINGLE);

        $templateRules = new TemplateRuleCollection();
        $templateRules[] = new SetCorrectResponse('R01', new BaseValue(BaseType::INTEGER, 20));
        $templateProcessing = new TemplateProcessing($templateRules);

        $templateDefaults = new TemplateDefaultCollection();
        $templateDefaults[] = new TemplateDefault('T01', new BaseValue(BaseType::INTEGER, 20));

        $component->setWeights($weights);
        $component->setResponseDeclarations($responseDeclarations);
        $component->setOutcomeDeclarations($outcomeDeclarations);
        $component->setTemplateProcessing($templateProcessing);
        $component->setTemplateDefaults($templateDefaults);

        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals('Q01', $element->getAttribute('identifier'));
        $this::assertEquals('./q01.xml', $element->getAttribute('href'));

        $weightElts = $element->getElementsByTagName('weight');
        $this::assertEquals(2, $weightElts->length);
        $this::assertEquals('W01', $weightElts->item(0)->getAttribute('identifier'));
        $this::assertEquals('W02', $weightElts->item(1)->getAttribute('identifier'));

        $responseDeclarationElts = $element->getElementsByTagName('responseDeclaration');
        $this::assertEquals(2, $responseDeclarationElts->length);
        $this::assertEquals('R01', $responseDeclarationElts->item(0)->getAttribute('identifier'));
        $this::assertEquals('R02', $responseDeclarationElts->item(1)->getAttribute('identifier'));

        $outcomeDeclarationElts = $element->getElementsByTagName('outcomeDeclaration');
        $this::assertEquals(2, $outcomeDeclarationElts->length);
        $this::assertEquals('O01', $outcomeDeclarationElts->item(0)->getAttribute('identifier'));
        $this::assertEquals('O02', $outcomeDeclarationElts->item(1)->getAttribute('identifier'));

        $templateProcessingElts = $element->getElementsByTagName('templateProcessing');
        $this::assertEquals(1, $templateProcessingElts->length);
        $templateProcessingElt = $templateProcessingElts->item(0);
        $baseValueElements = $templateProcessingElt->getElementsByTagName('baseValue');
        $this::assertEquals(1, $baseValueElements->length);

        $templateDefaultElts = $element->getElementsByTagName('templateDefault');
        $this::assertEquals(1, $templateDefaultElts->length);
    }

    /**
     * @depends testMarshallMinimal
     */
    public function testMarshallTemplateDeclarations(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');

        $templateDeclarations = new TemplateDeclarationCollection();
        $templateDeclarations[] = new TemplateDeclaration('T01', BaseType::INTEGER, Cardinality::SINGLE);
        $templateDeclarations[] = new TemplateDeclaration('T02', BaseType::INTEGER, Cardinality::SINGLE);
        $component->setTemplateDeclarations($templateDeclarations);

        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);

        $templateDeclarationElts = $element->getElementsByTagName('templateDeclaration');
        $this::assertEquals(2, $templateDeclarationElts->length);
        $this::assertEquals('T01', $templateDeclarationElts->item(0)->getAttribute('identifier'));
        $this::assertEquals('T02', $templateDeclarationElts->item(1)->getAttribute('identifier'));
    }

    /**
     * @depends testUnmarshallMinimal
     */
    public function testUnmarshallModerate(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" timeDependent="true" adaptive="true">
				<weight identifier="W01" value="1.0"/>
				<weight identifier="W02" value="2.0"/>
		        <templateDefault templateIdentifier="T01">
		            <baseValue baseType="boolean">true</baseValue>
		        </templateDefault>
				<responseDeclaration identifier="R01" baseType="integer" cardinality="single"/>
				<responseDeclaration identifier="R02" baseType="boolean" cardinality="single"/>
				<outcomeDeclaration identifier="O01" baseType="float" cardinality="single"/>
				<outcomeDeclaration identifier="O02" baseType="float" cardinality="single"/>
		        <templateProcessing>
		            <setCorrectResponse identifier="R01">
                        <baseValue baseType="integer">20</baseValue>
		            </setCorrectResponse>
		        </templateProcessing>
			</assessmentItemRef>
			'
        );
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $component);
        $this::assertEquals('Q01', $component->getIdentifier());
        $this::assertTrue($component->isTimeDependent());
        $this::assertTrue($component->isAdaptive());
        $this::assertEquals('./q01.xml', $component->getHref());

        $weights = $component->getWeights();
        $this::assertEquals('W01', $weights['W01']->getIdentifier());
        $this::assertEquals('W02', $weights['W02']->getIdentifier());

        $responseDeclarations = $component->getResponseDeclarations();
        $this::assertEquals('R01', $responseDeclarations['R01']->getIdentifier());
        $this::assertEquals('R02', $responseDeclarations['R02']->getIdentifier());

        $outcomeDeclarations = $component->getOutcomeDeclarations();
        $this::assertEquals('O01', $outcomeDeclarations['O01']->getIdentifier());
        $this::assertEquals('O02', $outcomeDeclarations['O02']->getIdentifier());

        $templateProcessing = $component->getTemplateProcessing();
        $this::assertNotNull($templateProcessing);

        $templateDefaults = $component->getTemplateDefaults();
        $this::assertCount(1, $templateDefaults);
        $this::assertEquals('T01', $templateDefaults[0]->getTemplateIdentifier());

        $templateDefaultExpression = $templateDefaults[0]->getExpression();
        $this::assertInstanceOf(BaseValue::class, $templateDefaultExpression);
    }

    /**
     * @depends testUnmarshallMinimal
     */
    public function testUnmarshallTemplateDeclarations(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
			<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" timeDependent="true" adaptive="true">
				<templateDeclaration identifier="T01" baseType="integer" cardinality="single"/>
	            <templateDeclaration identifier="T02" baseType="integer" cardinality="single"/>
			</assessmentItemRef>
			');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $templateDeclarations = $component->getTemplateDeclarations();
        $this::assertEquals('T01', $templateDeclarations['T01']->getIdentifier());
        $this::assertEquals('T02', $templateDeclarations['T02']->getIdentifier());
    }

    public function testMarshallMultipleEndAttemptIdentifiers(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');
        $component->setEndAttemptIdentifiers(new IdentifierCollection(['HINT1', 'HINT2', 'HINT3']));
        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals('Q01', $element->getAttribute('identifier'));
        $this::assertEquals('./q01.xml', $element->getAttribute('href'));
        $this::assertEquals('HINT1 HINT2 HINT3', $element->getAttribute('endAttemptIdentifiers'));
    }

    public function testMarshallSingleEndAttemptIdentifiers(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');
        $component->setEndAttemptIdentifiers(new IdentifierCollection(['HINT1']));
        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals('Q01', $element->getAttribute('identifier'));
        $this::assertEquals('./q01.xml', $element->getAttribute('href'));
        $this::assertEquals('HINT1', $element->getAttribute('endAttemptIdentifiers'));
    }

    public function testMarshallNoEndAttemptIdentifiers(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');
        $component->setEndAttemptIdentifiers(new IdentifierCollection([]));
        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals('Q01', $element->getAttribute('identifier'));
        $this::assertEquals('./q01.xml', $element->getAttribute('href'));
        $this::assertEquals('', $element->getAttribute('endAttemptIdentifiers'));
    }

    public function testUnmarshallMultipleEndAttemptIdentifiers(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" timeDependent="true" adaptive="true" endAttemptIdentifiers="HINT1 HINT2 HINT3"/>');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertEquals('Q01', $component->getIdentifier());
        $this::assertEquals('./q01.xml', $component->getHref());
        $this::assertTrue($component->isTimeDependent());
        $this::assertTrue($component->isAdaptive());

        $endAttemptIdentifiers = $component->getEndAttemptIdentifiers();
        $this::assertCount(3, $endAttemptIdentifiers);
        $this::assertEquals('HINT1', $endAttemptIdentifiers[0]);
        $this::assertEquals('HINT2', $endAttemptIdentifiers[1]);
        $this::assertEquals('HINT3', $endAttemptIdentifiers[2]);
    }

    /**
     * @depends testUnmarshallMultipleEndAttemptIdentifiers
     */
    public function testUnmarshallSingleEndAttemptIdentifier(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" timeDependent="true" adaptive="true" endAttemptIdentifiers="HINT1"/>');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $endAttemptIdentifiers = $component->getEndAttemptIdentifiers();
        $this::assertCount(1, $endAttemptIdentifiers);
        $this::assertEquals('HINT1', $endAttemptIdentifiers[0]);
    }

    /**
     * @depends testUnmarshallMultipleEndAttemptIdentifiers
     */
    public function testUnmarshallNoEndAttemptIdentifier(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" timeDependent="true" adaptive="true"/>');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $endAttemptIdentifiers = $component->getEndAttemptIdentifiers();
        $this::assertCount(0, $endAttemptIdentifiers);
    }

    /**
     * @depends testMarshallMinimal
     */
    public function testMarshallShufflingGroups(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');

        $group1 = new ShufflingGroup(new IdentifierCollection(['id1', 'id2', 'id3']));
        $group2 = new ShufflingGroup(new IdentifierCollection(['id4', 'id5', 'id6']));
        $shufflingGroups = new ShufflingGroupCollection([$group1, $group2]);
        $shufflings = new ShufflingCollection([new Shuffling('RESPONSE', $shufflingGroups)]);
        $component->setShufflings($shufflings);

        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals('Q01', $element->getAttribute('identifier'));
        $this::assertEquals('./q01.xml', $element->getAttribute('href'));

        $shufflingElts = $element->getElementsByTagName('shuffling');
        $this::assertEquals(1, $shufflingElts->length);
        $this::assertEquals('RESPONSE', $shufflingElts->item(0)->getAttribute('responseIdentifier'));

        $shufflingGroupElts = $shufflingElts->item(0)->getElementsByTagName('shufflingGroup');
        $this::assertEquals(2, $shufflingGroupElts->length);
        $this::assertEquals('id1 id2 id3', $shufflingGroupElts->item(0)->getAttribute('identifiers'));
        $this::assertEquals('id4 id5 id6', $shufflingGroupElts->item(1)->getAttribute('identifiers'));
    }

    /**
     * @depends testUnmarshallMinimal
     */
    public function testUnmarshallShufflingGroups(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
            <assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" timeDependent="true">
                <shuffling responseIdentifier="RESPONSE">
                    <shufflingGroup identifiers="id1 id2 id3"/>
                    <shufflingGroup identifiers="id4 id5 id6"/>
                </shuffling>
            </assessmentItemRef>
        ');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $shufflings = $component->getShufflings();
        $this::assertCount(1, $shufflings);
        $this::assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this::assertCount(2, $shufflingGroups);
        $this::assertEquals(['id1', 'id2', 'id3'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this::assertEquals(['id4', 'id5', 'id6'], $shufflingGroups[1]->getIdentifiers()->getArrayCopy());
    }

    /**
     * @depends testMarshallMinimal
     */
    public function testMarshallResponseValidityConstraints(): void
    {
        $factory = new Compact21MarshallerFactory();
        $component = new ExtendedAssessmentItemRef('Q01', './q01.xml');

        $responseValidityConstraints = new ResponseValidityConstraintCollection(
            [
                new ResponseValidityConstraint('RESPONSE', 0, 1),
                new ResponseValidityConstraint('RESPONSE2', 1, 2),
            ]
        );

        $component->setResponseValidityConstraints($responseValidityConstraints);

        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals('Q01', $element->getAttribute('identifier'));
        $this::assertEquals('./q01.xml', $element->getAttribute('href'));

        $responseValidityConstraintElts = $element->getElementsByTagName('responseValidityConstraint');
        $this::assertEquals(2, $responseValidityConstraintElts->length);

        $this::assertEquals('RESPONSE', $responseValidityConstraintElts->item(0)->getAttribute('responseIdentifier'));
        $this::assertEquals('0', $responseValidityConstraintElts->item(0)->getAttribute('minConstraint'));
        $this::assertEquals('1', $responseValidityConstraintElts->item(0)->getAttribute('maxConstraint'));

        $this::assertEquals('RESPONSE2', $responseValidityConstraintElts->item(1)->getAttribute('responseIdentifier'));
        $this::assertEquals('1', $responseValidityConstraintElts->item(1)->getAttribute('minConstraint'));
        $this::assertEquals('2', $responseValidityConstraintElts->item(1)->getAttribute('maxConstraint'));
    }

    /**
     * @depends testUnmarshallMinimal
     */
    public function testUnmarshallResponseValidityConstraints(): void
    {
        $factory = new Compact21MarshallerFactory();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
            <assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" href="./q01.xml" timeDependent="true">
                <responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0" maxConstraint="1"/>
                <responseValidityConstraint responseIdentifier="RESPONSE2" minConstraint="1" maxConstraint="2"/>
            </assessmentItemRef>
        ');
        $element = $dom->documentElement;
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $responseValidityConstraints = $component->getResponseValidityConstraints();

        $this::assertCount(2, $responseValidityConstraints);

        $this::assertEquals('RESPONSE', $responseValidityConstraints[0]->getResponseIdentifier());
        $this::assertEquals(0, $responseValidityConstraints[0]->getMinConstraint());
        $this::assertEquals(1, $responseValidityConstraints[0]->getMaxConstraint());

        $this::assertEquals('RESPONSE2', $responseValidityConstraints[1]->getResponseIdentifier());
        $this::assertEquals(1, $responseValidityConstraints[1]->getMinConstraint());
        $this::assertEquals(2, $responseValidityConstraints[1]->getMaxConstraint());
    }
}
