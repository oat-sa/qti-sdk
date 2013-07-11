<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\common\enums\BaseType;
use qtism\data\expressions\OutcomeMinimum;
use \DOMElement;

/**
 * A marshalling/unmarshalling implementation for the QTI OutcomeMinimum expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeMinimumMarshaller extends ItemSubsetMarshaller {
	
	/**
	 * Marshall an OutcomeMinimum object in its DOMElement equivalent.
	 * 
	 * @param QtiComponent A OutcomeMinimum object.
	 * @return DOMElement The corresponding outcomeMinimum QTI element.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		self::setDOMElementAttribute($element, 'outcomeIdentifier', $component->getOutcomeIdentifier());
		
		$weightIdentifier = $component->getWeightIdentifier();
		if (!empty($weightIdentifier)) {
			self::setDOMElementAttribute($element, 'weightIdentifier', $weightIdentifier);
		}
		
		return $element;
	}
	
	/**
	 * Marshall a outcomeMinimum QTI element in its OutcomeMinimum object equivalent.
	 * 
	 * @param DOMElement A DOMElement object.
	 * @return QtiComponent The corresponding OutcomeMinimum object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		
		if (($outcomeIdentifier = static::getDOMElementAttributeAs($element, 'outcomeIdentifier')) !== null) {
			$object = new OutcomeMinimum($outcomeIdentifier);
			$object->setSectionIdentifier($baseComponent->getSectionIdentifier());
			$object->setIncludeCategories($baseComponent->getIncludeCategories());
			$object->setExcludeCategories($baseComponent->getExcludeCategories());
			
			if (($weightIdentifier = static::getDOMElementAttributeAs($element, 'weightIdentifier')) !== null) {
				$object->setWeightIdentifier($weightIdentifier);
			}
			
			return $object;
		}
		else {
			$msg = "The mandatory attribute 'outcomeIdentifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'outcomeMinimum';
	}
}