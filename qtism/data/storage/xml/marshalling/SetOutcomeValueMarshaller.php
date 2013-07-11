<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\rules\SetOutcomeValue;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for setOutcomeValue.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SetOutcomeValueMarshaller extends Marshaller {
	
	/**
	 * Marshall a SetOutcomeValue object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A SetOutcomeValue object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		$marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
		$element->appendChild($marshaller->marshall($component->getExpression()));
		
		static::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI SetOutcomeValue element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A SetOutcomeValue object.
	 * @throws UnmarshallingException If the mandatory expression child element is missing from $element or if the 'target' element is missing.
	 */
	protected function unmarshall(DOMElement $element) {
		
		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier')) !== null) {
			$expressionElt = self::getFirstChildElement($element);
				
			if ($expressionElt !== false) {
				$marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElt);
				$object = new SetOutcomeValue($identifier, $marshaller->unmarshall($expressionElt));
				return $object;
			}
			else {
				$msg = "The mandatory child element 'expression' is missing from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'identifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'setOutcomeValue';
	}
}