<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\tests;

use SplObjectStorage;

/**
 * The BasicSelection class implements the basic Selection logic described by QTI.
 */
class BasicSelection extends AbstractSelection
{
    /**
     * Select the child elements of the AssessmentSection object
     * held by the Selection object.
     *
     * @return SelectableRouteCollection A collection of SelectableRoute object describing the performed selection.
     */
    public function select()
    {
        $assessmentSection = $this->getAssessmentSection();
        $selection = $assessmentSection->getSelection();
        $selectableRoutes = $this->getSelectableRoutes();

        // final result.
        $routesSelection = new SelectableRouteCollection();

        if ($selection !== null) {
            $select = $selection->getSelect();
            $childCount = count($selectableRoutes);
            $withReplacement = $selection->isWithReplacement();

            if ($select > $childCount && $withReplacement !== true) {
                // In case of the requested selection is greater than the actual
                // selectable elements, the requested selection is lowered down
                // to number of children elements.
                $select = $childCount;
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
                        $selectionBagCount--;
                        $select--;
                    }
                }
            }
            // reset indexes for remaining selections
            $selectionBag = array_values($selectionBag);

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
        } else {
            // Return the original routes as a single one.
            foreach ($selectableRoutes as $originalRoute) {
                $routesSelection[] = $originalRoute;
            }
        }

        return $routesSelection;
    }
}
