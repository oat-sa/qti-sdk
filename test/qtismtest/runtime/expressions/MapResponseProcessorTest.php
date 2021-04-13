<?php

namespace qtismtest\runtime\expressions;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\expressions\MapResponse;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\ExpressionProcessingException;
use qtism\runtime\expressions\MapResponseProcessor;
use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiFloat;

/**
 * Class MapResponseProcessorTest
 */
class MapResponseProcessorTest extends QtiSmTestCase
{
    public function testSimple()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="integer" cardinality="single">
				<mapping defaultValue="3">
					<mapEntry mapKey="0" mappedValue="1"/>
					<mapEntry mapKey="1" mappedValue="2"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState($state);

        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        // The variable has no value so the default mapping value is returned.
        $this::assertEquals(3, $result->getValue());

        $state['response1'] = new QtiInteger(0);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        $state['response1'] = new QtiInteger(1);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(2, $result->getValue());

        $state['response1'] = new QtiInteger(240);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(3, $result->getValue());
    }

    public function testMultipleComplexTyping()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="pair" cardinality="multiple">
				<mapping defaultValue="1">
					<mapEntry mapKey="A B" mappedValue="1.5"/>
					<mapEntry mapKey="C D" mappedValue="2.5"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor->setState($state);

        // No value could be tried to be matched.
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'B')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.5, $result->getValue());

        $state['response1'][] = new QtiPair('C', 'D');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4, $result->getValue());

        // mapEntries must be taken into account only once, as per QTI 2.1 spec.
        $state['response1'][] = new QtiPair('C', 'D');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4, $result->getValue()); // 2.5 taken into account only once!
    }

    public function testIdentifier()
    {
        $variableDeclaration = $this->createComponentFromXml('
	        <responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier">
                <correctResponse>
                    <value>Choice_3</value>
                </correctResponse>
                <mapping defaultValue="0">
                    <mapEntry mapKey="Choice_3" mappedValue="6"/>
                    <mapEntry mapKey="Choice_4" mappedValue="3"/>
                </mapping>
            </responseDeclaration>
	    ');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $variable->setValue(new QtiIdentifier('Choice_3'));
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="RESPONSE"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState(new State([$variable]));
        $result = $mapResponseProcessor->process();
        $this::assertEquals(6.0, $result->getValue());
    }

    public function testVariableNotDefined()
    {
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("No variable with identifier 'INVALID' could be found while processing MapResponse.");
        $this->expectExceptionCode(ExpressionProcessingException::NONEXISTENT_VARIABLE);

        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="INVALID"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->process();
    }

    public function testNoMapping()
    {
        // When no mapping is set. We consider a "fake" mapping with a default value of 0.
        $variableDeclaration = $this->createComponentFromXml('<responseDeclaration identifier="response1" baseType="duration" cardinality="multiple"/>');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $mapResponseProcessor->setState(new State([$variable]));
        $result = $mapResponseProcessor->process();
        $this::assertEquals(0.0, $result->getValue());

        $variableDeclaration = $this->createComponentFromXml('<responseDeclaration identifier="response1" baseType="identifier" cardinality="single"/>');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $state = new State([$variable]);
        $state['response1'] = new QtiIdentifier('correct_identifier');
        $mapResponseProcessor->setState($state);
        $result = $mapResponseProcessor->process();
        $this::assertEquals(0.0, $result->getValue());
    }

    public function testMultipleCardinalityIdentifierToFloat()
    {
        $responseDeclaration = $this->createComponentFromXml('
	        <responseDeclaration identifier="RESPONSE" baseType="identifier" cardinality="multiple">
                <correctResponse>
	                <value>Choice1</value>
	                <value>Choice6</value>
	                <value>Choice7</value>
	            </correctResponse>
	            <mapping>
	                <mapEntry mapKey="Choice1" mappedValue="2" caseSensitive="false"/>
	                <mapEntry mapKey="Choice6" mappedValue="20" caseSensitive="false"/>
                    <mapEntry mapKey="Choice9" mappedValue="20"/>
	                <mapEntry mapKey="Choice2" mappedValue="-20"/>
	                <mapEntry mapKey="Choice3" mappedValue="-20"/>
	                <mapEntry mapKey="Choice4" mappedValue="-20"/>
	                <mapEntry mapKey="Choice5" mappedValue="-20"/>
	                <mapEntry mapKey="Choice7" mappedValue="-20" caseSensitive="false"/>
	                <!-- no mapping for choice 8 -->
	            </mapping>
	        </responseDeclaration>
	    ');

        $mapResponseExpression = new MapResponse('RESPONSE');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpression);
        $state = new State();
        $mapResponseProcessor->setState($state);

        // State setup.
        $responseVariable = ResponseVariable::createFromDataModel($responseDeclaration);
        $state->setVariable($responseVariable);

        // RESPONSE is an empty container.
        $this::assertTrue($responseVariable->isNull());
        $result = $mapResponseProcessor->process();
        $this::assertEquals(0.0, $result->getValue());
        $this::assertInstanceOf(QtiFloat::class, $result);

        // RESPONSE is NULL.
        $responseVariable->setValue(null);
        $result = $mapResponseProcessor->process();
        $this::assertEquals(0.0, $result->getValue());
        $this::assertInstanceOf(QtiFloat::class, $result);

        // RESPONSE is Choice 6, Choice 8.
        // Note that Choice 8 has not mapping, the mapping's default value (0) must be then used.
        $state['RESPONSE'] = new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('Choice6'), new QtiIdentifier('Choice8')]);
        $result = $mapResponseProcessor->process();
        $this::assertEquals(20.0, $result->getValue());

        // Response is Choice 6, Choice 8, but the mapping's default values goes to -1.
        $mapping = $responseDeclaration->getMapping();
        $mapping->setDefaultValue(-1.0);
        $responseVariable = ResponseVariable::createFromDataModel($responseDeclaration);
        $state->setVariable($responseVariable);
        $state['RESPONSE'] = new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('Choice6'), new QtiIdentifier('Choice8')]);
        $result = $mapResponseProcessor->process();
        $this::assertEquals(19.0, $result->getValue());

        // Response is 'choice7', and 'identifierX'. choice7 is in lower case but its
        // associated entry is case insensitive. It must be then matched.
        // 'identifierX' will not be matched at all, the mapping's default value (still -1) will be used.
        $state['RESPONSE'] = new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('choice7'), new QtiIdentifier('identifierX')]);
        $result = $mapResponseProcessor->process();
        $this::assertEquals(-21.0, $result->getValue());

        // Empty state.
        // An exception is raised because no RESPONSE variable found.
        $state->reset();
        $this->expectException(ExpressionProcessingException::class);
        $result = $mapResponseProcessor->process();
    }

    public function testOutcomeDeclaration()
    {
        $this->expectException(ExpressionProcessingException::class);
        $variableDeclaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="response1" baseType="integer" cardinality="multiple">
				<mapping>
					<mapEntry mapKey="0" mappedValue="0.0"/>
				</mapping>
			</outcomeDeclaration>
		');
        $variable = OutcomeVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $mapResponseProcessor->setState(new State([$variable]));
        $mapResponseProcessor->process();
    }

    public function testEmptyMapEntryForStringSingleCardinality()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="string" cardinality="single">
				<mapping lowerBound="-1" defaultValue="0">
					<mapEntry mapKey="" mappedValue="-1"/>
					<mapEntry mapKey="Correct" mappedValue="1"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState($state);

        // No response provided, so the null value is equal to empty string...
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Empty string response provided. Expected is the same result as above...
        $state['response1'] = new QtiString('');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Non empty string (with match). Expected is 1.
        $state['response1'] = new QtiString('Correct');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        // Non empty string (without match). Expected is 1.
        $state['response1'] = new QtiString('Incorrect');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0, $result->getValue());
    }

    public function testEmptyMapEntryForStringMultipleCardinality()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="string" cardinality="multiple">
				<mapping lowerBound="-1" defaultValue="0">
					<mapEntry mapKey="" mappedValue="-1"/>
					<mapEntry mapKey="Correct" mappedValue="1"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState($state);

        // No response provided, so the null value is equal to empty string...
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Empty string provided, so we expect the same result as with null.
        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Null provided as the value of the only value of the container, we expect the same result as above.
        $state['response1'] = new MultipleContainer(BaseType::STRING, [null]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Empty container provided, as it is in QTI treated as null, we expect the same result as above.
        $state['response1'] = new MultipleContainer(BaseType::STRING);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Similar values are counted a single time as per spec.
        $state['response1'] = new MultipleContainer(BaseType::STRING, [null, null]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString(''), new QtiString('')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('Correct'), new QtiString('Correct')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('Correct'), new QtiString('')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString(''), new QtiString('Correct'), null]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0, $result->getValue());
    }

    public function testEmptyMapEntryForStringMultipleCardinalityCaseInsensitive()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="string" cardinality="multiple">
				<mapping lowerBound="-1" defaultValue="0">
					<mapEntry mapKey="" mappedValue="-1" caseSensitive="false"/>
					<mapEntry mapKey="Correct" mappedValue="1" caseSensitive="false"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState($state);

        // No response provided, so the null value is equal to empty string...
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Empty string provided, so we expect the same result as with null.
        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Two empty strings provided, so we expect the same result as with null (because similar values are only counted one time).
        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString(''), new QtiString('')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Null provided as the value of the only value of the container, we expect the same result as above.
        $state['response1'] = new MultipleContainer(BaseType::STRING, [null]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Null provided two times (will be considered onyl once).
        $state['response1'] = new MultipleContainer(BaseType::STRING, [null, null]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // Empty container provided, as it is in QTI treated as null, we expect the same result as above.
        $state['response1'] = new MultipleContainer(BaseType::STRING);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        // mapKeys are matched a single time.
        $state['response1'] = new MultipleContainer(BaseType::STRING, [null, null]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString(''), new QtiString('')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('Correct')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('correct')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('correct'), new QtiString('correct')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('CORRECT'), new QtiString('CORRECT')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('Correct'), new QtiString('correct')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(2, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString('Correct'), new QtiString('')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0, $result->getValue());

        $state['response1'] = new MultipleContainer(BaseType::STRING, [new QtiString(''), new QtiString('correct'), null]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0, $result->getValue());
    }

    public function testLowerBoundSingleCardinality()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" baseType="string" cardinality="single">
				<mapping lowerBound="-1" defaultValue="0">
					<mapEntry mapKey="incorrect_1" mappedValue="-1"/>
                    <mapEntry mapKey="incorrect_2" mappedValue="-2"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="RESPONSE"/>');

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState($state);

        // Should just "touch" the lower bound...
        $state['RESPONSE'] = new QtiString('incorrect_1');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1.0, $result->getValue());

        // Try to overflow the lower bound...
        $state['RESPONSE'] = new QtiString('incorrect_2');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1.0, $result->getValue());
    }

    public function testLowerBoundWithMultipleCardinality()
    {
        // In case of using a single cardinality variable lower bound is ignored!
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" baseType="string" cardinality="multiple">
				<mapping lowerBound="-1" defaultValue="0">
					<mapEntry mapKey="incorrect_1" mappedValue="-1"/>
                    <mapEntry mapKey="incorrect_2" mappedValue="-2"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="RESPONSE"/>');

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState($state);

        // Should just "touch" the lower bound...
        $state['RESPONSE'] = new MultipleContainer(BaseType::STRING, [new QtiString('incorrect_1')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1.0, $result->getValue());

        // Lower bound is the limit as we are mapping a container (multiple cardinality string).
        $state['RESPONSE'] = new MultipleContainer(BaseType::STRING, [new QtiString('incorrect_2')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1.0, $result->getValue());
    }

    public function testUpperBoundSingleCardinality()
    {
        // In case of using a single cardinality variable lower bound is ignored!
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" baseType="string" cardinality="single">
				<mapping lowerBound="-1" upperBound="1" defaultValue="0">
					<mapEntry mapKey="correct_1" mappedValue="1"/>
                    <mapEntry mapKey="correct_2" mappedValue="2"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="RESPONSE"/>');

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState($state);

        // Should just "touch" the upper bound...
        $state['RESPONSE'] = new QtiString('correct_1');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        // Try to overflow the upper bound...
        $state['RESPONSE'] = new QtiString('correct_2');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());
    }

    public function testUpperBoundWithMultipleCardinality()
    {
        // In case of using a single cardinality variable lower bound is ignored!
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" baseType="string" cardinality="multiple">
				<mapping lowerBound="-1" upperBound="1" defaultValue="0">
					<mapEntry mapKey="correct_1" mappedValue="1"/>
                    <mapEntry mapKey="correct_2" mappedValue="2"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="RESPONSE"/>');

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
        $mapResponseProcessor->setState($state);

        // Should just "touch" the upper bound...
        $state['RESPONSE'] = new MultipleContainer(BaseType::STRING, [new QtiString('correct_1')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        // Upper bound is the limit as we are mapping a container (multiple cardinality string).
        $state['RESPONSE'] = new MultipleContainer(BaseType::STRING, [new QtiString('correct_2')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());
    }

    public function testOrderedContainer()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="pair" cardinality="ordered">
				<mapping defaultValue="1">
					<mapEntry mapKey="A B" mappedValue="1.5"/>
					<mapEntry mapKey="C D" mappedValue="2.5"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor->setState($state);

        $state['response1'] = new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.5, $result->getValue());

        $state['response1'] = new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('C', 'D')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4.0, $result->getValue());

        $state['response1'] = new OrderedContainer(BaseType::PAIR, [new QtiPair('C', 'D'), new QtiPair('A', 'B')]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4.0, $result->getValue());
    }

    public function testDefaultValue()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="point" cardinality="ordered">
				<mapping defaultValue="1" upperBound="3">
					<mapEntry mapKey="0 0" mappedValue="1.5"/>
					<mapEntry mapKey="10 10" mappedValue="2.5"/>
				</mapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor->setState($state);

        // No response, should have 1.
        $state['response1'] = null;
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        // No match, should have 1.
        $state['response1'] = new OrderedContainer(BaseType::POINT, [new QtiPoint(-2, 2)]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        // No match, two times, should have 2.
        $state['response1'] = new OrderedContainer(BaseType::POINT, [new QtiPoint(-2, 2), new QtiPoint(100, 100)]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(2.0, $result->getValue());

        // No match, two times, but with the same value. Identical values are considered only one time.
        $state['response1'] = new OrderedContainer(BaseType::POINT, [new QtiPoint(-2, 2), new QtiPoint(-2, 2)]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        // One is not matched, the other is. Should have 2.5.
        $state['response1'] = new OrderedContainer(BaseType::POINT, [new QtiPoint(-2, 2), new QtiPoint(0, 0)]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(2.5, $result->getValue());

        // Both matched but there is an upperBound. Should have 3.0.
        $state['response1'] = new OrderedContainer(BaseType::POINT, [new QtiPoint(10, 10), new QtiPoint(0, 0)]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(3.0, $result->getValue());

        // Both will match, but because the matching values are identical, it only matches a single time.
        $state['response1'] = new OrderedContainer(BaseType::POINT, [new QtiPoint(10, 10), new QtiPoint(10, 10)]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(2.5, $result->getValue());
    }

    public function testIdenticicalMapKeysSingleCardinality()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" baseType="integer" cardinality="single">
				<mapping>
					<mapEntry mapKey="1" mappedValue="1.5"/>
					<mapEntry mapKey="1" mappedValue="2.5"/>
				</mapping>
			</responseDeclaration>
		');

        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="RESPONSE"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor->setState($state);

        // Null value, should have 0.
        $state['RESPONSE'] = null;
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0.0, $result->getValue());

        // 1 has two corresponding mapEntries. The final result will be the sum of mapped values 1.5 & 2.5
        $state['RESPONSE'] = new QtiInteger(1);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4.0, $result->getValue());
    }

    public function testIdenticicalMapKeysMultipleCardinality()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" baseType="integer" cardinality="multiple">
				<mapping>
					<mapEntry mapKey="1" mappedValue="1.5"/>
					<mapEntry mapKey="1" mappedValue="2.5"/>
				</mapping>
			</responseDeclaration>
		');

        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="RESPONSE"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor->setState($state);

        // Null value, should have 0.
        $state['RESPONSE'] = null;
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0.0, $result->getValue());

        // 1 has two corresponding mapEntries. The final result will be the sum of mapped values 1.5 & 2.5
        $state['RESPONSE'] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(1)]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4.0, $result->getValue());

        // 1 has two corresponding mapEntries. The final result will be the sum of mapped values 1.5 & 2.5. The value 1 will be considered only a single time.
        $state['RESPONSE'] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(1)]);
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4.0, $result->getValue());
    }

    public function testSingleCardinalityStringCaseSensitivity()
    {
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" baseType="string" cardinality="single">
				<mapping>
					<mapEntry mapKey="correct" mappedValue="1" caseSensitive="true"/>
					<mapEntry mapKey="AnotherCorrect" mappedValue="1" caseSensitive="false"/>
				</mapping>
			</responseDeclaration>
		');

        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="RESPONSE"/>');
        $mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);

        $state = new State();
        $state->setVariable($variable);
        $mapResponseProcessor->setState($state);

        // Null value, should have 0.
        $state['RESPONSE'] = null;
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0.0, $result->getValue());

        // Empty string value, should have 0.
        $state['RESPONSE'] = new QtiString('');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0.0, $result->getValue());

        $state['RESPONSE'] = new QtiString('CoRrEcT');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0.0, $result->getValue());

        $state['RESPONSE'] = new QtiString('correct');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        $state['RESPONSE'] = new QtiString('anothercorrect');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        $state['RESPONSE'] = new QtiString('AnotherCorrect');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.0, $result->getValue());

        $state['RESPONSE'] = new QtiString('xxx');
        $result = $mapResponseProcessor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0.0, $result->getValue());
    }
}
