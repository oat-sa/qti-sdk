<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\FieldValue;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of fieldValue QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class FieldValueMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall an FieldValue object into a QTI fieldValue element.
	 * 
	 * @param QtiComponent The FieldValue object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI fieldValue element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		self::setDOMElementAttribute($element, 'fieldIdentifier', $component->getFieldIdentifier());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI fieldValue operator element into an FieldValue object.
	 *
	 * @param DOMElement The fieldValue element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent An FieldValue object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($fieldIdentifier = static::getDOMElementAttributeAs($element, 'fieldIdentifier')) !== null) {
			
			$object = new FieldValue($children, $fieldIdentifier);
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'fieldIdentifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}