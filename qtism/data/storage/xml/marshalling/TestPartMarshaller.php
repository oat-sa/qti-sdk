<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\TestPart;
use qtism\data\TestFeedbackCollection;
use qtism\data\ItemSessionControl;
use qtism\data\AssessmentSectionCollection;
use qtism\data\rules\PreConditionCollection;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\TimeLimits;
use qtism\data\NavigationMode;
use qtism\data\SubmissionMode;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for TestPart.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TestPartMarshaller extends Marshaller {
	
	protected function marshall(QtiComponent $component) {
		$element = static::getDOMCradle()->createElement($component->getQTIClassName());
		
		self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
		self::setDOMElementAttribute($element, 'navigationMode', NavigationMode::getNameByConstant($component->getNavigationMode()));
		self::setDOMElementAttribute($element, 'submissionMode', SubmissionMode::getNameByConstant($component->getSubmissionMode()));
		
		foreach ($component->getPreConditions() as $preCondition) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($preCondition);
			$element->appendChild($marshaller->marshall($preCondition));
		}
		
		foreach ($component->getBranchRules() as $branchRule) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($branchRule);
			$element->appendChild($marshaller->marshall($branchRule));
		}
		
		$itemSessionControl = $component->getItemSessionControl();
		if (!empty($itemSessionControl)) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($itemSessionControl);
			$element->appendChild($marshaller->marshall($itemSessionControl));
		}
		
		$timeLimits = $component->getTimeLimits();
		if (!empty($timeLimits)) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($timeLimits);
			$element->appendChild($marshaller->marshall($timeLimits));
		}
		
		foreach ($component->getAssessmentSections() as $assessmentSection) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($assessmentSection);
			$element->appendChild($marshaller->marshall($assessmentSection));
		}
		
		foreach ($component->getTestFeedbacks() as $testFeedback) {
			$marshaller = $this->getMarshallerFactory()->createMarshaller($testFeedback);
			$element->appendChild($marshaller->marshall($testFeedback));
		}
		
		return $element;
	}
	
	protected function unmarshall(DOMElement $element) {
		
		if (($identifier = static::getDOMElementAttributeAs($element, 'identifier')) !== null) {
			
			if (($navigationMode = static::getDOMElementAttributeAs($element, 'navigationMode')) !== null) {
				
				if (($submissionMode = static::getDOMElementAttributeAs($element, 'submissionMode')) !== null) {
					
					// We do not use the regular DOMElement::getElementsByTagName method
					// because it is recursive. We only want the first level elements with
					// tagname = 'assessmentSection'.
					$assessmentSectionElts = self::getChildElementsByTagName($element, 'assessmentSection');
					$assessmentSections = new AssessmentSectionCollection();
					foreach ($assessmentSectionElts as $sectElt) {
						$marshaller = $this->getMarshallerFactory()->createMarshaller($sectElt);
						$assessmentSections[] = $marshaller->unmarshall($sectElt);
					}
					
					if (count($assessmentSections) > 0) {
						// We can instantiate because all mandatory attributes/elements were found.
						$navigationMode = NavigationMode::getConstantByName($navigationMode);
						$submissionMode = SubmissionMode::getConstantByName($submissionMode);
						$object = new TestPart($identifier, $assessmentSections, $navigationMode, $submissionMode);
						
						$testFeedbackElements = $element->getElementsByTagName('testFeedback');
						$testFeedbacks = new TestFeedbackCollection();
						for ($i = 0; $i < $testFeedbackElements->length; $i++) {
							$marshaller = $this->getMarshallerFactory()->createMarshaller($testFeedbackElements->item($i));
							$testFeedbacks[] = $marshaller->marshall($testFeedbackElements->item($i));
						}
						$object->setTestFeedbacks($testFeedbacks);
						
						return $object;
					}
					else {
						$msg = "A testPart element must contain at least one assessmentSection.";
						throw new UnmarshallingException($msg, $element);
					}
				}
				else {
					$msg = "The mandatory attribute 'submissionMode' is missing from element '" . $element->nodeName . "'.";
					throw new UnmarshallingException($msg, $element);
				}
			}
			else {
				$msg = "The mandatory attribute 'navigationMode' is missing from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'identifier' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
	
	public function getExpectedQTIClassName() {
		return 'testPart';
	}
}