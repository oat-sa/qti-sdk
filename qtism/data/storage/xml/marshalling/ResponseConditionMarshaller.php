<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\expressions\Expression;
use qtism\data\rules\SetOutcomeValue;
use qtism\data\rules\LookupOutcomeValue;
use qtism\data\rules\ExitResponse;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\ResponseRuleCollection;
use qtism\data\rules\ResponseCondition;
use qtism\data\rules\ResponseIf;
use qtism\data\rules\ResponseElseIf;
use qtism\data\rules\ResponseElseIfCollection;
use qtism\data\rules\ResponseElse;
use \DOMElement;

class ResponseConditionMarshaller extends RecursiveMarshaller {
	
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (count($children) > 0) {
			// The first element of $children must be a responseIf.
			$responseIf = $children[0];
			$responseCondition = new ResponseCondition($responseIf);
			
			if (isset($children[1])) {
				$responseElseIfs = new ResponseElseIfCollection();
				// We have at least one elseIf.
				for ($i = 1; $i < count($children) - 1; $i++) {
					$responseElseIfs[] = $children[$i];
				}
				
				$responseCondition->setResponseElseIfs($responseElseIfs);
				$lastOutcomeControl = $children[count($children) - 1];
				
				if ($lastOutcomeControl instanceof ResponseElseIf) {
					// There is no else.
					$responseElseIfs[] = $lastOutcomeControl;
				}
				else {
					// We have an else.
					$responseCondition->setResponseElse($lastOutcomeControl);
				}
			}
			
			return $responseCondition;
		}
		else {
			$msg = "A 'responseCondition' element must contain at least one 'responseIf' element. None given.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	protected function isElementFinal(DOMElement $element) {
		$exclusion = array('responseIf', 'responseElseIf', 'responseElse', 'responseCondition');
		return !in_array($element->nodeName, $exclusion);
	}
	
	protected function isComponentFinal(QtiComponent $component) {
		return (!$component instanceof ResponseIf &&
				 !$component instanceof ResponseElseIf &&
				 !$component instanceof ResponseElse &&
				 !$component instanceof ResponseCondition);
	}
	
	protected function getChildrenElements(DOMElement $element) {
		return self::getChildElementsByTagName($element, array(
				'responseIf',
				'responseElseIf',
				'responseElse',
				'exitResponse',
				'lookupOutcomeValue',
				'setOutcomeValue',
				'responseCondition'
		));
	}
	
	protected function getChildrenComponents(QtiComponent $component) {
		if ($component instanceof ResponseIf || $component instanceof ResponseElseIf || $component instanceof ResponseElse) {
			// ResponseControl
			return $component->getResponseRules()->getArrayCopy();
		}
		else {
			// ResponseCondition
			$returnValue = array($component->getResponseIf());
			
			if (count($component->getResponseElseIfs()) > 0) {
				$returnValue = array_merge($returnValue, $component->getResponseElseIfs()->getArrayCopy());
			}
			
			if ($component->getResponseElse() !== null) {
				$returnValue[] = $component->getResponseElse();
			}
			
			return $returnValue;
		}
	}
	
	protected function createCollection(DOMElement $currentNode) {
		if ($currentNode->nodeName != 'responseCondition') {
			return new ResponseRuleCollection();
		}
		else {
			return new QtiComponentCollection();
		}
		
	}
	
	public function getExpectedQtiClassName() {
		return '';
	}
}