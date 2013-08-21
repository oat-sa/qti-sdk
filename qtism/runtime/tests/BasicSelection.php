<?php

namespace qtism\runtime\tests;

use qtism\data\SectionPart;
use qtism\data\AssessmentItemRef;
use qtism\data\QtiComponentIterator;
use qtism\data\SectionPartCollection;
use qtism\data\AssessmentSection;
use qtism\runtime\tests\AbstractSelection;
use \SplObjectStorage;

/**
 * The BasicSelection class implements the basic Selection logic described by QTI.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BasicSelection extends AbstractSelection {
    
    /**
     * Select the child elements of the AssessmentSection object
     * held by the Selection object.
     * 
     * If child elements are selected multiple times, they are suffixed by a QTI
     * sequence number. For instance, if assessmentItemRef 'Q01' is selected 2 times,
     * the containing AssessmentSection object will be composed of 'Q01.1' and 'Q01.2'.
     * 
     * @return AssessmentSection An AssessmentSection object on which the selection occured.
     * @throws SelectionException If the select attribute of the Selection exceeds the number of child elements but the withReplacement attribute is set to true.
     */
    public function select() {
        $assessmentSection = clone $this->getAssessmentSection();
        $selection = $assessmentSection->getSelection();
        
        if (is_null($selection) === false) {
            $sectionParts = $assessmentSection->getSectionParts();
            
            $select = $selection->getSelect();
            $childCount = count($sectionParts);
            $withReplacement = $selection->isWithReplacement();
            
            if ($select > $childCount && $withReplacement !== true) {
                $assessmentSectionIdentifier = $assessmentSection->getIdentifier();
                $msg = "The number of children to select (${select}) cannot exceed the number ";
                $msg.= "the number of child elements defined (${childCount}) in assessmentSection '${assessmentSectionIdentifier}'.";
                throw new SelectionException($msg, SelectionException::LOGIC_ERROR);
            }
            
            // Map used to count the amount of selection by sectionPart.
            $selections = new SplObjectStorage();
            $selectionsBaseIndex = new SplObjectStorage();
            // A bag where sectionParts will be picked up for selection.
            $selectionBag = $sectionParts->getArrayCopy();
            $baseSelectionBag = $selectionBag;
            $selectionBagCount = count($selectionBag);
            
            foreach ($selectionBag as $baseIndex => $selectable) {
                $selectionsBaseIndex[$selectable] = $baseIndex;
                $selections[$selectable] = 0;
            }
            
            for ($i = 0; $i < $select; $i++) {
                
                $selectedIndex = mt_rand(0, $selectionBagCount - 1);
                $selectedSectionPart = $selectionBag[$selectedIndex];
                
                $selections[$selectedSectionPart] += 1;
                
                // If no replacement allowed, remove the selected sectionPart from
                // the selection bag.
                if ($withReplacement === false) {
                    unset($selectionBag[$selectedIndex]);
                    $selectionBag = array_values($selectionBag);
                    $selectionBagCount--;
                }
            }
            
            $newSectionParts = new SectionPartCollection();
            foreach ($baseSelectionBag as $selectable) {
                // How many time is this item selected?
                $selectionCount = $selections[$selectable];
                
                if ($selectionCount === 1) {
                    // Single occurence of sectionPart.
                    $newSectionPart = clone $selectable;
                    $newSectionParts[] = $newSectionPart;
                }
                else {
                    // Multiple occurences of the same sectionPart.
                    self::multipleOccurencesSelection($newSectionParts, $selectionCount, $selectable);
                }
            }
            
            $assessmentSection->setSectionParts($newSectionParts);
        }
        
        return $assessmentSection;
    }
    
    /**
     * Instructions to be applied when the same occurence of SectionPart is selected multiple times.
     * 
     * @param SectionPartCollection $result The resulting collection of sectionParts.
     * @param integer $selectionCount The amount of occurences to be selected.
     * @param SectionPart $selectedPart The SectionPart object that has to be selected $selectionCount times.
     */
    protected static function multipleOccurencesSelection(SectionPartCollection $result, $selectionCount, SectionPart $selectedPart) {
        
        for ($i = 0; $i < $selectionCount; $i++) {
            
            $sectionPart = clone $selectedPart;
            $oldIdentifier = $sectionPart->getIdentifier();
            $sequenceNumber = $i + 1;
            $sectionPart->setIdentifier($oldIdentifier . ".${sequenceNumber}");
            $result[] = $sectionPart;
        
            if ($sectionPart instanceof AssessmentSection) {
                // Any child sequence number must be incremented by 1.
                $iterator = new QtiComponentIterator($sectionPart);
        
                while($iterator->valid()) {
        
                    $currentComponent = $iterator->current();
        
                    if ($currentComponent instanceof SectionPart) {
                        
                        // Increment the sequence number.
                        $oldIdentifier = $currentComponent->getIdentifier();
                        if (mb_strpos($oldIdentifier, '.', 0, 'UTF-8')) {
                            // Already has a sequence number.
                            
                            // explode is multi-byte safe.
                            $identifierParts = explode('.', $oldIdentifier);
                            $currentComponent->setIdentifier($identifierParts[0] . '.' . (intval($identifierParts[1]) + 1));
                        }
                        else {
                            // No sequence number yet.
                            $currentComponent->setIdentifier($oldIdentifier . ".${sequenceNumber}");
                        }
                    }
                    
                    $iterator->next();
                }
            }
        }
    }
}