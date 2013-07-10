<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\common\enums\BaseType;
use qtism\data\expressions\OutcomeMaximum;
use \DOMElement;

/**
 * A marshalling/unmarshalling implementation for the QTI OutcomeMaximum expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeMaximumMarshaller extends ItemSubsetMarshaller {
	
	/**
	 * Marshall an outcomeMaximum object in its DOMElement equivalent.
	 * 
	 * @param QtiComponent A OutcomeMaximum object.
	 * @return DOMElement The corresponding outcomeMaximum QTI element.
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
	 * Marshall an outcomeMaximum QTI element in its OutcomeMaximum object equivalent.
	 * 
	 * @param DOMElement A DOMElement object.
	 * @return QtiComponent The corresponding OutcomeMaximum object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		
		if (($outcomeIdentifier = static::getDOMElementAttributeAs($element, 'outcomeIdentifier')) !== null) {
			$object = new OutcomeMaximum($outcomeIdentifier);
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
	
	public function getExpectedQTIClassName() {
		return 'outcomeMaximum';
	}
}