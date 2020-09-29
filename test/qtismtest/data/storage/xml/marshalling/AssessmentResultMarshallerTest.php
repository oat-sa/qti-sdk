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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\data\storage\xml\marshalling;

use DOMElement;
use oat\dtms\DateTime;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiUri;
use qtism\data\results\AssessmentResult;
use qtism\data\results\CandidateResponse;
use qtism\data\results\Context;
use qtism\data\results\ItemResult;
use qtism\data\results\ItemResultCollection;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\ResultOutcomeVariable;
use qtism\data\results\ResultResponseVariable;
use qtism\data\results\ResultTemplateVariable;
use qtism\data\results\SessionIdentifier;
use qtism\data\results\SessionIdentifierCollection;
use qtism\data\results\TestResult;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class AssessmentResultMarshallerTest
 */
class AssessmentResultMarshallerTest extends QtiSmTestCase
{
    public function testValidMinimalXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <assessmentResult xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p2">
                <context />
            </assessmentResult>
        ';

        /** @var AssessmentResult $assessmentResult */
        $assessmentResult = $this->createComponentFromXml($xml);
        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);

        $context = $assessmentResult->getContext();
        $this->assertFalse($context->hasSourcedId());
        $this->assertFalse($context->hasSessionIdentifiers());

        $this->assertFalse($assessmentResult->hasTestResult());
        $this->assertEquals(null, $assessmentResult->getTestResult());
        $this->assertFalse($assessmentResult->hasItemResults());
        $this->assertEquals(null, $assessmentResult->getItemResults());
    }

    public function testUnmarshall()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <assessmentResult xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p2">
                <context sourcedId="fixture-sourcedId">
                    <sessionIdentifier sourceID="http://sessionIdentifier1-sourceID" identifier="sessionIdentifier1-id"/>
                    <sessionIdentifier sourceID="http://sessionIdentifier2-sourceID" identifier="sessionIdentifier2-id"/>
                </context>
                <testResult identifier="fixture-test-identifier" datestamp="2018-06-27T09:41:45.529">
                    <responseVariable identifier="response-identifier" cardinality="single">
                        <correctResponse>
                            <value>fixture-test-value1</value>
                            <value>fixture-test-value2</value>
                        </correctResponse>
                        <candidateResponse>
                            <value fieldIdentifier="test-id-1">fixture-test-value1</value>
                            <value fieldIdentifier="test-id-2">fixture-test-value2</value>
                            <value fieldIdentifier="test-id-3">fixture-test-value3</value>
                        </candidateResponse>
                    </responseVariable>
                    <templateVariable identifier="response-identifier" cardinality="single">
                        <value>test1</value>
                        <value>test2</value>
                    </templateVariable>
                </testResult>
                <itemResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529" sessionStatus="final" sequenceIndex="2">
                    <responseVariable cardinality="single" identifier="fixture-identifier" baseType="string" choiceSequence="value-id-1">
                        <correctResponse>
                            <value>fixture-value1</value>
                            <value>fixture-value2</value>
                        </correctResponse>
                        <candidateResponse>
                            <value fieldIdentifier="value-id-1">fixture-value1</value>
                            <value fieldIdentifier="value-id-2">fixture-value2</value>
                            <value fieldIdentifier="value-id-3">fixture-value3</value>
                        </candidateResponse>
                    </responseVariable>
                    <templateVariable cardinality="single" identifier="fixture-identifier" baseType="string">
                        <value>fixture-value1</value>
                        <value>fixture-value2</value>
                        <value>fixture-value3</value>
                    </templateVariable>
                    <outcomeVariable 
                        cardinality="single" 
                        identifier="fixture-identifier" 
                        baseType="string" 
                        view="candidate"
                        interpretation="fixture-interpretation"
                        longInterpretation="http://fixture-interpretation"
                        normalMinimum="2"
                        normalMaximum="3"
                        masteryValue="4"
                        >
                        <value>fixture-value1</value>
                        <value>fixture-value2</value>
                        <value>fixture-value3</value>
                    </outcomeVariable>
                    <candidateComment>comment-fixture</candidateComment>
                </itemResult>
                <itemResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529" sessionStatus="final" sequenceIndex="2">
                    <responseVariable cardinality="single" identifier="fixture-identifier" baseType="string" choiceSequence="value-id-1">
                        <correctResponse>
                            <value>fixture-value1</value>
                            <value>fixture-value2</value>
                        </correctResponse>
                        <candidateResponse>
                            <value fieldIdentifier="value-id-1">fixture-value1</value>
                            <value fieldIdentifier="value-id-2">fixture-value2</value>
                            <value fieldIdentifier="value-id-3">fixture-value3</value>
                        </candidateResponse>
                    </responseVariable>
                    <outcomeVariable 
                        cardinality="single" 
                        identifier="fixture-identifier" 
                        baseType="string" 
                        view="candidate"
                        interpretation="fixture-interpretation"
                        longInterpretation="http://fixture-interpretation"
                        normalMinimum="2"
                        normalMaximum="3"
                        masteryValue="4"
                        >
                        <value>fixture-value1</value>
                        <value>fixture-value2</value>
                        <value>fixture-value3</value>
                    </outcomeVariable>
                </itemResult>
            </assessmentResult>
        ';

        /** @var AssessmentResult $assessmentResult */
        $assessmentResult = $this->createComponentFromXml($xml);
        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);

        $context = $assessmentResult->getContext();
        $this->assertEquals('fixture-sourcedId', $context->getSourcedId());
        $this->assertEquals(2, $context->getSessionIdentifiers()->count());

        $this->assertTrue($assessmentResult->hasTestResult());
        $this->assertEquals(2, $assessmentResult->getTestResult()->getItemVariables()->count());

        $this->assertTrue($assessmentResult->hasItemResults());
        $this->assertEquals(2, $assessmentResult->getItemResults()->count());
        $this->assertEquals(3, $assessmentResult->getItemResults()[0]->getItemVariables()->count());
        $this->assertTrue($assessmentResult->getItemResults()[0]->hasCandidateComment());
        $this->assertEquals(2, $assessmentResult->getItemResults()[1]->getItemVariables()->count());
        $this->assertFalse($assessmentResult->getItemResults()[1]->hasCandidateComment());

        $assessmentResult = $this->createComponentFromXml($xml);
        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);
    }

    public function testUnmarshallWithoutTestResult()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <assessmentResult xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p2">
                <context />
                <itemResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529" sessionStatus="final" sequenceIndex="2">
                    <responseVariable cardinality="single" identifier="fixture-identifier" baseType="string" choiceSequence="value-id-1">
                        <correctResponse>
                            <value>fixture-value1</value>
                            <value>fixture-value2</value>
                        </correctResponse>
                        <candidateResponse>
                            <value fieldIdentifier="value-id-1">fixture-value1</value>
                            <value fieldIdentifier="value-id-2">fixture-value2</value>
                            <value fieldIdentifier="value-id-3">fixture-value3</value>
                        </candidateResponse>
                    </responseVariable>
                </itemResult>
                <itemResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529" sessionStatus="final" sequenceIndex="2">
                    <responseVariable cardinality="single" identifier="fixture-identifier" baseType="string" choiceSequence="value-id-1">
                        <correctResponse>
                            <value>fixture-value1</value>
                            <value>fixture-value2</value>
                        </correctResponse>
                        <candidateResponse>
                            <value fieldIdentifier="value-id-1">fixture-value1</value>
                            <value fieldIdentifier="value-id-2">fixture-value2</value>
                            <value fieldIdentifier="value-id-3">fixture-value3</value>
                        </candidateResponse>
                    </responseVariable>
                </itemResult>
            </assessmentResult>
        ';

        /** @var AssessmentResult $assessmentResult */
        $assessmentResult = $this->createComponentFromXml($xml);
        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);

        $this->assertFalse($assessmentResult->hasTestResult());

        $this->assertTrue($assessmentResult->hasItemResults());
        $this->assertEquals(2, $assessmentResult->getItemResults()->count());

        $assessmentResult = $this->createComponentFromXml($xml);
        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);
    }

    public function testMarshall()
    {
        $component = new AssessmentResult(
            new Context(
                new QtiIdentifier('fixture-sourcedId'),
                new SessionIdentifierCollection([
                    new SessionIdentifier(
                        new QtiUri('http://sessionIdentifier1-sourceID'),
                        new QtiIdentifier('sessionIdentifier1-id')
                    ),
                    new SessionIdentifier(
                        new QtiUri('http://sessionIdentifier2-sourceID'),
                        new QtiIdentifier('sessionIdentifier2-id')
                    ),
                ])
            ),
            new TestResult(
                new QtiIdentifier('fixture-identifier'),
                new DateTime('2018-06-27T09:41:45.529'),
                new ItemVariableCollection([
                    new ResultResponseVariable(
                        new QtiIdentifier('response-identifier'),
                        0,
                        new CandidateResponse(new ValueCollection([
                            new Value('fixture-test-value1'),
                        ]))
                    ),
                    new ResultTemplateVariable(
                        new QtiIdentifier('fixture-identifier'),
                        0,
                        4,
                        new ValueCollection([
                            new Value('fixture-test-value1'),
                            new Value('fixture-test-value2'),
                        ])
                    ),
                ])
            ),
            new ItemResultCollection([
                new ItemResult(
                    new QtiIdentifier('fixture-identifier'),
                    new DateTime('2018-06-27T09:41:45.529'),
                    1,
                    new ItemVariableCollection([
                        new ResultResponseVariable(
                            new QtiIdentifier('response-identifier'),
                            0,
                            new CandidateResponse(new ValueCollection([
                                new Value('fixture-test-value1'),
                            ]))
                        ),
                        new ResultTemplateVariable(
                            new QtiIdentifier('response-identifier'),
                            0,
                            4,
                            new ValueCollection([
                                new Value('fixture-test-value1'),
                                new Value('fixture-test-value2'),
                            ])
                        ),
                        new ResultOutcomeVariable(
                            new QtiIdentifier('response-identifier'),
                            0,
                            4,
                            new ValueCollection([
                                new Value('fixture-test-value1'),
                                new Value('fixture-test-value2'),
                            ])
                        ),
                    ])
                ),
                new ItemResult(
                    new QtiIdentifier('fixture-identifier'),
                    new DateTime('2018-06-27T09:41:45.529'),
                    1,
                    new ItemVariableCollection([
                        new ResultResponseVariable(
                            new QtiIdentifier('response-identifier'),
                            0,
                            new CandidateResponse(new ValueCollection([
                                new Value('fixture-test-value1'),
                            ]))
                        ),
                        new ResultOutcomeVariable(
                            new QtiIdentifier('response-identifier'),
                            0,
                            4,
                            new ValueCollection([
                                new Value('fixture-test-value1'),
                                new Value('fixture-test-value2'),
                            ])
                        ),
                    ])
                ),
            ])
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $this->assertEquals(1, $element->getElementsByTagName('context')->length);
        $this->assertEquals(1, $element->getElementsByTagName('testResult')->length);
        $this->assertEquals(2, $element->getElementsByTagName('itemResult')->length);
    }

    public function testUnmarshallMinimal()
    {
        $component = new AssessmentResult(
            new Context()
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $this->assertEquals(1, $element->getElementsByTagName('context')->length);
        $this->assertEquals(0, $element->getElementsByTagName('testResult')->length);
        $this->assertEquals(0, $element->getElementsByTagName('itemResult')->length);
    }
}
