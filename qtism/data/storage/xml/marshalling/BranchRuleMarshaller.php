<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\rules\BranchRule;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for branchRule.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BranchRuleMarshaller extends Marshaller {
	
	/**
	 * Marshall a BranchRule object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A BranchRule object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		$marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
		$element->appendChild($marshaller->marshall($component->getExpression()));
		static::setDOMElementAttribute($element, 'target', $component->getTarget());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI branchRule element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A BranchRule object.
	 * @throws UnmarshallingException If the mandatory expression child element is missing from $element or if the 'target' element is missing.
	 */
	protected function unmarshall(DOMElement $element) {
		
		if (($target = static::getDOMElementAttributeAs($element, 'target')) !== null) {
			$expressionElt = self::getFirstChildElement($element);
			
			if ($expressionElt !== false) {
				$marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElt);
				$object = new BranchRule($marshaller->unmarshall($expressionElt), $target);
				return $object;
			}
			else {
				$msg = "The mandatory child element 'expression' is missing from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'target' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'branchRule';
	}
}