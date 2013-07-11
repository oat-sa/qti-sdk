<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\rules\PreCondition;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for preCondition.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PreConditionMarshaller extends Marshaller {
	
	/**
	 * Marshall a PreCondition object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A PreCondition object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
		$element->appendChild($marshaller->marshall($component->getExpression()));
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI preCondition element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A Precondition object.
	 * @throws UnmarshallingException If $element does not contain any QTI expression element.
	 */
	protected function unmarshall(DOMElement $element) {
		
		$expressionElt = self::getFirstChildElement($element);
		
		if ($expressionElt !== false) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElt);
			$object = new PreCondition($marshaller->unmarshall($expressionElt));
			return $object;
		}
		else {
			$msg = "The mandatory 'expression' child element is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'preCondition';
	}
}