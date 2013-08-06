<?php

namespace qtism\runtime\rules;

use qtism\runtime\common\ProcessingException;

/**
 * An Exception to be thrown in a Rule Processing context.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RuleProcessingException extends ProcessingException {
	
	/**
	 * The error code to use when the result
	 * of a conditional expression is not boolean nor NULL.
	 * 
	 * @var integer
	 */
	const WRONG_CONDITION_VALUE = 10;
	
	/**
	 * Set the source of the error.
	 *
	 * @param Processable $source The source of the error.
	 * @throws InvalidArgumentException If $source is not an ExpressionProcessor object.
	 */
	public function setSource(Processable $source) {
		if ($source instanceof RuleProcessor) {
			parent::setSource($source);
		}
		else {
			$msg = "RuleProcessingException::setSource only accept RuleProcessor objects.";
			throw new InvalidArgumentException($msg);
		}
	}
}