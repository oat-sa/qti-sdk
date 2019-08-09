<?php

namespace qtismtest\data\storage\xml;

use qtism\data\results\AssessmentResult;
use qtism\data\results\Context;
use qtism\data\results\SessionIdentifier;
use qtism\data\results\SessionIdentifierCollection;
use qtism\data\storage\xml\XmlResultDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;

class XmlResultDocumentTest extends QtiSmTestCase
{
    public function testLoad()
    {
        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result.xml', true);

        $this->assertEquals('2.1.0', $xmlDoc->getVersion());

        /** @var AssessmentResult $assessmentResult */
        $assessmentResult = $xmlDoc->getDocumentComponent();
        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);

        $context = $assessmentResult->getContext();
        $this->assertInstanceOf(Context::class, $context);

        $sessionIdentifiers = $context->getSessionIdentifiers();
        $this->assertInstanceOf(SessionIdentifierCollection::class, $sessionIdentifiers);

        /** @var SessionIdentifier $sessionIdentifier1 */
        $sessionIdentifier1 = $sessionIdentifiers[0];
        $this->assertEquals('sessionIdentifier1-id', $sessionIdentifier1->getIdentifier());
        $this->assertEquals('http://sessionIdentifier1-sourceID', $sessionIdentifier1->getSourceID());

        /** @var SessionIdentifier $sessionIdentifier2 */
        $sessionIdentifier2 = $sessionIdentifiers[1];
        $this->assertEquals('sessionIdentifier2-id', $sessionIdentifier2->getIdentifier());
        $this->assertEquals('http://sessionIdentifier2-sourceID', $sessionIdentifier2->getSourceID());

        $testResult = $assessmentResult->getTestResult();
        $this->assertEquals('fixture-test-identifier', $testResult->getIdentifier());
        $this->assertInstanceOf(\DateTime::class, $testResult->getDatestamp());

        $this->assertCount(2, $testResult->getItemVariables());
    }

    public function testLoadMissingData()
    {
        $this->setExpectedException(
            XmlStorageException::class,
            'The document could not be validated with XML Schema'
        );

        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result-missing-data.xml', true);
    }
}