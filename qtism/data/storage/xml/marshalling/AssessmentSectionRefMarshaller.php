<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\AssessmentSectionRef;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for assessmentSectionRef.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentSectionRefMarshaller extends SectionPartMarshaller {
	
	/**
	 * Marshall an AssessmentSectionRef object into a DOMElement object.
	 * 
	 * @param QtiComponent $component An AssessmentSectionRef object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		
		self::setDOMElementAttribute($element, 'href', $component->getHref());
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI assessmentSectionRef element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent An AssessmentSectionRef object.
	 * @throws UnmarshallingException If the mandatory attribute 'href' is missing.
	 */
	protected function unmarshall(DOMElement $element) {
		
		$baseComponent = parent::unmarshall($element);
		
		if (($href = static::getDOMElementAttributeAs($element, 'href', 'string')) !== null) {
			$object = new AssessmentSectionRef($baseComponent->getIdentifier(), $href);
			$object->setRequired($baseComponent->isRequired());
			$object->setFixed($baseComponent->isFixed());
			$object->setPreConditions($baseComponent->getPreConditions());
			$object->setBranchRules($baseComponent->getBranchRules());
			$object->setItemSessionControl($baseComponent->getItemSessionControl());
			$object->setTimeLimits($baseComponent->getTimeLimits());
			
			return $object;
		}
		else {
			$msg = "Mandatory attribute 'href' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQTIClassName() {
		return 'assessmentSectionRef';
	}
}