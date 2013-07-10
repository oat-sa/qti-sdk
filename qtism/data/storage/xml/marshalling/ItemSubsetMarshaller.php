<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\expressions\ItemSubset;

use qtism\data\QtiComponent;
use qtism\common\collections\IdentifierCollection;
use \DOMElement;
use \InvalidArgumentException;

class ItemSubsetMarshaller extends Marshaller {
	
	protected function marshall(QtiComponent $component) {
		$element = self::getDOMCradle()->createElement($this->getExpectedQTIClassName());
		
		$sectionIdentifier = $component->getSectionIdentifier();
		if (!empty($sectionIdentifier)) {
			self::setDOMElementAttribute($element, 'sectionIdentifier', $sectionIdentifier);	
		}
		
		$includeCategories = $component->getIncludeCategories();
		if (count($includeCategories) > 0) {
			self::setDOMElementAttribute($element, 'includeCategory', implode(' ', $includeCategories->getArrayCopy()));
		}
		
		$excludeCategories = $component->getExcludeCategories();
		if (count($excludeCategories) > 0) {
			self::setDOMElementAttribute($element, 'excludeCategory', implode(' ', $excludeCategories->getArrayCopy()));
		}
		
		return $element;
	}
	
	protected function unmarshall(DOMElement $element) {
		
		$object = new ItemSubset();
		
		if (($sectionIdentifier = static::getDOMElementAttributeAs($element, 'sectionIdentifier')) !== null) {
			$object->setSectionIdentifier($sectionIdentifier);
		}
		
		if (($includeCategories = static::getDOMElementAttributeAs($element, 'includeCategory')) !== null) {
			$includeCategories = new IdentifierCollection(explode("\x20", $includeCategories));
			$object->setIncludeCategories($includeCategories);
		}
		
		if (($excludeCategories = static::getDOMElementAttributeAs($element, 'excludeCategory')) !== null) {
			$excludeCategories = new IdentifierCollection(explode("\x20", $excludeCategories));
			$object->setExcludeCategories($excludeCategories);
		}
		
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'itemSubset';
	}
}