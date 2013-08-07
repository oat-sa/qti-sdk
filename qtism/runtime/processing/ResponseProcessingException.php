<?php

namespace qtism\runtime\processing;

use qtism\runtime\common\Processable;
use qtism\runtime\common\ProcessingException;
use \InvalidArgumentException;

/**
 * An Exception to be thrown in an Expression Processing context.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseProcessingException extends ProcessingException {
	
	/**
	 * Error code to use when a response processing
	 * template cannot be found.
	 * 
	 * @var integer
	 */
	const TEMPLATE_NOT_FOUND = 11;
	
	/**
	 * Error code to use when a response processing
	 * template contains or produces errors.
	 * 
	 * @var integer
	 */
	const TEMPLATE_ERROR = 12;
	
	/**
	 * Set the source of the error.
	 * 
	 * @param Processable $source The source of the error.
	 * @throws InvalidArgumentException If $source is not a ResponseProcessingEngine object.
	 */
	public function setSource(Processable $source) {
		if ($source instanceof ResponseProcessingEngine) {
			parent::setSource($source);
		}
		else {
			$msg = "ResponseProcessingException::setSource only accepts ResponseProcessingEngine objects.";
			throw new InvalidArgumentException($msg);
		}
	}
}