<?php

namespace qtism\runtime\expressions\processing;

use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\data\expressions\Expression;
use qtism\data\expressions\MapResponsePoint;
use \InvalidArgumentException;

/**
 * The MapResponsePointProcessor class aims at processing QTI Data Model MapResponsePoint
 * Expression objects.
 * 
 * From IMS QTI:
 * 
 * This expression looks up the value of a response variable that must be of base-type point, 
 * and transforms it using the associated areaMapping. The transformation is similar to 
 * mapResponse except that the points are tested against each area in turn. When mapping 
 * containers each area can be mapped once only. For example, if the candidate identified 
 * two points that both fall in the same area then the mappedValue is still added to the 
 * calculated total just once.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MapResponsePointProcessor extends ExpressionProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof MapResponsePoint) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The MapResponsePoint processor can only process MapResponsePoint Expression objects.";
			throw new InvalidArgumentException($expression);
		}
	}
	
	/**
	 * Process the MapResponsePoint Expression.
	 * 
	 * An ExpressionProcessingException is throw if:
	 * 
	 * * The expression's identifier attribute does not point a variable in the current State object.
	 * * The targeted variable is not a ResponseVariable object.
	 * * The targeted variable has no areaMapping.
	 * * The target variable has the RECORD cardinality.
	 * 
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		
		$expr = $this->getExpression();
		$identifier = $expr->getIdentifier();
		$state = $this->getState();
		$var = $state->getVariable($identifier);
		
		if (!is_null($var)) {
			if ($var instanceof ResponseVariable) {
				$areaMapping = $var->getAreaMapping();
				
				if (!is_null($areaMapping)) {
					
					// Correct cardinality ?
					if ($var->getBaseType() === BaseType::POINT && ($var->isSingle() || $var->isMultiple())) {
						
						// We can begin!
						
						// -- Null value, nothing will match
						if ($var->isNull()) {
							return $areaMapping->getDefaultValue();
						}
						
						if ($var->isSingle()) {
							$val = new MultipleContainer(BaseType::POINT, array($state[$identifier]));
						}
						else {
							$val = $state[$identifier];
						}
						
						$result = 0;
						$mapped = array();
						
						foreach ($val as $point) {
							foreach ($areaMapping->getAreaMapEntries() as $areaMapEntry) {

								$coords = $areaMapEntry->getCoords();
								
								if (!in_array($coords, $mapped) && $coords->inside($point)) {
									$mapped[] = $coords;
									$result += $areaMapEntry->getMappedValue();
								}
							}
						}
						
						// If no relevant mapping found, return the default.
						if (count($mapped) === 0) {
							return $areaMapping->getDefaultValue();
						}
						else {
							// Check upper and lower bound.
							if ($areaMapping->hasLowerBound() && $result < $areaMapping->getLowerBound()) {
								return $areaMapping->getLowerBound();
							}
							else if ($areaMapping->hasUpperBound() && $result > $areaMapping->getUpperBound()) {
								return $areaMapping->getUpperBound();
							}
							else {
								return floatval($result);
							}
						}
					}
					else {
						if ($var->isRecord()) {
							$msg = "The MapResponsePoint expression cannot be applied to RECORD variables.";
							throw new ExpressionProcessingException($msg, $this);
						}
						else {
							$strBaseType = BaseType::getNameByConstant($var->getBaseType());
							$msg = "The MapResponsePoint expression applies only on variables with baseType 'point', baseType '${strBaseType}' given.";
							throw new ExpressionProcessingException($msg, $this);
						}
					}
				}
				else {
					$msg = "Variable with identifier '${identifier}' has no areaMapping.";
					throw new ExpressionProcessingException($msg, $this);
				}
			}
			else {
				$msg = "The variable with identifier '${identifier}' is not a ResponseVariable.";
				throw new ExpressionProcessingException($msg, $this);
			}
		}
		else {
			$msg = "No variable with identifier '${identifier}' could be found in the current State object.";
			throw new ExpressionProcessingException($msg, $this);
		}
	}
}