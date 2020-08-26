<?php

namespace qtismtest\runtime\rules;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\rules\ResponseConditionProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class ResponseConditionProcessorTest
 */
class ResponseConditionProcessorTest extends QtiSmTestCase
{
    /**
     * @dataProvider responseConditionMatchCorrectProvider
     *
     * @param string $response A QTI Identifier
     * @param float $expectedScore The expected score for a given $response
     * @throws MarshallerNotFoundException
     */
    public function testResponseConditionMatchCorrect($response, $expectedScore)
    {
        $rule = $this->createComponentFromXml('
			<responseCondition>
				<responseIf>
					<match>
						<variable identifier="RESPONSE"/>
						<correct identifier="RESPONSE"/>
					</match>
					<setOutcomeValue identifier="SCORE">
						<baseValue baseType="float">1</baseValue>
						</setOutcomeValue>
				</responseIf>
				<responseElse>
					<setOutcomeValue identifier="SCORE">
						<baseValue baseType="float">0</baseValue>
					</setOutcomeValue>
				</responseElse>
			</responseCondition>
		');

        $responseVarDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier">
				<correctResponse>
					<value>ChoiceA</value>
				</correctResponse>
			</responseDeclaration>
		');
        $responseVar = ResponseVariable::createFromDataModel($responseVarDeclaration);
        $this->assertTrue($responseVar->getCorrectResponse()->equals(new QtiIdentifier('ChoiceA')));

        // Set 'ChoiceA' to 'RESPONSE' in order to get a score of 1.0.
        $responseVar->setValue($response);

        $outcomeVarDeclaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
				<defaultValue>
					<value>0</value>
				</defaultValue>
			</outcomeDeclaration>		
		');
        $outcomeVar = OutcomeVariable::createFromDataModel($outcomeVarDeclaration);
        $this->assertEquals(0, $outcomeVar->getDefaultValue()->getValue());

        $state = new State([$responseVar, $outcomeVar]);
        $processor = new ResponseConditionProcessor($rule);
        $processor->setState($state);
        $processor->process();

        $this->assertInstanceOf(QtiFloat::class, $state['SCORE']);
        $this->assertTrue($expectedScore->equals($state['SCORE']));
    }

    /**
     * @return array
     */
    public function responseConditionMatchCorrectProvider()
    {
        return [
            [new QtiIdentifier('ChoiceA'), new QtiFloat(1.0)],
            [new QtiIdentifier('ChoiceB'), new QtiFloat(0.0)],
            [new QtiIdentifier('ChoiceC'), new QtiFloat(0.0)],
            [new QtiIdentifier('ChoiceD'), new QtiFloat(0.0)],
        ];
    }
}
