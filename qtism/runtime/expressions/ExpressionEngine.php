<?php

namespace qtism\runtime\expressions;

use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\common\StackTraceItem;
use qtism\data\QtiComponent;
use qtism\runtime\common\StackTrace;
use qtism\runtime\expressions\operators\OperatorProcessorFactory;
use qtism\data\expressions\operators\Operator;
use qtism\data\expressions\Expression;
use qtism\runtime\expressions\operators\OperatorProcessingException;
use qtism\runtime\common\State;
use qtism\runtime\common\Processable;

/**
 * The ExpressionEngine class provides a bed for Expression processing, by processing
 * a given Expression object following a given execution context (a State object) providing
 * the variables and the values needed to process the Expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExpressionEngine implements Processable {
	
	/**
	 * A Context for the ExpressionEngine.
	 * 
	 * @var State
	 */
	private $context;
	
	/**
	 * The Expression object the ExpressionEngine will process.
	 * 
	 * @var Expression
	 */
	private $expression;
	
	/**
	 * 
	 * The expression trail.
	 * 
	 * @var array
	 */
	private $trail = array();
	
	/**
	 * The expression marker.
	 * 
	 * @var array
	 */
	private $marker = array();
	
	/**
	 * The ExpressionProcessorFactory object.
	 * 
	 * @var ExpressionProcessorFactory
	 */
	private $expressionProcessorFactory;
	
	/**
	 * The OperatorProcessorFactory object.
	 * 
	 * @var OperatorProcessingException
	 */
	private $operatorProcessorFactory;
	
	/**
	 * The StackTrace of the engine.
	 * 
	 * @var StackTrace;
	 */
	private $stackTrace;
	
	/**
	 * The operands stack.
	 * 
	 * @var OperandsCollection
	 */
	private $operands;
	
	/**
	 * Create a new ExpressionEngine object.
	 * 
	 * @param Expression $expression The Expression object to be processed.
	 * @param State $context (optional) The execution context. If no execution context is given, a virgin one will be set up.
	 */
	public function __construct(Expression $expression, State $context = null) {
		$this->setExpression($expression);
		$this->setContext((is_null($context) === true) ? new State() : $context);
		$this->setExpressionProcessorFactory(new ExpressionProcessorFactory());
		$this->setOperatorProcessorFactory(new OperatorProcessorFactory());
		$this->setStackTrace(new StackTrace());
		$this->setOperands(new OperandsCollection());
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
	 * Set the Expression object to be processed by the ExpressionEngine.
	 * 
	 * @param Expression $expression An Expression object to be processed.
	 */
	public function setExpression(Expression $expression) {
		$this->expression = $expression;
	}
	
	/**
	 * Get the Expression object to be processed by the ExpressionEngine.
	 * 
	 * @return Expression An Expression object to be processed.
	 */
	public function getExpression() {
		return $this->expression;
	}
	
	/**
	 * Set the ExpressionProcessorFactory object to be used by the engine.
	 * 
	 * @param ExpressionProcessorFactory $expressionProcessorFactory An ExpressionProcessorFactory object.
	 */
	public function setExpressionProcessorFactory(ExpressionProcessorFactory $expressionProcessorFactory) {
		$this->expressionProcessorFactory = $expressionProcessorFactory;
	}
	
	/**
	 * Get the ExpressionProcessorFactory currently in use.
	 * 
	 * @return ExpressionProcessorFactory An ExpressionProcessorFactory object.
	 */
	public function getExpressionProcessorFactory() {
		return $this->expressionProcessorFactory;
	} 
	
	/**
	 * Set the OperatorProcessorFactory object to be used by the engine.
	 * 
	 * @param OperatorProcessorFactory $operatorProcessorFactory An OperatorProcessorFactory object.
	 */
	public function setOperatorProcessorFactory(OperatorProcessorFactory $operatorProcessorFactory) {
		$this->operatorProcessorFactory = $operatorProcessorFactory;
	}
	
	/**
	 * Get the OperatorProcessorFactory object currenlty in use.
	 * 
	 * @return OperatorProcessorFactory
	 */
	public function getOperatorProcessorFactory() {
		return $this->operatorProcessorFactory;
	}
	
	/**
	 * Set the StackTrace of the engine.
	 * 
	 * @param StackTrace $stackTrace A StackTrace object.
	 */
	protected function setStackTrace(StackTrace $stackTrace) {
		$this->stackTrace = $stackTrace;
	}
	
	/**
	 * Get the execution Stack trace.
	 * 
	 * @return StackTrace The current execution stack trace.
	 */
	public function getStackTrace() {
		return $this->stackTrace;
	}
	
	/**
	 * Get the Operands stack.
	 * 
	 * @return OperandsCollection An OperandsCollection object.
	 */
	protected function getOperands() {
		return $this->operands;
	}
	
	/**
	 * Set the Operands stack.
	 * 
	 * @param OperandsCollection $operands An OperandsCo
	 */
	protected function setOperands(OperandsCollection $operands) {
		$this->operands = $operands;
	}
	
	/**
	 * Add an entry in the stack trace.
	 * 
	 * @param QtiComponent $component A component you want to trace.
	 * @param string $message A trace message.
	 */
	protected function trace(QtiComponent $component, $message) {
		$item = new StackTraceItem($component, $message);
		$this->getStackTrace()->push($item);
	}
	
	/**
	 * Push an Expression object on the trail stack.
	 * 
	 * @param Expression|ExpressionCollection $expression An Expression/ExpressionCollection object to be pushed on top of the trail stack.
	 */
	protected function pushTrail($expression) {
		
		$trail = &$this->getTrail();
		
		if ($expression instanceof Expression) {
			array_push($trail, $expression);
		}
		else {
			// Add the collection in reverse order.
			$i = count($expression);
			while ($i >= 1) {
				$i--;
				array_push($trail, $expression[$i]);
			}	
		}
	}
	
	/**
	 * Pop an Expression object from the trail stack.
	 * 
	 * @return Expression $expression The Expression object at the top of the trail stack.
	 */
	protected function popTrail() {
		$trail = &$this->getTrail();
		return array_pop($trail);
	}
	
	/**
	 * Get a reference on the trail stack.
	 * 
	 * @return array A reference on the trail stack.
	 */
	protected function &getTrail() {
		return $this->trail;
	}
	
	/**
	 * Set a reference on the trail stack.
	 * 
	 * @param array $trail A reference on an array that will be used as the trail stack.
	 */
	protected function setTrail(array &$trail) {
		$this->trail = $trail;
	}
	
	/**
	 * Get a reference on the marker array.
	 * 
	 * @return array A reference on the marker array.
	 */
	protected function &getMarker() {
		return $this->marker;
	}
	
	/**
	 * Set a reference on the marker array.
	 * 
	 * @param array $marker
	 */
	protected function setMarker(array &$marker) {
		$this->marker = $marker;
	}
	
	/**
	 * Mark a given $expression object as explored.
	 * 
	 * @param Expression $expression An explored Expression object.
	 */
	protected function mark(Expression $expression) {
		$marker = &$this->getMarker();
		array_push($marker, $expression);
	}
	
	/**
	 * Whether a given $expression object is already marked as explored.
	 * 
	 * @param Expression $expression An Expression object.
	 * @return boolean Whether $expression is marked as explored.
	 */
	protected function isMarked(Expression $expression) {
		$marker = &$this->getMarker();
		return in_array($expression, $marker, true);
	}
	
	/**
	 * Process the current Expression object according to the current
	 * execution context.
	 * 
	 * @throws ExpressionProcessingException|OperatorProcessingException If an error occurs during the Expression processing.
	 */
	public function process() {
		$expression = $this->getExpression();
		
		// Reset trail and marker arrays.
		$trail = array();
		$this->setTrail($trail);
		$marker = array();
		$this->setMarker($marker);
		
		$this->pushTrail($expression);
		
		while (count($this->getTrail()) > 0) {
			
			$expression = $this->popTrail();
			
			if ($this->isMarked($expression) === false && $expression instanceof Operator) {
				// This is an operator, first pass. Repush for a second pass.
				$this->mark($expression);
				$this->pushTrail($expression);
				$this->pushTrail($expression->getExpressions());
			}
			else if ($this->isMarked($expression)) {
				// Operator, second pass. Process it.
				$factory = $this->getOperatorProcessorFactory();
				$popCount = count($expression->getExpressions());
				$operands = $this->getOperands()->pop($popCount);
				$processor = $factory->createProcessor($expression, $operands);
				$processor->setState($this->getContext());
				$result = $processor->process();
				
				// trace the processing of the operator.
				$qtiName = $expression->getQtiClassName();
				$trace = "Operator '${qtiName}' processed.";
				$this->trace($expression, $trace);
				
				if ($expression !== $this->getExpression()) {
					$this->getOperands()->push($result);
				}
			}
			else {
				// Simple expression, process it.
				$factory = $this->getExpressionProcessorFactory();
				$processor = $factory->createProcessor($expression);
				$processor->setState($this->getContext());
				
				$result = $processor->process();
				$this->getOperands()->push($result);
				
				// trace the processing of the expression.
				$qtiName = $expression->getQtiClassName();
				$trace = "Expression '${qtiName}' processed.";
				$this->trace($expression, $trace);
			}
		}
		
		return $result;
	}
}