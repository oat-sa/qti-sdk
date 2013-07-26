<?php

namespace qtism\runtime\expressions\processing;

use qtism\runtime\common\ResponseVariable;
use qtism\data\expressions\Expression;
use qtism\data\expressions\Correct;
use \InvalidArgumentException;

/**
 * The CorrectProcessor class aims at processing Correct Expression objects from the 
 * QTI Data Model.
 * 
 * FROM IMS QTI:
 * 
 * This expression looks up the declaration of a response variable and returns the
 * associated correctResponse or NULL if no correct value was declared. When used
 * in outcomes processing item identifier prefixing (see variable) may be used to
 * obtain the correct response from an individual item.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class CorrectProcessor extends ExpressionProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Correct) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The CorrectProcessor can only process Correct Expression objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Returns the related correstResponse as a QTI Runtime compliant value.
	 * 
	 * * If no variable can be matched, null is returned.
	 * * If the target variable has no correctResponse, null is returned.
	 * 
	 * An ExpressionProcessingException is thrown if:
	 * 
	 * * The targeted variable is not a ResponseVariable.
	 * 
	 * @return mixed A QTI Runtime compliant value or null.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$expr = $this->getExpression();
		$state = $this->getState();
		$identifier=  $expr->getIdentifier();
		
		$var = $state->getVariable($identifier);
		
		if (is_null($var)) {
			return null;
		}
		else if ($var instanceof ResponseVariable) {
			return $var->getCorrectResponse();
		}
		else {
			$msg = "The variable with identifier '${identifier}' is not a ResponseVariable object.";
			throw new ExpressionProcessingException($msg, $this, ExpressionProcessingException::WRONG_VARIABLE_TYPE);
		}
	}
}