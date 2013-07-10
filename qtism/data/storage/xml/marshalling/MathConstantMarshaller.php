<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\MathConstant;
use qtism\data\expressions\MathEnumeration;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for mathConstant.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MathConstantMarshaller extends Marshaller {
	
	/**
	 * Marshall a MathConstant object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A MathConstant object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'name', MathEnumeration::getNameByConstant($component->getName()));
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI mathConstant element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A MathConstant object.
	 * @throws UnmarshallingException If the mandatory attribute 'name' is missing.
	 */
	protected function unmarshall(DOMElement $element) {

		if (($name = static::getDOMElementAttributeAs($element, 'name')) !== null) {
			if (($cst = MathEnumeration::getConstantByName($name)) !== false) {
				$object = new MathConstant($cst);
				return $object;
			}
			else {
				$msg = "'${name}' is not a valid value for the attribute 'name' from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'name' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQTIClassName() {
		return 'mathConstant';
	}
}