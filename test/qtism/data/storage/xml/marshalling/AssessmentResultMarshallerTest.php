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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille, <camille@taotesting.com>
 * @license GPLv2
 */

use qtism\data\results\AssessmentResult;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class AssessmentResultMarshallerTest extends QtiSmTestCase
{
	public function testUnmarshall()
    {

	}
	
	public function testValidMinimalXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <assessmentResult xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p2">
                <context />
            </assessmentResult>
        ';
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $this->assertTrue($dom->schemaValidate($this->getXsd()));
    }

    public function testValidMaximalXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <assessmentResult xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p2">
                <context sourcedId="fixture-sourcedId">
                    <sessionIdentifier sourceID="sessionIdentifier1-sourceID" identifier="sessionIdentifier1-id"/>
                    <sessionIdentifier sourceID="sessionIdentifier2-sourceID" identifier="sessionIdentifier2-id"/>
                </context>
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
            </assessmentResult>
        ';
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $this->assertTrue($dom->schemaValidate($this->getXsd()));

        $assessmentResult = $this->createComponentFromXml($xml);
        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);
    }

	protected function getXsd()
    {
        return
            __DIR__ . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'qtism' . DIRECTORY_SEPARATOR .
            'data' . DIRECTORY_SEPARATOR .
            'storage' . DIRECTORY_SEPARATOR .
            'xml' . DIRECTORY_SEPARATOR .
            'schemes' . DIRECTORY_SEPARATOR .
            'qtiv2p2' . DIRECTORY_SEPARATOR .
            'imsqti_result_v2p2.xsd';
    }
}
