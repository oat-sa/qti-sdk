<?php

namespace qtism\runtime\expressions\processing;

use qtism\data\expressions\operators\RoundingMode;
use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\RoundTo;
use \InvalidArgumentException;

/**
 * The RoundToProcessor class aims at processing QTI Data Model RoundTo Operator objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RoundToProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof RoundTo) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The RoundToProcessor class only accepts RoundTo Operator objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the RoundTo operator.
	 * 
	 * An ExpressionProcessingException will be thrown if:
	 * 
	 * * The given operand is not a numeric value.
	 * * The cardinality of the operand is not single.
	 * * The value of the 'figures' attribute comes from a templateVariable which does not exist or is not numeric or null.
	 * 
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		$state = $this->getState();
		$operand = $operands[0];
			
		// If the value is null, return null.
		if ($operands->containsNull()) {
			return null;
		}
		
		if (!$operands->exclusivelySingle()) {
			$msg = "The RoundTo operator accepts 1 operand with single cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		// Accept only numerical operands.
		if (!$operands->exclusivelyNumeric()) {
			$msg = "The RoundTo operand accepts 1 operand with numerical baseType, '" . gettype($operand) . "' given.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		// As per QTI 2.1 spec...
		if (is_nan($operand)) {
			return null;
		}
		else if (is_infinite($operand)) {
			return $operand;
		}
		
		$roundingMode = $this->getExpression()->getRoundingMode();
		$figures = $this->getExpression()->getFigures();
		
		if (is_string($figures)) {
			// try to recover the value from the state.
			$figuresIdentifier = Utils::sanitizeVariableRef($figures);
			$figures = $state[$figuresIdentifier];
			
			if (is_null($figures) || !is_int($figures)) {
				$msg = "The variable '${figuresIdentifier}' used to set up the 'figures' attribute is null or not an integer value.";
				throw new ExpressionProcessingException($msg, $this);
			}
		}
		
		if ($roundingMode === RoundingMode::SIGNIFICANT_FIGURES) {
			
			if ($figures <= 0) {
				// As per QTI 2.1 spec.
				$msg = "The 'figures' attribute must be a non-zero positive integer when mode 'significantFigures' is used, '${figures}' given.";
				throw new ExpressionProcessingException($msg, $this);
			}
			
			if ($operand == 0) {
				return 0.0;
			}
			
			$d = ceil(log10($operand < 0 ? -$operand : $operand));
			$power = $figures - intval($d);
			
			$magnitude = pow(10, $power);
			$shifted = round($operand * $magnitude);
			return floatval($shifted / $magnitude);
		}
		else {
			
			// As per QTI 2.1 spec.
			if ($figures < 0) {
				$msg = "The 'figures' attribute must be a integer greater than or equal to zero when mode 'decimalPlaces' is used, '${figures}' given.";
				throw new ExpressionProcessingException($msg, $this);
			}
			
			return round($operand, $figures);
		}
	}
}