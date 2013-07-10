<?php

namespace qtism\common;

use \RuntimeException;
use \Exception;

/**
 * The ResolutionException must be thrown when an error occurs while
 * resolving something.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResolutionException extends RuntimeException {
	
	/**
	 * Create a new ResolutionException.
	 * 
	 * @param string $message A human-readable description of the exception.
	 * @param Exception $previous An optional previous Exception that caused the exception to be thrown.
	 */
	public function __construct($message, Exception $previous = null) {
		parent::__construct($message, 0, $previous);
	}
}