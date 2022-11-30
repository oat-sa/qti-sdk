<?php

declare(strict_types=1);

namespace qtismtest\runtime\processing;

use InvalidArgumentException;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\processing\ResponseProcessingEngine;
use qtism\runtime\rules\ProcessingCollectionException;
use qtismtest\QtiSmTestCase;

/**
 * Class ResponseProcessingEngineTest
 */
class ResponseProcessingEngineTest extends QtiSmTestCase
{
    public function testResponseProcessingMatchCorrect(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
        ');

        $responseDeclaration = $this->createComponentFromXml('
            <responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier">
                <correctResponse>
                    <value>ChoiceA</value>
                </correctResponse>
            </responseDeclaration>		
        ');

        $outcomeDeclaration = $this->createComponentFromXml('
            <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
                <defaultValue>
                    <value>0</value>
                </defaultValue>
            </outcomeDeclaration>
        ');

        $respVar = ResponseVariable::createFromDataModel($responseDeclaration);
        $outVar = OutcomeVariable::createFromDataModel($outcomeDeclaration);
        $context = new State([$respVar, $outVar]);

        $engine = new ResponseProcessingEngine($responseProcessing, $context);

        // --> answer as a correct response.
        $context['RESPONSE'] = new QtiIdentifier('ChoiceA');
        $engine->process();
        $this::assertInstanceOf(QtiFloat::class, $context['SCORE']);
        $this::assertEquals(1.0, $context['SCORE']->getValue());

        // --> answer as an incorrect response.
        $context['RESPONSE'] = new QtiIdentifier('ChoiceB');
        $engine->process();
        $this::assertInstanceOf(QtiFloat::class, $context['SCORE']);
        $this::assertEquals(0.0, $context['SCORE']->getValue());
    }

    public function testResponseProcessingNoResponseRule(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing>
                <responseCondition>
                    <responseIf>
                        <match>
                            <variable identifier="RESPONSE"/>
                            <baseValue baseType="identifier">ChoiceA</baseValue>
                        </match>
                        <!-- Do nothing... -->
                    </responseIf>
                    <responseElseIf>
                        <match>
                            <variable identifier="RESPONSE"/>
                            <baseValue baseType="identifier">ChoiceB</baseValue>
                        </match>
                        <!-- Do nothing... -->
                    </responseElseIf>
                    <responseElse>
                        <setOutcomeValue identifier="SCORE">
                            <baseValue baseType="float">1.0</baseValue>
                        </setOutcomeValue>
                    </responseElse>
                </responseCondition>
            </responseProcessing>
        ');

        $responseDeclaration = $this->createComponentFromXml('
            <responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier"/>	
        ');

        $outcomeDeclaration = $this->createComponentFromXml('
            <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float"/>
        ');

        $respVar = ResponseVariable::createFromDataModel($responseDeclaration);
        $outVar = OutcomeVariable::createFromDataModel($outcomeDeclaration);
        $context = new State([$respVar, $outVar]);

        $engine = new ResponseProcessingEngine($responseProcessing, $context);

        $context['RESPONSE'] = new QtiIdentifier('ChoiceA');
        $engine->process();
        $this::assertNull($context['SCORE']);

        $context['RESPONSE'] = new QtiIdentifier('ChoiceB');
        $engine->process();
        $this::assertNull($context['SCORE']);

        $context['RESPONSE'] = new QtiIdentifier('ChoiceC');
        $engine->process();
        $this::assertInstanceOf(QtiFloat::class, $context['SCORE']);
        $this::assertEquals(1.0, $context['SCORE']->getValue());
    }

    public function testResponseProcessingExitResponse(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing>
                <setOutcomeValue identifier="OUTCOME">
                    <baseValue baseType="integer">1</baseValue>
                </setOutcomeValue>
                <exitResponse/>
                <!-- Should never be executed! -->
                <setOutcomeValue identifier="OUTCOME">
                    <baseValue baseType="integer">2</baseValue>
                </setOutcomeValue>
            </responseProcessing>
        ');

        $state = new State();
        $state->setVariable(new OutcomeVariable('OUTCOME', Cardinality::SINGLE, BaseType::INTEGER));
        $engine = new ResponseProcessingEngine($responseProcessing);
        $engine->setContext($state);
        $engine->process();
        $this::assertEquals(1, $state['OUTCOME']->getValue());
    }

    public function testResponseProcessingWithError(): void
    {
        $this->expectException(ProcessingCollectionException::class);
        $this->expectExceptionMessage('Unexpected error(s) occurred while processing response');

        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing>
                <responseCondition>
                    <responseIf>
                        <and>
                            <match>
                                <index n="1">
                                    <customOperator class="qti.customOperators.CsvToOrdered"><fieldValue fieldIdentifier="points"><variable identifier="RESPONSE"/></fieldValue>
                                    </customOperator>
                                </index>
                                <fieldValue fieldIdentifier="vertex">
                                    <correct identifier="RESPONSE"/>
                                </fieldValue>
                            </match>
                        </and>
                        <setOutcomeValue identifier="SCORE">
                            <sum>
                                <variable identifier="SCORE"/>
                                <baseValue baseType="float">1</baseValue>
                            </sum>
                        </setOutcomeValue>
                    </responseIf>
                    <responseElse>
                        <setOutcomeValue identifier="SCORE">
                            <baseValue baseType="float">0</baseValue>
                        </setOutcomeValue>
                    </responseElse>
                </responseCondition>
            </responseProcessing>
        ');

        $engine = new ResponseProcessingEngine($responseProcessing);
        $engine->process();
    }

    public function testResponseProcessingErrorCollection(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing>
                <responseCondition>
                    <responseIf>
                        <and>
                            <match>
                                <index n="1">
                                    <customOperator class="qti.customOperators.CsvToOrdered"><fieldValue fieldIdentifier="points"><variable identifier="RESPONSE"/></fieldValue>
                                    </customOperator>
                                </index>
                                <fieldValue fieldIdentifier="vertex">
                                    <correct identifier="RESPONSE"/>
                                </fieldValue>
                            </match>
                        </and>
                        <setOutcomeValue identifier="SCORE">
                            <sum>
                                <variable identifier="SCORE"/>
                                <baseValue baseType="float">1</baseValue>
                            </sum>
                        </setOutcomeValue>
                    </responseIf>
                    <responseElse>
                        <setOutcomeValue identifier="SCORE">
                            <baseValue baseType="float">0</baseValue>
                        </setOutcomeValue>
                    </responseElse>
                </responseCondition>
            </responseProcessing>
        ');

        $engine = new ResponseProcessingEngine($responseProcessing);

        try {
            $engine->process();
        } catch (ProcessingCollectionException $exceptions) {}

        self::assertNotNull($exceptions);
        self::assertEquals(
            'The FieldValue operator only accepts operands with a cardinality of record.',
            $exceptions->getProcessingExceptions()[0]->getMessage()
        );
    }

    public function testSetOutcomeValueWithSum(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing>
                <responseCondition>
                  <responseIf>
                    <isNull>
                      <variable identifier="response-X" />
                    </isNull>
                    <setOutcomeValue identifier="score-X">
                      <baseValue baseType="integer">0</baseValue>
                    </setOutcomeValue>
                  </responseIf>
                  <responseElseIf>
                    <match>
                      <variable identifier="response-X" />
                      <correct identifier="response-X" />
                    </match>
                    <setOutcomeValue identifier="score-X">
                      <variable identifier="maxscore-X" />
                    </setOutcomeValue>
                  </responseElseIf>
                  <responseElse>
                    <setOutcomeValue identifier="score-X">
                      <baseValue baseType="integer">0</baseValue>
                    </setOutcomeValue>
                  </responseElse>
                </responseCondition>
                <setOutcomeValue identifier="SCORE">
                  <sum>
                    <variable identifier="score-X" />
                  </sum>
                </setOutcomeValue>
                <setOutcomeValue identifier="MAXSCORE">
                  <sum>
                    <variable identifier="maxscore-X" />
                  </sum>
                </setOutcomeValue>
              </responseProcessing>
        ');

        $responseX = new ResponseVariable('response-X', Cardinality::MULTIPLE, BaseType::IDENTIFIER);
        $responseX->setCorrectResponse(new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('ChoiceA'), new QtiIdentifier('ChoiceB')]));

        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.));
        $maxScore = new OutcomeVariable('MAXSCORE', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.));
        $scoreX = new OutcomeVariable('score-X', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.));
        $maxScoreX = new OutcomeVariable('maxscore-X', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(1.));

        $state = new State(
            [
                $responseX,
                $score,
                $maxScore,
                $scoreX,
                $maxScoreX,
            ]
        );

        // Try correct response...
        $state['response-X'][] = new QtiIdentifier('ChoiceA');
        $state['response-X'][] = new QtiIdentifier('ChoiceB');

        $engine = new ResponseProcessingEngine($responseProcessing, $state);
        $engine->process();

        $this::assertEquals(1., $state['score-X']->getValue());
        $this::assertEquals(1., $state['SCORE']->getValue());
        $this::assertEquals(1., $state['MAXSCORE']->getValue());
    }

    public function testWrongComponentType(): void
    {
        $responseProcessing = $this->createComponentFromXml(
            '<responseIf>
                <isNull>
                  <variable identifier="response-X" />
                </isNull>
                <setOutcomeValue identifier="score-X">
                  <baseValue baseType="integer">0</baseValue>
                </setOutcomeValue>
              </responseIf>'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The ResponseProcessingEngine class only accepts ResponseProcessing objects to be executed.');

        $engine = new ResponseProcessingEngine($responseProcessing);
    }

    public function testAddTemplateMappingWrongFirstParam(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
        ');

        $engine = new ResponseProcessingEngine($responseProcessing);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The uri argument must be a string, 'integer' given.");

        $engine->addTemplateMapping(10, 'http://taotesting.com');
    }

    public function testAddTemplateMappingWrongSecondParam(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
        ');

        $engine = new ResponseProcessingEngine($responseProcessing);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The url argument must be a string, 'string' given.");

        $engine->addTemplateMapping('http://taotesting.com', 10);
    }

    public function testRemoveTemplateMappingWrongUrl(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
        ');

        $engine = new ResponseProcessingEngine($responseProcessing);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The uri argument must be a string, 'integer' given.");

        $engine->removeTemplateMapping(10);
    }

    public function testRemoveTemplateMapping(): void
    {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
        ');

        $engine = new ResponseProcessingEngine($responseProcessing);
        $engine->removeTemplateMapping('http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct');

        $this::assertTrue(true, 'The template mapping removal should not produce any error.');
    }

    public function testNotOperator(): void
    {
        $var = new OutcomeVariable('NOTRESULT', Cardinality::SINGLE, BaseType::BOOLEAN);
        $state = new State([$var]);

        $responseProcessing = $this->createComponentFromXml('
            <!--
                if (isNull($NOTRESULT)) {
                    $NOTRESULT = true;
                }
            -->
            <responseProcessing>
                <responseCondition>
                    <responseIf>
                        <isNull>
                            <variable identifier="NOTRESULT"/>
                        </isNull>
                        <setOutcomeValue identifier="NOTRESULT">
                            <not>
                                <baseValue baseType="boolean">true</baseValue>
                            </not>
                        </setOutcomeValue>
                    </responseIf>
                </responseCondition>
            </responseProcessing>
        ');

        $engine = new ResponseProcessingEngine($responseProcessing, $state);
        $engine->process();

        $this::assertFalse($state['NOTRESULT']->getValue());
    }
}
