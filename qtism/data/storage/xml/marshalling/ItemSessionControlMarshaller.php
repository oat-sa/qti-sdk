<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\ItemSessionControl;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for itemSessionControl.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ItemSessionControlMarshaller extends Marshaller {
	
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());

		// If max attempts <= 0, it means it was not specified.
		if ($component->getMaxAttempts() > 0) {
			static::setDOMElementAttribute($element, 'maxAttempts', $component->getMaxAttempts());
		}
		
		static::setDOMElementAttribute($element, 'showFeedback', $component->mustShowFeedback());
		static::setDOMElementAttribute($element, 'allowReview', $component->doesAllowReview());
		static::setDOMElementAttribute($element, 'showSolution', $component->mustShowSolution());
		static::setDOMElementAttribute($element, 'allowComment', $component->doesAllowComment());
		static::setDOMElementAttribute($element, 'allowSkipping', $component->doesAllowSkipping());
		static::setDOMElementAttribute($element, 'validateResponses', $component->mustValidateResponses());
		
		return $element;
	}
	
	protected function unmarshall(DOMElement $element) {
		
		$object = new ItemSessionControl();
		
		if (($value = static::getDOMElementAttributeAs($element, 'maxAttempts', 'integer')) !== null) {
			$object->setMaxAttempts($value);
		}
		
		if (($value = static::getDOMElementAttributeAs($element, 'showFeedback', 'boolean')) !== null) {
			$object->setShowFeedback($value);
		}
		
		if (($value = static::getDOMElementAttributeAs($element, 'allowReview', 'boolean')) !== null) {
			$object->setAllowReview($value);
		}
		
		if (($value = static::getDOMElementAttributeAs($element, 'showSolution', 'boolean')) !== null) {
			$object->setShowSolution($value);
		}
		
		if (($value = static::getDOMElementAttributeAs($element, 'allowComment', 'boolean')) !== null) {
			$object->setAllowComment($value);
		}
		
		if (($value = static::getDOMElementAttributeAs($element, 'allowSkipping', 'boolean')) !== null) {
			$object->setAllowSkipping($value);
		}
		
		if (($value = static::getDOMElementAttributeAs($element, 'validateResponses', 'boolean')) !== null) {
			$object->setValidateResponses($value);
		}
		
		return $object;
	}
	
	public function getExpectedQtiClassName() {
		return 'itemSessionControl';
	}
}