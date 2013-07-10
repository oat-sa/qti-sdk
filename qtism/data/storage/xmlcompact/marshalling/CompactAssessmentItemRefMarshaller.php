<?php

namespace qtism\data\storage\xmlcompact\marshalling;

use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\storage\xmlcompact\data\CompactAssessmentItemRef;
use qtism\data\storage\xml\marshalling\AssessmentItemRefMarshaller;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * A Marshaller aiming at marshalling/unmarshalling CompactAssessmentItemRefs.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class CompactAssessmentItemRefMarshaller extends AssessmentItemRefMarshaller {
	
	/**
	 * Marshall a CompactAssessmentItemRef object into its DOMElement representation.
	 * 
	 * @return DOMElement The according DOMElement object.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		
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
	 * Unmarshall an extended version of an assessmentItemRef DOMElement into 
	 * a CompactAssessmentItemRef object.
	 * 
	 * @return CompactAssessmentItemRef A CompactAssessmentItemRef object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		$identifier = $baseComponent->getIdentifier();
		$href = $baseComponent->getHref();
		
		$compactAssessmentItemRef = new CompactAssessmentItemRef($identifier, $href);
		$compactAssessmentItemRef->setRequired($baseComponent->isRequired());
		$compactAssessmentItemRef->setFixed($baseComponent->isFixed());
		$compactAssessmentItemRef->setPreConditions($baseComponent->getPreConditions());
		$compactAssessmentItemRef->setBranchRules($baseComponent->getBranchRules());
		$compactAssessmentItemRef->setItemSessionControl($baseComponent->getItemSessionControl());
		$compactAssessmentItemRef->setTimeLimits($baseComponent->getTimeLimits());
		$compactAssessmentItemRef->setTemplateDefaults($baseComponent->getTemplateDefaults());
		$compactAssessmentItemRef->setWeights($baseComponent->getWeights());
		$compactAssessmentItemRef->setVariableMappings($baseComponent->getVariableMappings());
		
		$responseDeclarationElts = self::getChildElementsByTagName($element, 'responseDeclaration');
		$responseDeclarations = new ResponseDeclarationCollection();
		foreach ($responseDeclarationElts as $responseDeclarationElt) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($responseDeclarationElt);
			$responseDeclarations[] = $marshaller->unmarshall($responseDeclarationElt);
		}
		$compactAssessmentItemRef->setResponseDeclarations($responseDeclarations);
		
		$outcomeDeclarationElts = self::getChildElementsByTagName($element, 'outcomeDeclaration');
		$outcomeDeclarations = new OutcomeDeclarationCollection();
		foreach ($outcomeDeclarationElts as $outcomeDeclarationElt) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclarationElt);
			$outcomeDeclarations[] = $marshaller->unmarshall($outcomeDeclarationElt);
		}
		$compactAssessmentItemRef->setOutcomeDeclarations($outcomeDeclarations);
		
		return $compactAssessmentItemRef;
	}
}