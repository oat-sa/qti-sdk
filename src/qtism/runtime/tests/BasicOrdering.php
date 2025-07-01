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
 * Copyright (c) 2013-2025 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\tests;

use \qtism\runtime\rendering\markup\xhtml\Utils as Utils;

/**
 * A basic implementation of QTI ordering.
 */
class BasicOrdering extends AbstractOrdering
{
    /**
     * @return SelectableRouteCollection
     */
    public function order(): SelectableRouteCollection
    {
        if (($ordering = $this->getAssessmentSection()->getOrdering()) !== null && $ordering->getShuffle() === true) {
            // $orderedRoutes will contain the result of the ordering algorithm.
            $orderedRoutes = new SelectableRoute();
            $selectableRoutes = $this->getSelectableRoutes();
            $selectableRoutesCount = count($selectableRoutes);

            // What are the child elements that can be shuffled?
            $shufflingIndexes = [];
            for ($index = 0; $index < $selectableRoutesCount; $index++) {
                $selectableRoute = $selectableRoutes[$index];

                if ($selectableRoute->isVisible() === false && $selectableRoute->mustKeepTogether() === false) {
                    $oldIndex = $index;
                    // The RouteItems in the Route must be merged
                    // with the parent's one.
                    unset($selectableRoutes[$index]);

                    // Split the current selection in multiple selections.
                    foreach ($selectableRoute as $routeItem) {
                        $item = $routeItem->getAssessmentItemRef();
                        $newRoute = new SelectableRoute($item->isFixed(), $item->isRequired(), true, true);
                        $newRoute->addRouteItem($routeItem->getAssessmentItemRef(), $routeItem->getAssessmentSection(), $routeItem->getTestPart(), $routeItem->getAssessmentTest());
                        $selectableRoutes->insertAt($newRoute, $index);
                        $index++;
                    }

                    // reload...
                    $index = $oldIndex;
                    $selectableRoutesCount = count($selectableRoutes);
                    $selectableRoute = $selectableRoutes[$index];
                }

                if ($selectableRoute->isFixed() === false) {
                    $shufflingIndexes[] = $index;
                }
            }

            $shuffledIndexes = $shufflingIndexes;
            shuffle($shuffledIndexes);
            $map = \qtism\runtime\rendering\markup\xhtml\Utils::getSwappingMapByValues($shuffledIndexes, $shufflingIndexes);
            foreach ($map as $swapPair) {
                list($elIndex1, $elIndex2) = $swapPair;
                $selectableRoutes->swap($elIndex1, $elIndex2);
            }

            return $selectableRoutes;
        } else {
            // Simple return as it is...
            return $this->getSelectableRoutes();
        }
    }
}
