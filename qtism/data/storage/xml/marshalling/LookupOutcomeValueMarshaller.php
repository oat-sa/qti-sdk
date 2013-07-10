<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\rules\LookupOutcomeValue;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for LookupOutcomeValue.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class LookupOutcomeValueMarshaller extends Marshaller {
	
	/**
	 * Marshall a LookupOutcomeValue object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A LookupOutcomeValue object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
		$element->appendChild($marshaller->marshall($component->getExpression()));
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI lookupOutcomeValue element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A LookupOutcomeValue object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshall(DOMElement $element) {
		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier')) !== null) {
			
			$expressionElt = self::getFirstChildElement($element);
			if ($expressionElt !== false) {
				$marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElt);
				$expression = $marshaller->unmarshall($expressionElt);
				
				$object = new LookupOutcomeValue($identifier, $expression);
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
	
	public function getExpectedQTIClassName() {
		return 'lookupOutcomeValue';
	}
}