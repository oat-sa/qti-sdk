<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\common\enums\BaseType;
use qtism\data\expressions\TestVariables;
use \DOMElement;

/**
 * A marshalling/unmarshalling implementation for the QTI TesVariable expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TestVariablesMarshaller extends ItemSubsetMarshaller {
	
	/**
	 * Marshall a TestVariable object in its DOMElement equivalent.
	 * 
	 * @param QtiComponent A TestVariable object.
	 * @return DOMElement The corresponding testVariable QTI element.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		
		self::setDOMElementAttribute($element, 'variableIdentifier', $component->getVariableIdentifier());
		
		$baseType = $component->getBaseType();
		if ($baseType != -1) {
			self::setDOMElementAttribute($element, 'baseType', BaseType::getNameByConstant($baseType));
		}
		
		$weightIdentifier = $component->getWeightIdentifier();
		if (!empty($weightIdentifier)) {
			self::setDOMElementAttribute($element, 'weightIdentifier', $weightIdentifier);
		}
		
		return $element;
	}
	
	/**
	 * Marshall a testVariable QTI element in its TestVariable object equivalent.
	 * 
	 * @param DOMElement A DOMElement object.
	 * @return QtiComponent The corresponding TestVariable object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		
		if (($variableIdentifier = static::getDOMElementAttributeAs($element, 'variableIdentifier')) !== null) {
			$object = new TestVariables($variableIdentifier);
			$object->setSectionIdentifier($baseComponent->getSectionIdentifier());
			$object->setIncludeCategories($baseComponent->getIncludeCategories());
			$object->setExcludeCategories($baseComponent->getExcludeCategories());
			
			if (($baseType = static::getDOMElementAttributeAs($element, 'baseType')) !== null) {
				$object->setBaseType(BaseType::getConstantByName($baseType));
			}
			
			if (($weightIdentifier = static::getDOMElementAttributeAs($element, 'weightIdentifier')) !== null) {
				$object->setWeightIdentifier($weightIdentifier);
			}
			
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'variableIdentifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQTIClassName() {
		return 'testVariables';
	}
}