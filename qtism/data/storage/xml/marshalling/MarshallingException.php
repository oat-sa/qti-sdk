<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use \Exception;
use \DOMElement;

/**
 * Exception to be thrown when an error occurs during the marshalling process
 * of a QtiComponent.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MarshallingException extends Exception {
	
	/**
	 * A QtiComponent object that caused the exception to be thrown.
	 * 
	 * @var QtiComponent
	 */
	private $component;
	
	/**
	 * Create a new instance of MarshallingException.
	 * 
	 * @param string $message A human-readable message which describes the exception.
	 * @param QtiComponent $component A QtiComponent object that caused the exception to be thrown.
	 * @param Exception $previous A previous exception that caused the exception to be thrown.
	 */
	public function __construct($message, QtiComponent $component, $previous = null) {
		parent::__construct($message, 0, $previous);
		$this->setComponent($component);
	}
	
	/**
	 * Get the QtiComponent object that caused the exception to be thrown.
	 * 
	 * @return QtiComponent A QtiComponent object.
	 */
	public function getComponent() {
		return $this->component;
	}
	
	/**
	 * Set the QTIcomponent object that caused the exception to be thrown.
	 * 
	 * @param QtiComponent $component A QTI Component object.
	 */
	protected function setComponent(QtiComponent $component) {
		$this->component = $component;
	}
}