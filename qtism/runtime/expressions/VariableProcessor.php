<?php

namespace qtism\runtime\expressions;

use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\runtime\tests\AssessmentTestState;
use qtism\data\expressions\Variable;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * This class aims at processing Variable expressions. In a test context,
 * the weighting will be applied.
 * 
 * From IMS QTI:
 * 
 * This expression looks up the value of an itemVariable that has been declared in a 
 * corresponding variableDeclaration or is one of the built-in variables. The result 
 * has the base-type and cardinality declared for the variable subject to the type 
 * promotion of weighted outcomes (see below).
 * 
 * During outcomes processing, values taken from an individual item session can be 
 * looked up by prefixing the name of the item variable with the identifier assigned 
 * to the item in the assessmentItemRef, separated by a period character. For example,
 * to obtain the value of the SCORE variable in the item referred to as Q01 you would 
 * use a variable instance with identifier Q01.SCORE.
 * 
 * In adaptive tests that contain items that are allowed to be replaced (i.e. that 
 * have the withReplacement attribute set to "true"), the same item can be 
 * instantiated more than once. In order to access the outcome variable values of 
 * each instantiation, a number that denotes the instance's place in the sequence of 
 * the item's instantiation is inserted between the item variable identifier and the 
 * item variable, separated by a period character. For example, to obtain the value 
 * of the SCORE variable in the item referred to as Q01 in its second instantiation 
 * you would use a variable instance, prefixed by the instantiation sequence number, 
 * prefixed by an identifier Q01.2.SCORE.
 * 
 * When looking up the value of a response variable it always takes the value 
 * assigned to it by the candidate's last submission. Unsubmitted responses are 
 * not available during expression evaluation.
 * 
 * The value of an item variable taken from an item instantiated multiple times from 
 * the same assessmentItemRef (through the use of selection withReplacement) is 
 * taken from the last instance submitted if submission is simultaneous, otherwise 
 * it is undefined.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class VariableProcessor extends ExpressionProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Variable) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The VariableProcessor class only accepts Variable expressions to be processed.";
			throw new InvalidArgumentException($expression);
		}
	}
	
	/**
	 * Process the Variable expression.
	 * 
	 * * If the requested variable does not exist, NULL is returned.
	 * * In a test context, if the requested weight does not exist, the raw value of the variable is returned.
	 * 
	 * @returns null|mixed The value of the target variable or NULL if the variable does not exist.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$state = $this->getState();
		$variableIdentifier = $this->getExpression()->getIdentifier();
		$weightIdentifier = $this->getExpression()->getWeightIdentifier();
		
		$variable = $state->getVariable($variableIdentifier);
		
		if (empty($variable)) {
			return null; 
		}
		
		$variableValue = $variable->getValue();
		if (empty($variableValue)) {
			return $variableValue; // Even if empty string, it is considered by QTI as null.
		}
		
		// We have a value for this variable, is it weighted?
		if ($state instanceof AssessmentTestState) {
			$weights = $state->getWeights();
			$weight = $weights[$weightIdentifier];
			
			// From IMS QTI:
			//Weights only apply to item variables with base types integer and float.
			// If the item variable is of any other type the weight is ignored.
			if (!empty($weight) && ($variable->getBaseType() == BaseType::INTEGER || $variable->getBaseType() == BaseType::FLOAT)) {
				
				if ($variable->getCardinality() == Cardinality::SINGLE) {
					$variableValue *= $weight->getValue();
				}
				else {
					
					// variableValue is an object, the weighting should not
					// affect the content of the state so a new container is created.
					$cloneValue = clone $variableValue;
					for ($i = 0; $i < count($cloneValue); $i++) {
						$cloneValue[$i] *= $weight->getValue();
					}
					
					$variableValue = $cloneValue;
				}
			}
		}
		
		return $variableValue;
	}
}