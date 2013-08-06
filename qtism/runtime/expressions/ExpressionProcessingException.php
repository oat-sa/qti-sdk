<?php

namespace qtism\runtime\expressions;

use qtism\runtime\common\Processable;
use qtism\runtime\common\ProcessingException;
use \InvalidArgumentException;

/**
 * An Exception to be thrown in an Expression Processing context.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExpressionProcessingException extends ProcessingException {
	
	/**
	 * Set the source of the error.
	 * 
	 * @param Processable $source The source of the error.
	 * @throws InvalidArgumentException If $source is not an ExpressionProcessor object.
	 */
	public function setSource(Processable $source) {
		if ($source instanceof ExpressionProcessor) {
			parent::setSource($source);
		}
		else {
			$msg = "ExpressionProcessingException::setSource only accept ExpressionProcessor objects.";
			throw new InvalidArgumentException($msg);
		}
	}
}