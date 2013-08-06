<?php

namespace qtism\runtime\common;

use qtism\data\QtiComponent;
use qtism\runtime\common\StackTrace;

/**
 * The AbstractEngine class is the common sub-class to all engines.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractEngine implements Processable {
	
	/**
	 * The QtiComponent that will be the object of the
	 * processing.
	 * 
	 * @var QtiComponent
	 */
	private $component;
	
	/**
	 * The StackTrace of the processing, giving some
	 * information about the running processing.
	 * 
	 * @var StackTrace
	 */
	private $stackTrace;
	
	/**
	 * A Context for the ExpressionEngine.
	 *
	 * @var State
	 */
	private $context;
	
	/**
	 * Create a new AbstractEngine object.
	 *
	 * @param QtiComponent $component A QtiComponent object to process.
	 * @param State $context (optional) The execution context. If no execution context is given, a virgin one will be set up.
	 */
	public function __construct(QtiComponent $component, State $context = null) {
		$this->setComponent($component);
		$this->setContext((is_null($context) === true) ? new State() : $context);
		$this->setStackTrace(new StackTrace());
	}
	
	/**
	 * Set the QtiComponent object to be processed by the Engine.
	 * 
	 * @param QtiComponent $component A QtiComponent object.
	 */
	public function setComponent(QtiComponent $component) {
		$this->component = $component;
	}
	
	/**
	 * Get the QtiComponent object to be processed by the Engine.
	 * 
	 * @return QtiComponent A QtiComponent object.
	 */
	public function getComponent() {
		return $this->component;
	}
	
	/**
	 * Set the execution context of the ExpressionEngine.
	 *
	 * @param State $context A State object representing the execution context.
	 */
	public function setContext(State $context) {
		$this->context = $context;
	}
	
	/**
	 * Get the execution context of the ExpressionEngine.
	 *
	 * @return State A State object representing the execution context.
	 */
	public function getContext() {
		return $this->context;
	}
	
	/**
	 * Set the StackTrace object that will hold information
	 * about the running processing.
	 * 
	 * @param StackTrace $stackTrace A StackTrace object.
	 */
	protected function setStackTrace(StackTrace $stackTrace) {
		$this->stackTrace = $stackTrace;
	}
	
	/**
	 * Get the StackTrace object that will hold information
	 * about the running processing.
	 * 
	 * @return StackTrace A StackTrace object.
	 */
	public function getStackTrace() {
		return $this->stackTrace;
	}
	
	/**
	 * Add an entry in the stack trace about the QtiComponent being
	 * processed.
	 *
	 * @param string $message A trace message.
	 */
	protected function trace($message) {
		$item = new StackTraceItem($this->getComponent(), $message);
		$this->getStackTrace()->push($item);
	}
	
	/**
	 * Process the target QtiComponent.
	 * 
	 * @return mixed|null The result of the processing if any or NULL if no result.
	 * @throws ProcessingException
	 */
	public abstract function process();
}