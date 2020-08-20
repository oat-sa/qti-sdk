<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Bogaerts Jérôme <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\data\storage\xml;

use DateTime;
use DOMDocument;
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
        $this->assertInstanceOf(DateTime::class, $testResult->getDatestamp());

        $this->assertCount(2, $testResult->getItemVariables());
    }

    public function testLoadMissingData()
    {
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessageRegExp('/^The document could not be validated with XML Schema/');

        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result-missing-data.xml', true);
    }

    public function testSaveToString()
    {
        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result.xml', true);

        $str = new DOMDocument();
        $str->loadXML($xmlDoc->saveToString(false));

        $expected = '<assessmentResult xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_result_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_result_v2p1.xsd"><context sourcedId="fixture-sourcedId"><sessionIdentifier sourceID="http://sessionIdentifier1-sourceID" identifier="sessionIdentifier1-id"/><sessionIdentifier sourceID="http://sessionIdentifier2-sourceID" identifier="sessionIdentifier2-id"/></context><testResult identifier="fixture-test-identifier" datestamp="2018-06-27T09:41:45.529"><responseVariable identifier="response-identifier" cardinality="single"><correctResponse><value>fixture-test-value1</value><value>fixture-test-value2</value></correctResponse><candidateResponse><value fieldIdentifier="test-id-1">fixture-test-value1</value><value fieldIdentifier="test-id-2">fixture-test-value2</value><value fieldIdentifier="test-id-3">fixture-test-value3</value></candidateResponse></responseVariable><templateVariable identifier="response-identifier" cardinality="single"><value>test1</value><value>test2</value></templateVariable></testResult><itemResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529" sessionStatus="final" sequenceIndex="2"><candidateComment>comment-fixture</candidateComment><responseVariable identifier="fixture-identifier" cardinality="single" baseType="string" choiceSequence="value-id-1"><correctResponse><value>fixture-value1</value><value>fixture-value2</value></correctResponse><candidateResponse><value fieldIdentifier="value-id-1">fixture-value1</value><value fieldIdentifier="value-id-2">fixture-value2</value><value fieldIdentifier="value-id-3">fixture-value3</value></candidateResponse></responseVariable><outcomeVariable identifier="fixture-identifier" cardinality="single" baseType="string" view="candidate" interpretation="fixture-interpretation" longInterpretation="http://fixture-interpretation" normalMinimum="2" normalMaximum="3" masteryValue="4"><value>fixture-value1</value><value>fixture-value2</value><value>fixture-value3</value></outcomeVariable><templateVariable identifier="fixture-identifier" cardinality="single" baseType="string"><value>fixture-value1</value><value>fixture-value2</value><value>fixture-value3</value></templateVariable></itemResult><itemResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45+0000" sessionStatus="final" sequenceIndex="2"><responseVariable identifier="fixture-identifier" cardinality="single" baseType="string" choiceSequence="value-id-1"><correctResponse><value>fixture-value1</value><value>fixture-value2</value></correctResponse><candidateResponse><value fieldIdentifier="value-id-1">fixture-value1</value><value fieldIdentifier="value-id-2">fixture-value2</value><value fieldIdentifier="value-id-3">fixture-value3</value></candidateResponse></responseVariable><outcomeVariable identifier="fixture-identifier" cardinality="single" baseType="string" view="candidate" interpretation="fixture-interpretation" longInterpretation="http://fixture-interpretation" normalMinimum="2" normalMaximum="3" masteryValue="4"><value>fixture-value1</value><value>fixture-value2</value><value>fixture-value3</value></outcomeVariable></itemResult></assessmentResult>';
        $expectedDom = new DOMDocument();
        $expectedDom->loadXML($expected);

        $this->assertEqualXMLStructure($expectedDom->firstChild, $str->firstChild);
    }

    /**
     * @dataProvider inferVersionAndSchemaValidateProvider
     * @param string $testFile
     * @param string $expectedVersion
     * @throws XmlStorageException
     */
    public function testInferVersionAndSchemaValidate(string $testFile, string $expectedVersion)
    {
        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load($testFile, true);

        $this->assertEquals($expectedVersion, $xmlDoc->getVersion());
    }

    public function inferVersionAndSchemaValidateProvider(): array
    {
        return [
            [self::samplesDir() . 'results/simple-assessment-result.xml', '2.1.0'],
            [self::samplesDir() . 'results/simple-assessment-result-v2p2.xml', '2.2.0'],
        ];
    }

    public function testInferVersionWithMissingNamespaceThrowsException()
    {
        $xmlDoc = new XmlResultDocument();

        $this->expectException(XmlStorageException::class);

        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result-missing-namespace.xml');
    }
}
