<?php

namespace qtism\runtime\rules;

use qtism\runtime\common\ProcessingException;
use qtism\runtime\common\Processable;

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
	 * The error code to use when the exitResponse rule is invoked
	 * during rule processing.
	 * 
	 * @var integer
	 */
	const EXIT_RESPONSE = 11;
	
	/**
	 * The error code to use when the exitTest rule is invoked
	 * during rule processing.
	 *
	 * @var integer
	 */
	const EXIT_TEST = 12;
	
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