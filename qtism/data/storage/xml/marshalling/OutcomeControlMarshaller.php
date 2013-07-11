<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\expressions\Expression;
use qtism\data\rules\SetOutcomeValue;
use qtism\data\rules\LookupOutcomeValue;
use qtism\data\rules\ExitTest;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\OutcomeIf;
use qtism\data\rules\OutcomeElseIf;
use \ReflectionClass;
use \DOMElement;

class OutcomeControlMarshaller extends RecursiveMarshaller {
	
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		
		$expressionElts = self::getChildElementsByTagName($element, Expression::getExpressionClassNames());

		if (count($expressionElts) > 0) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElts[0]);
			$expression = $marshaller->unmarshall($expressionElts[0]);
		}
		else if (($element->nodeName == 'outcomeIf' || $element->nodeName == 'outcomeElseIf') && count($expressionElts) == 0) {
			$msg = "An '" . $element->nodeName . "' must contain an 'expression' element. None found at line " . $element->getLineNo() . "'.";
			throw new UnmarshallingException($msg, $element);
		}
		
		if ($element->nodeName == 'outcomeIf' || $element->nodeName == 'outcomeElseIf') {
			$className = 'qtism\\data\\rules\\' . ucfirst($element->nodeName);
			$class = new ReflectionClass($className);
			$object = $class->newInstanceArgs(array($expression, $children));
		}
		else {
			$className = 'qtism\\data\\rules\\' . ucfirst($element->nodeName);
			$class = new ReflectionClass($className);
			$object = $class->newInstanceArgs(array($children));
		}

		return $object;
	}
	
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		
		if ($component instanceof OutcomeIf || $component instanceof OutcomeElseIf) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
			$element->appendChild($marshaller->marshall($component->getExpression()));
		}
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	protected function isElementFinal(DOMElement $element) {
		return in_array($element->nodeName, array_merge(array(
					'exitTest',
					'lookupOutcomeValue',
					'setOutcomeValue'	
				)));
	}
	
	protected function isComponentFinal(QtiComponent $component) {
		return ($component instanceof ExitTest || 
				$component instanceof LookupOutcomeValue || 
				$component instanceof SetOutcomeValue);
	}
	
	protected function getChildrenElements(DOMElement $element) {
		return self::getChildElementsByTagName($element, array(
					'exitTest',
					'lookupOutcomeValue',
					'setOutcomeValue',
					'outcomeCondition'
				));
	}
	
	protected function getChildrenComponents(QtiComponent $component) {
		return $component->getOutcomeRules()->getArrayCopy();
	}
	
	protected function createCollection(DOMElement $currentNode) {
		return new OutcomeRuleCollection();
	}
	
	public function getExpectedQtiClassName() {
		return '';
	}
}