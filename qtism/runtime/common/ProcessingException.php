<?php

namespace qtism\runtime\common;

use \RuntimeException;
use \Exception;

/**
 * This Exception should be raised at runtime while processing something (e.g. an expression,
 * an outcomeCondition, ...).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ProcessingException extends \RuntimeException {
	
	/**
	 * Code to use when the error of the nature is unknown.
	 *
	 * @var integer
	 */
	const UNKNOWN = 0;
	
	/**
	 * Code to use when a runtime error occcurs.
	 * 
	 * e.g. When a division by zero occurs, an overflow, ...
	 * 
	 * @var unknown_type
	 */
	const RUNTIME_ERROR = 1;
	
	/**
	 * Code to use when a requested variable does not exist or is not set.
	 *
	 * @var integer
	 */
	const NONEXISTENT_VARIABLE = 2;
	
	/**
	 * Code to use when a variable has not the expected type.
	 *
	 * e.g. If the correct processor retrieves a variable which is not
	 * a ResponseDeclaration.
	 *
	 * @var integer
	 */
	const WRONG_VARIABLE_TYPE = 3;
	
	/**
	 * Code to use when a variable has not the expected baseType.
	 *
	 * e.g. If the mapResponsePoint processor retrieves a variable with
	 * a baseType different than point.
	 *
	 * @var integer
	 */
	const WRONG_VARIABLE_BASETYPE = 4;
	
	/**
	 * Code to use when a variable is inconsistent.
	 *
	 * e.g. If the mapResponsePoint processor retrieves a variable with
	 * no areaMapping set.
	 *
	 * @var integer
	 */
	const INCONSISTENT_VARIABLE = 5;
	
	/**
	 * Code to use when a processor encounters an internal logic error.
	 *
	 * e.g. min >= max in the randomFloat processor.
	 *
	 * @var integer
	 */
	const LOGIC_ERROR = 6;
	
	private $source = null;
	
	/**
	 * Create a new ProcessingException.
	 * 
	 * @param string $msg A human-readable message describing the error.
	 * @param Processable $source A Processable object where the error occured.
	 * @param integer A code to characterize the error.
	 * @param Exception $previous An optional Exception object that caused the error.
	 */
	public function __construct($msg, Processable $source, $code = 0, Exception $previous = null) {
		
		parent::__construct($msg, $code, $previous);
		$this->setSource($source);
	}
	
	/**
	 * Set the source of the exception.
	 * 
	 * @param Processable $source The Processable object whithin the error occured.
	 */
	protected function setSource(Processable $source) {
		$this->source = $source;
	}
	
	/**
	 * Get the source of the exception.
	 * 
	 * @return Processable The Processable object within the error occured.
	 */
	public function getSource() {
		return $this->source;
	}
}