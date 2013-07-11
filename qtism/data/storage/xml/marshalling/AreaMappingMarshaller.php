<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\state\AreaMapping;
use qtism\data\state\AreaMapEntry;
use qtism\data\state\AreaMapEntryCollection;
use \DOMElement;
use \Exception;
use \InvalidArgumentException;

/**
 * Marshalling/Unmarshalling implementation for AreaMapping.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AreaMappingMarshaller extends Marshaller {
	
	/**
	 * Marshall an AreaMapping object into a DOMElement object.
	 * 
	 * @param QtiComponent $component An AreaMapping object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'defaultValue', $component->getDefaultValue());
		
		if ($component->hasLowerBound() === true) {
			self::setDOMElementAttribute($element, 'lowerBound', $component->getLowerBound());
		}
		
		if ($component->hasUpperBound() === true) {
			self::setDOMElementAttribute($element, 'upperBound', $component->getUpperBound());
		}
		
		foreach ($component->getAreaMapEntries() as $entry) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($entry);
			
			$element->appendChild($marshaller->marshall($entry));
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI areaMapping element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent An AreaMapping object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshall(DOMElement $element) {
		
		$areaMapEntries = new AreaMapEntryCollection();
		$areaMapEntryElts = static::getChildElementsByTagName($element, 'areaMapEntry');
		
		foreach ($areaMapEntryElts as $areaMapEntryElt) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($areaMapEntryElt);
			$areaMapEntries[] = $marshaller->unmarshall($areaMapEntryElt);
		}
		
		$object = new AreaMapping($areaMapEntries);
		
		if (($defaultValue = static::getDOMElementAttributeAs($element, 'defaultValue', 'float')) !== null) {
			$object->setDefaultValue($defaultValue);
		}
		
		if (($lowerBound = static::getDOMElementAttributeAs($element, 'lowerBound', 'float')) !== null) {
			$object->setLowerBound($lowerBound);
		}
		
		if (($upperBound = static::getDOMElementAttributeAs($element, 'upperBound', 'float')) !== null) {
			$object->setUpperBound($upperBound);
		}
		
		return $object;
	}
	
	public function getExpectedQtiClassName() {
		return 'areaMapping';
	}
}