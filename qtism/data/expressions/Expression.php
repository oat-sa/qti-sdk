<?php

namespace qtism\data\expressions;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use \InvalidArgumentException;

/**
 * The base class for all QTI expressions.
 * 
 * From IMS QTI:
 * 
 * Expressions are used to assign values to item variables and to control conditional 
 * actions in response and template processing.
 * 
 * An expression can be a simple reference to the value of an itemVariable, a 
 * constant value from one of the value sets defined by baseTypes or a hierarchical 
 * expression operator. Like itemVariables, each expression can also have the special value NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class Expression extends QtiComponent {
	
	private static $expressionClassNames = array('and', 'anyN', 'baseValue', 'containerSize', 'contains', 'correct', 'customOperator', 'default', 
			'delete', 'divide', 'durationGTE', 'durationLT', 'equal', 'equalRounded', 'fieldValue', 'gcd', 'lcm', 'repeat', 'gt', 'gte', 'index', 
			'inside', 'integerDivide', 'integerModulus', 'integerToFloat', 'isNull', 'lt', 'lte', 'mapResponse', 'mapResponsePoint', 'match', 
			'mathOperator', 'mathConstant', 'max', 'min', 'member', 'multiple', 'not', 'null', 'numberCorrect', 'numberIncorrect', 'numberPresented', 
			'numberResponded', 'numberSelected', 'or', 'ordered', 'outcomeMaximum', 'outcomeMinimum', 'patternMatch', 'power', 'product', 'random', 
			'randomFloat', 'randomInteger', 'round', 'roundTo', 'statsOperator', 'stringMatch', 'substring', 'subtract', 'sum', 'testVariables', 
			'truncate', 'variable');
	
	/**
	 * Returns an array of string which are all the class names that 
	 * are sub classes of the 'expression' QTI class.
	 * 
	 * @return array An array of string values.
	 */
	public static function getExpressionClassNames() {
		return self::$expressionClassNames;
	}
	
	public function getComponents() {
		return new QtiComponentCollection();
	}
}