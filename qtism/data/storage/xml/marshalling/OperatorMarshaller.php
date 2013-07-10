<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Operator;
use \DOMElement;
use \ReflectionClass;

/**
 * The OperatorMarshaller class focuses on Marshaller/Unmarshalling
 * the QTI Operators (a.k.a. hierarchical expressions).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OperatorMarshaller extends RecursiveMarshaller {
	
	private static $operators = array('roundTo',
			'statsOperator',
			'max',
			'min',
			'mathOperator',
			'gcd',
			'lcm',
			'repeat',
			'multiple',
			'ordered',
			'containerSize',
			'isNull',
			'index',
			'fieldValue',
			'random',
			'member',
			'delete',
			'contains',
			'substring',
			'not',
			'and',
			'or',
			'anyN',
			'match',
			'stringMatch',
			'patternMatch',
			'equal',
			'equalRounded',
			'inside',
			'lt',
			'gt',
			'lte',
			'gte',
			'durationLT',
			'durationGTE',
			'sum',
			'product',
			'subtract',
			'divide',
			'power',
			'integerDivide',
			'integerModulus',
			'truncate',
			'round',
			'integerToFloat',
			'customOperator');
	
	/**
	 * Get the list of operator QTI class names.
	 *
	 * @return array An array of string.
	 */
	public static function getOperators() {
		return self::$operators;
	}
	
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		
		// Some exceptions applies on instanciation e.g. the And operator is named
		// AndOperator because of PHP reserved words restriction.
		
		if ($element->nodeName == 'and') {
			$className = 'qtism\\data\\expressions\\operators\\AndOperator';
		}
		else if ($element->nodeName == 'or') {
			$className = 'qtism\\data\\expressions\\operators\\OrOperator';
		}
		else {
			$className = 'qtism\\data\\expressions\\operators\\' . ucfirst($element->nodeName);
		}
		
		$class = new ReflectionClass($className);
		return $class->newInstanceArgs(array($children));
	}
	
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		
		$element = self::getDOMCradle()->createElement($component->getQTIClassName());
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	protected function isElementFinal(DOMElement $element) {
		return !in_array($element->nodeName, static::getOperators());
	}
	
	protected function isComponentFinal(QtiComponent $component) {
		return !$component instanceof Operator;
	}
	
	protected function getChildrenElements(DOMElement $element) {
		return self::getChildElements($element);
	}
	
	protected function getChildrenComponents(QtiComponent $component) {
		if ($component instanceof Operator) {
			return $component->getExpressions()->getArrayCopy();
		}
		else {
			return array();
		}
	}
	
	protected function createCollection(DOMElement $currentNode) {
		return new ExpressionCollection();
	}
	
	public function getExpectedQTIClassName() {
		return '';
	}
}