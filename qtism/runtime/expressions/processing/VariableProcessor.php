<?php

namespace qtism\runtime\expressions\processing;

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
			$weight = $state->getWeights()->getByIdentifier($weightIdentifier);
			
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