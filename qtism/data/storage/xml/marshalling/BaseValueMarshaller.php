<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\BaseValue;
use qtism\common\enums\BaseType;
use qtism\data\storage\Utils;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for BaseValue.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BaseValueMarshaller extends Marshaller {
	
	/**
	 * Marshall a BaseValue object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A BaseValue object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'baseType', BaseType::getNameByConstant($component->getBaseType()));
		self::setDOMElementValue($element, $component->getValue());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI baseValue element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A BaseValue object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshall(DOMElement $element) {

		if (($baseType = static::getDOMElementAttributeAs($element, 'baseType', 'string')) !== null) {
			
			$value = $element->nodeValue;
			$baseTypeCst = BaseType::getConstantByName($baseType);
			$object = new BaseValue($baseTypeCst, Utils::stringToDatatype($value, $baseTypeCst));
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'baseType' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'baseValue';
	}
}