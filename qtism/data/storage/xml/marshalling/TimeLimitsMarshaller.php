<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\TimeLimits;
use \DOMElement;
use \InvalidArgumentException;

/**
 * Marshalling/Unmarshalling implementation for timeLimits.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TimeLimitsMarshaller extends Marshaller {
	
	/**
	 * Marshall a TimeLimits object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A TimeLimits object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		if ($component->hasMinTime() === true) {
			self::setDOMElementAttribute($element, 'minTime', $component->getMinTime());
		}
		
		if ($component->hasMaxTime() === true) {
			self::setDOMElementAttribute($element, 'maxTime', $component->getMaxTime());
		}
		
		self::setDOMElementAttribute($element, 'allowLateSubmission', $component->doesAllowLateSubmission());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI timeLimits element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A TimeLimits object.
	 * @throws UnmarshallingException If the attribute 'allowLateSubmission' is not a valid boolean value.
	 */
	protected function unmarshall(DOMElement $element) {
		
		$object = new TimeLimits();
		
		if (($value = static::getDOMElementAttributeAs($element, 'minTime', 'integer')) !== null) {
			$object->setMinTime($value);
		}
		
		if (($value = static::getDOMElementAttributeAs($element, 'maxTime', 'integer')) !== null) {
			$object->setMaxTime($value);
		}
		
		if (($value = static::getDOMElementAttributeAs($element, 'allowLateSubmission', 'boolean')) !== null) {
			$object->setAllowLateSubmission($value);
		}
		
		return $object;
	}
	
	public function getExpectedQtiClassName() {
		return 'timeLimits';
	}
}