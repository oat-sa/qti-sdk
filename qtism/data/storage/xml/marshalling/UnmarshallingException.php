<?php

namespace qtism\data\storage\xml\marshalling;

use \Exception;
use \DOMElement;

/**
 * Exception to be thrown when an error occurs during the unmarshalling process
 * of a DOMElement object.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class UnmarshallingException extends Exception {
	
	/**
	 * The DOMElement object that caused the exception to be thrown.
	 * 
	 * @var DOMElement
	 */
	private $DOMElement;
	
	/**
	 * Create a new instance of UnmarshallingException.
	 * 
	 * @param string $message A human-readable message which describe the exception.
	 * @param DOMElement $DOMElement The DOMElement object that caused the exception to be thrown.
	 * @param Exception $previous A previous Exception that caused the exception to be thrown.
	 */
	public function __construct($message, DOMElement $DOMElement, Exception $previous = null) {
		parent::__construct($message, 0, $previous);
		$this->setDOMElement($DOMElement);
	}
	
	/**
	 * Get the DOMElement object that caused the exception to be thrown.
	 * 
	 * @return DOMElement A DOMElement object.
	 */
	public function getDOMElement() {
		return $this->DOMElement;
	}
	
	/**
	 * Set the DOMElement object that caused the exception to be thrown.
	 * 
	 * @param DOMElement $DOMElement A DOMElement object.
	 */
	protected function setDOMElement(DOMElement $DOMElement) {
		$this->DOMElement = $DOMElement;
	}
}