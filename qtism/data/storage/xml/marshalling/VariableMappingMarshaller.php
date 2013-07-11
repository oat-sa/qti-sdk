<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\state\VariableMapping;
use \DOMElement;
use \InvalidArgumentException;

/**
 * Marshalling/Unmarshalling implementation for variableMapping.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class VariableMappingMarshaller extends Marshaller {
	
	/**
	 * Marshall a VariableMapping object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A VariableMapping object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'sourceIdentifier', $component->getSource());
		self::setDOMElementAttribute($element, 'targetIdentifier', $component->getTarget());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI variableMapping element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A VariableMapping object.
	 * @throws UnmarshallingException If the mandatory attributes 'sourceIdentifier' or 'targetIdentifier' are missing from $element or are invalid.
	 */
	protected function unmarshall(DOMElement $element) {
		
		if (($source = static::getDOMElementAttributeAs($element, 'sourceIdentifier', 'string')) !== null) {
			if (($target = static::getDOMElementAttributeAs($element, 'targetIdentifier', 'string')) !== null) {
				try {
					$object = new VariableMapping($source, $target);
					return $object;
				}
				catch (InvalidArgumentException $e) {
					$msg = "'targetIdentifier or/and 'sourceIdentifier' are not valid QTI Identifiers.";
					throw new UnmarshallingException($msg, $element, $e);
				}
			}
			else {
				$msg = "The mandatory attribute 'targetIdentifier' is missing from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'sourceIdentifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'variableMapping';
	}
}