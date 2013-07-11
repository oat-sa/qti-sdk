<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\QtiComponent;
use qtism\data\AssessmentItem;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for AssessmentItem.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentItemMarshaller extends Marshaller {
	
	/**
	 * Marshall an AssessmentItem object into a DOMElement object.
	 * 
	 * @param QtiComponent $component An AssessmentItem object.
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQtiClassName());
		
		self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		self::setDOMElementAttribute($element, 'timeDependent', $component->isTimeDependent());
		self::setDOMElementAttribute($element, 'adaptive', $component->isAdaptive());
		
		if ($component->hasLang() === true) {
			self::setDOMElementAttribute($element, 'lang', $component->getLang());
		}
		
		foreach ($component->getResponseDeclarations() as $responseDeclaration) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($responseDeclaration);
			$element->appendChild($marshaller->marshall($responseDeclaration));
		}
		
		foreach ($component->getOutcomeDeclarations() as $outcomeDeclaration) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclaration);
			$element->appendChild($marshaller->marshall($outcomeDeclaration));
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI assessmentItem element.
	 * 
	 * If $assessmentItem is provided, it will be used as the unmarshalled component instead of creating
	 * a new one.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @param AssessmentItem $assessmentItem An optional AssessmentItem object to be decorated.
	 * @return QtiComponent An AssessmentItem object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshall(DOMElement $element, AssessmentItem $assessmentItem = null) {

		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier')) !== null) {
			
			if (($timeDependent = static::getDOMElementAttributeAs($element, 'timeDependent', 'boolean')) !== null) {
				
				if (empty($assessmentItem)) {
					$object = new AssessmentItem($identifier, $timeDependent);
				}
				else {
					$object = $assessmentItem;
					$object->setIdentifier($identifier);
					$object->setTimeDependent($timeDependent);
				}
				
				
				if (($lang = static::getDOMElementAttributeAs($element, 'lang')) !== null) {
					$object->setLang($lang);
				}
				
				if (($adaptive = static::getDOMElementAttributeAs($element, 'adaptive', 'boolean')) !== null) {
					$object->setAdaptive($adaptive);
				}
				
				$responseDeclarationElts = static::getChildElementsByTagName($element, 'responseDeclaration');
				if (!empty($responseDeclarationElts)) {
					
					$responseDeclarations = new ResponseDeclarationCollection();
					
					foreach ($responseDeclarationElts as $responseDeclarationElt) {
						$marshaller = $this->getMarshallerFactory()->createMarshaller($responseDeclarationElt);
						$responseDeclarations[] = $marshaller->unmarshall($responseDeclarationElt);
					}

					$object->setResponseDeclarations($responseDeclarations);
				}
				
				$outcomeDeclarationElts = static::getChildElementsByTagName($element, 'outcomeDeclaration');
				if (!empty($outcomeDeclarationElts)) {
					
					$outcomeDeclarations = new OutcomeDeclarationCollection();
					
					foreach ($outcomeDeclarationElts as $outcomeDeclarationElt) {
						$marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclarationElt);
						$outcomeDeclarations[] = $marshaller->unmarshall($outcomeDeclarationElt);
					}
					
					$object->setOutcomeDeclarations($outcomeDeclarations);
				}
				
				return $object;
			}
			else {
				$msg = "The mandatory attribute 'timeDependent' is missing from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'identifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQtiClassName() {
		return 'assessmentItem';
	}
}