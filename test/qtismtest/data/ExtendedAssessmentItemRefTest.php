<?php

namespace qtismtest\data;

use qtism\data\state\OutcomeDeclaration;
use PHPUnit\Framework\TestCase;
use qtism\common\collections\IdentifierCollection;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\processing\ResponseProcessing;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\ResponseValidityConstraintCollection;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\ResponseValidityConstraint;

class ExtendedAssessmentItemRefTest extends TestCase
{
    public function testConstructor()
    {
        $identifier = 'testIdentifier';
        $href = 'http://example.com';
        $categories = new IdentifierCollection();

        $itemRef = new ExtendedAssessmentItemRef($identifier, $href, $categories);

        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);
        $this->assertEquals($identifier, $itemRef->getIdentifier());
        $this->assertEquals($href, $itemRef->getHref());
        $this->assertInstanceOf(OutcomeDeclarationCollection::class, $itemRef->getOutcomeDeclarations());
        $this->assertInstanceOf(ResponseDeclarationCollection::class, $itemRef->getResponseDeclarations());
        $this->assertInstanceOf(ResponseValidityConstraintCollection::class, $itemRef->getResponseValidityConstraints());
    }

    public function testSetAndGetOutcomeDeclarations()
    {
        $itemRef = new ExtendedAssessmentItemRef('testIdentifier', 'http://example.com');
        $outcomeDeclarations = new OutcomeDeclarationCollection();

        $itemRef->setOutcomeDeclarations($outcomeDeclarations);
        $this->assertSame($outcomeDeclarations, $itemRef->getOutcomeDeclarations());
    }

    public function testSetAndGetResponseProcessing()
    {
        $itemRef = new ExtendedAssessmentItemRef('testIdentifier', 'http://example.com');
        $responseProcessing = new ResponseProcessing();

        $itemRef->setResponseProcessing($responseProcessing);
        $this->assertSame($responseProcessing, $itemRef->getResponseProcessing());
    }

    public function testSetAndGetAdaptive()
    {
        $itemRef = new ExtendedAssessmentItemRef('testIdentifier', 'http://example.com');

        $itemRef->setAdaptive(true);
        $this->assertTrue($itemRef->isAdaptive());

        $itemRef->setAdaptive(false);
        $this->assertFalse($itemRef->isAdaptive());
    }

    public function testSetAndGetTimeDependent()
    {
        $itemRef = new ExtendedAssessmentItemRef('testIdentifier', 'http://example.com');

        $itemRef->setTimeDependent(true);
        $this->assertTrue($itemRef->isTimeDependent());

        $itemRef->setTimeDependent(false);
        $this->assertFalse($itemRef->isTimeDependent());
    }

    public function testAddAndRemoveOutcomeDeclaration()
    {
        $itemRef = new ExtendedAssessmentItemRef('testIdentifier', 'http://example.com');
        $outcomeDeclaration = $this->createMock(OutcomeDeclaration::class);

        $itemRef->addOutcomeDeclaration($outcomeDeclaration);
        $this->assertTrue($itemRef->getOutcomeDeclarations()->contains($outcomeDeclaration));

        $itemRef->removeOutcomeDeclaration($outcomeDeclaration);
        $this->assertFalse($itemRef->getOutcomeDeclarations()->contains($outcomeDeclaration));
    }

    public function testAddAndRemoveResponseDeclaration()
    {
        $itemRef = new ExtendedAssessmentItemRef('testIdentifier', 'http://example.com');
        $responseDeclaration = $this->createMock(ResponseDeclaration::class);

        $itemRef->addResponseDeclaration($responseDeclaration);
        $this->assertTrue($itemRef->getResponseDeclarations()->contains($responseDeclaration));

        $itemRef->removeResponseDeclaration($responseDeclaration);
        $this->assertFalse($itemRef->getResponseDeclarations()->contains($responseDeclaration));
    }

    public function testAddAndRemoveResponseValidityConstraint()
    {
        $itemRef = new ExtendedAssessmentItemRef('testIdentifier', 'http://example.com');
        $responseValidityConstraint = $this->createMock(ResponseValidityConstraint::class);

        $itemRef->addResponseValidityConstraint($responseValidityConstraint);
        $this->assertTrue($itemRef->getResponseValidityConstraints()->contains($responseValidityConstraint));

        $itemRef->removeResponseValidityConstraint($responseValidityConstraint);
        $this->assertFalse($itemRef->getResponseValidityConstraints()->contains($responseValidityConstraint));
    }
}
