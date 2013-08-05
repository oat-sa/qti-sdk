<?php

namespace qtism\runtime\common;

use qtism\data\QtiComponent;
use \InvalidArgumentException;

/**
 * A composite class. StackTrace objects are composed of StackTraceItem objects.
 * 
 * The StackTraceItem class focuses on describing a traced QtiComponent object. What
 * happened to the traced QtiComponent object is described by a trace message.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StackTraceItem {
	
	/**
	 * A traced QtiComponent object.
	 * 
	 * @var QtiComponent
	 */
	private $component;
	
	/**
	 * A trace message about the traced QtiComponent object.
	 * 
	 * @var string
	 */
	private $traceMessage = '';
	
	/**
	 * Create a new StackTraceItem object.
	 * 
	 * @param QtiComponent $component The QtiComponent object which is the subject of $traceMessage.
	 * @param string $traceMessage A human-readable message about what happened whith $component.
	 * @throws InvalidArgumentException If $traceMessage is not a string.
	 */
	public function __construct(QtiComponent $component, $traceMessage) {
		$this->setComponent($component);
		$this->setTraceMessage($traceMessage);
	}
	
	/**
	 * Set the traced QtiComponent object.
	 * 
	 * @param QtiComponent $component A traced QtiComponent object.
	 */
	public function setComponent(QtiComponent $component) {
		$this->component = $component;
	}
	
	/**
	 * Get the traced QtiComponent object.
	 * 
	 * @return QtiComponent A traced QtiComponent object.
	 */
	public function getComponent() {
		return $this->component;
	}
	
	/**
	 * Get the message about the traced QtiComponent object.
	 * 
	 * @return string A human-readable message.
	 */
	public function getTraceMessage() {
		return $this->traceMessage;
	}
	
	/**
	 * Get the message about the traced QtiComponent object.
	 * 
	 * @param string $traceMessage A human-readable message.
	 * @throws InvalidArgumentException If $traceMessage is not a string.
	 */
	public function setTraceMessage($traceMessage) {
		if (is_string($traceMessage)) {
			$this->traceMessage = $traceMessage;
		}
		else {
			$msg = "The traceMessage argument must be a string, '" . gettype($traceMessage) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}