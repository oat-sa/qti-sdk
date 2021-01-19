<?php

namespace qtismtest;

use qtism\common\datatypes\QtiDuration;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\storage\xml\marshalling\ExtendedAssessmentItemRefMarshaller;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\SessionManager;

/**
 * Class QtiSmAssessmentItemTestCase
 */
abstract class QtiSmAssessmentItemTestCase extends QtiSmTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @param $xmlString
     * @return ExtendedAssessmentItemRef
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function createExtendedAssessmentItemRefFromXml($xmlString)
    {
        $marshaller = new ExtendedAssessmentItemRefMarshaller('2.1');
        $element = $this->createDOMElement($xmlString);
        return $marshaller->unmarshall($element);
    }

    /**
     * Instantiate a basic item session for a non-adaptive, non-timeDependent item with two variables:
     *
     * * RESPONSE (single, identifier, correctResponse = 'ChoiceB')
     * * SCORE (single, float, defaultValue = 0.0)
     *
     * The responseProcessing for item of the session is the template 'match_correct'.
     *
     * @param QtiDuration|null $acceptableLatency
     * @return AssessmentItemSession
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function instantiateBasicAssessmentItemSession(QtiDuration $acceptableLatency = null)
    {
        $itemRef = $this->createExtendedAssessmentItemRefFromXml('
            <assessmentItemRef identifier="Q01" href="./Q01.xml" adaptive="false" timeDependent="false">
                <responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier">
					<correctResponse>
						<value>ChoiceB</value>
					</correctResponse>
				</responseDeclaration>
                <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
					<defaultValue>
						<value>0.0</value>
					</defaultValue>
				</outcomeDeclaration>
                <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
            </assessmentItemRef>
        ');

        $manager = new SessionManager();

        if ($acceptableLatency !== null) {
            $manager->setAcceptableLatency($acceptableLatency);
        }

        return new AssessmentItemSession($itemRef, $manager);
    }

    /**
     * Instantiate a basic item session for an adaptive, non-timeDependent item with two variables:
     *
     * * RESPONSE (single, identifier, correctResponse = 'ChoiceB'
     * * SCORE (single, float, defaultValue = 0.0)
     *
     * The responseProcessing sets:
     *
     * * SCORE to 0, completionStatus to 'incomplete', if the response is not 'ChoiceB'.
     * * SCORE to 1, completionStatus to 'complete', if the response is 'ChoiceB'.
     *
     * @param QtiDuration|null $acceptableLatency
     * @return AssessmentItemSession
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function instantiateBasicAdaptiveAssessmentItem(QtiDuration $acceptableLatency = null)
    {
        $itemRef = $this->createExtendedAssessmentItemRefFromXml('
            <assessmentItemRef identifier="Q01" href="./Q01.xml" adaptive="true" timeDependent="false">
                <responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier">
					<correctResponse>
						<value>ChoiceB</value>
					</correctResponse>
				</responseDeclaration>
                <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
					<defaultValue>
						<value>0.0</value>
					</defaultValue>
				</outcomeDeclaration>
	
                <!-- The candidate is allowed to attempt the item until he provides the correct answer -->
                <responseProcessing>
                    <responseCondition>
                        <responseIf>
                            <match>
                                <variable identifier="RESPONSE"/>
                                <baseValue baseType="identifier">ChoiceB</baseValue>
                            </match>
                            <setOutcomeValue identifier="SCORE">
                                <baseValue baseType="float">1</baseValue>
                            </setOutcomeValue>
                            <setOutcomeValue identifier="completionStatus">
                                <baseValue baseType="identifier">completed</baseValue>
                            </setOutcomeValue>
                        </responseIf>
                        <responseElse>
                            <setOutcomeValue identifier="SCORE">
                                <baseValue baseType="float">0</baseValue>
                            </setOutcomeValue>
                            <setOutcomeValue identifier="completionStatus">
                                <baseValue baseType="identifier">incomplete</baseValue>
                            </setOutcomeValue>
                        </responseElse>
                    </responseCondition>
                </responseProcessing>
            </assessmentItemRef>
        ');

        $manager = new SessionManager();

        if ($acceptableLatency !== null) {
            $manager->setAcceptableLatency($acceptableLatency);
        }

        return new AssessmentItemSession($itemRef, $manager);
    }
}
