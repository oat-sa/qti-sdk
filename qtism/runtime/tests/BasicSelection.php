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
     * @return SelectableRouteCollection A collection of SelectableRoute object describing the performed selection.
     * @throws SelectionException If the select attribute of the Selection exceeds the number of child elements but the withReplacement attribute is set to true.
     */
    public function select() {
        $assessmentSection = $this->getAssessmentSection();
        $selection = $assessmentSection->getSelection();
        $selectableRoutes = $this->getSelectableRoutes();
        
        // final result.
        $routesSelection = new SelectableRouteCollection();
        
        if (is_null($selection) === false) {
            
            $select = $selection->getSelect();
            $childCount = count($selectableRoutes);
            $withReplacement = $selection->isWithReplacement();
            
            if ($select > $childCount && $withReplacement !== true) {
                $assessmentSectionIdentifier = $assessmentSection->getIdentifier();
                $msg = "The number of children to select (${select}) cannot exceed the number ";
                $msg.= "of child elements defined (${childCount}) in assessmentSection '${assessmentSectionIdentifier}'.";
                throw new SelectionException($msg, SelectionException::LOGIC_ERROR);
            }
            
            // Map used to count the amount of selection by Route.
            $selections = new SplObjectStorage();
            $selectionsBaseIndex = new SplObjectStorage();
            // A bag where Routes will be picked up for selection.
            $selectionBag = $selectableRoutes->getArrayCopy();
            $baseSelectionBag = $selectionBag;
            $selectionBagCount = count($selectionBag);
            
            foreach ($selectionBag as $baseIndex => $selectable) {
                $selectionsBaseIndex[$selectable] = $baseIndex;
                $selections[$selectable] = 0;
                
                if ($selectable->isRequired() === true) {
                    $selections[$selectable] += 1;
                    
                    if ($withReplacement === false) {
                        unset($selectionBag[$baseIndex]);
                        $selectionBag = array_values($selectionBag);
                        $selectionBagCount--;
                        $select--;
                    }
                }
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
            
            foreach ($baseSelectionBag as $selectable) {
                // How many time is this item selected?
                if ($selections[$selectable] > 0) {
                    for ($i = 0; $i < $selections[$selectable]; $i++) {
                        $routesSelection[] = $selectable;
                    }
                }
            }
        }
        else {
            // Return the original routes as a single one.
            foreach ($selectableRoutes as $originalRoute) {
                $routesSelection[] = $originalRoute;
            }
        }
       
        
        return $routesSelection;
    }
}