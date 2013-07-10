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
	
	private $source = null;
	
	/**
	 * Create a new ProcessingException.
	 * 
	 * @param string $msg A human-readable message describing the error.
	 * @param Processable $source A Processable object where the error occured.
	 * @param Exception $previous An optional Exception object that caused the error.
	 */
	public function __construct($msg, Processable $source, Exception $previous = null) {
		parent::__construct($msg, $previous);
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