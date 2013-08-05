<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\state\OutcomeProcessing;
use qtism\data\rules\OutcomeRuleCollection;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for outcomeProcessing.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeProcessingMarshaller extends Marshaller {
	
	/**
	 * Marshall an OutcomeProcessing object into a DOMElement object.
	 * 
	 * @param QtiComponent $component An OutcomeProcessing object.
	 * @return DOMElement The according DOMElement object.
	 * @throws MarshallingException
	 */
	protected function marshall(QtiComponent $component) {
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		
		foreach ($component->getOutcomeRules() as $outcomeRule) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeRule);
			$element->appendChild($marshaller->marshall($outcomeRule));
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a QTI outcomeProcessing element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent An OutcomeProcessing object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshall(DOMElement $element) {
		$outcomeRuleElts = self::getChildElements($element);
		
		$outcomeRules = new OutcomeRuleCollection();
		for ($i = 0; $i < count($outcomeRuleElts); $i++) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeRuleElts[$i]);
			$outcomeRules[] = $marshaller->unmarshall($outcomeRuleElts[$i]);
		}
		
		$object = new OutcomeProcessing($outcomeRules);
		return $object;
	}
	
	public function getExpectedQtiClassName() {
		return 'outcomeProcessing';
	}
}