<?php

namespace qtism\runtime\expressions\processing;

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
	 * Code to use when the error of the nature is unknown.
	 * 
	 * @var integer
	 */
	const UNKNOWN = 0;
	
	/**
	 * Code to use when a state variable has not the expected type.
	 * 
	 * e.g. If the correct processor retrieves a variable which is not
	 * a ResponseDeclaration.
	 * 
	 * @var integer
	 */
	const WRONG_VARIABLE_TYPE = 1;
	
	/**
	 * Code to use when a state variable has not the expected baseType.
	 * 
	 * e.g. If the mapResponsePoint processor retrieves a variable with
	 * a baseType different than point.
	 * 
	 * @var integer
	 */
	const WRONG_VARIABLE_BASETYPE = 2;
	
	/**
	 * Code to use when a state variable is inconsistent.
	 * 
	 * e.g. If the mapResponsePoint processor retrieves a variable with
	 * no areaMapping set.
	 * 
	 * @var integer
	 */
	const INCONSISTENT_VARIABLE = 3;
	
	/**
	 * Code to use when a state variable does not exist or is not set.
	 * 
	 * @var integer
	 */
	const NONEXISTENT_VARIABLE = 4;
	
	/**
	 * Code to use when a processor encounters an internal logic error.
	 * 
	 * e.g. min >= max in the randomFloat processor.
	 * 
	 * @var unknown_type
	 */
	const LOGIC_ERROR = 5;
	
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