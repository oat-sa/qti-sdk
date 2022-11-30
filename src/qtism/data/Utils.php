<?php

declare(strict_types=1);

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

/**
 * Class Utils
 */
class Utils
{
    /**
     * Checks if the current section has a parent, and returns it, if any.
     *
     * Gets the list of sections of this AssessmentTest, and checks if any has the AssessmentSection
     * set as parameter in its components. If some has, we keep the last section found (to take the
     * closest parent).
     *
     * @param AssessmentSection $component The section from which we want to find the parent.
     * @param AssessmentSectionCollection $sections The collection of all sections in this AssessmentTest.
     * @return AssessmentSection|null The parent of the AssessmentSection set as parameter, if any.
     */
    private static function checkRecursion($component, $sections): ?AssessmentSection
    {
        $sectparent = null;

        foreach ($sections as $key => $sect) {
            if (in_array($component, $sect->getSectionParts()->getArrayCopy())) {
                $sectparent = $sect;
            }

            if ($sect->getIdentifier() == $component->getIdentifier()) {
                break;
            }
        }

        return $sectparent;
    }

    /**
     * Returns the first AssessmentItem that will be prompted if a branch targets the
     * QtiComponent set as parameter.
     *
     * This method depends heavily of the QtiClass of the QtiComponent. Some require multiples loops where we need to
     * find the firstItem from another QtiComponent : this is then done iteratively.
     *
     * @param AssessmentTest $test The AssessmentTest where we are searching the item from where the branch
     * comes.
     * @param QtiComponent $component The QtiComponent targeted by a branch.
     * @param AssessmentSectionCollection $sections The collection of all sections in this AssessmentTest.
     * @return AssessmentItem|null The first AssessmentItem that will be prompted if a branch targets the
     * QtiComponent set as parameter. Returns null, if there are no more AssessmentItem because the end of the test
     * has been reached.
     */
    #[\ReturnTypeWillChange]
    public static function getFirstItem($test, $component, $sections)
    {
        $currentCmp = $component;
        $visitedNodes = [];

        while (true) {
            $visitedNodes[] = $currentCmp->getIdentifier();

            switch ($currentCmp->getQtiClassName()) {
                case 'assessmentItemRef':
                    return $currentCmp;
                    break;

                case 'assessmentSection':
                    $items = $currentCmp->getComponentsByClassName('assessmentItemRef')->getArrayCopy();

                    if (count($items) == 0) {
                        // Check for recursion

                        $sectparent = self::checkRecursion($currentCmp, $sections);

                        if ($sectparent != null) {
                            $nextSectpart = null;
                            $currentFound = false;

                            foreach ($sectparent->getSectionParts() as $key => $scpt) {
                                if ($currentFound) {
                                    $nextSectpart = $scpt;
                                    break;
                                }

                                if ($scpt == $currentCmp) {
                                    $currentFound = true;
                                }
                            }

                            if ($nextSectpart == null) {  // Check end of file or at a higher level
                                $currentCmp = $sectparent;
                            } else { // Recursive part
                                $currentCmp = $nextSectpart;
                            }
                        } else { // No recursion
                            $nextSect = null;
                            $keyFound = null;

                            foreach ($sections as $sect) {
                                if (($keyFound) and (!in_array($sect->getIdentifier(), $visitedNodes))) {
                                    $nextSect = $sect;
                                    break;
                                }

                                if ($sect->getIdentifier() == $currentCmp->getIdentifier()) {
                                    $keyFound = true;
                                }
                            }

                            if ($nextSect == null) {
                                return null;
                            } else {
                                $currentCmp = $nextSect;
                            }
                        }
                    } else {
                        return $items[0];
                    }
                    break;

                case 'testPart':
                    $items = $currentCmp->getComponentsByClassName('assessmentItemRef')->getArrayCopy();

                    if (count($items) == 0) {
                        // First item of the next testpart

                        $nextTest = null;
                        $keyFound = null;

                        foreach ($test->getComponentsByClassName($currentCmp->getQtiClassName()) as $test) {
                            if ($keyFound) {
                                $nextTest = $test;
                                break;
                            }

                            if ($test->getIdentifier() == $currentCmp->getIdentifier()) {
                                $keyFound = true;
                            }
                        }

                        if ($nextTest != null) {
                            $currentCmp = $nextTest;
                        } else {
                            return null;
                        }
                    } else {
                        return $items[0];
                    }
                    break;

                default:
                    return null;
            }
        }
    }

    /**
     * Returns the last AssessmentItem that will be prompted before a BranchRule of the QtiComponent set as parameter
     * will be taken.
     *
     * This method depends heavily of the QtiClass of the QtiComponent. Some require multiples loops where we need to
     * find the lastItem from another QtiComponent : this is then done iteratively.
     *
     * @param AssessmentTest $test The AssessmentTest where we are searching the item from where the branch
     * starts.
     * @param QtiComponent $component The QtiComponent with a BranchRule.
     * @param AssessmentSectionCollection $sections The collection of all sections in this AssessmentTest.
     * @return AssessmentItem|null The last AssessmentItem that will be prompted before taking a BranchRule
     * in the QtiComponent set as parameter. Returns null, if there are no more AssessmentItem because the begin of the
     * test has been reached.
     */
    #[\ReturnTypeWillChange]
    public static function getLastItem($test, $component, $sections)
    {
        $currentCmp = $component;
        // $sections = null;

        while (true) {
            switch ($currentCmp->getQtiClassName()) {
                case 'assessmentItemRef':
                    return $currentCmp;
                    break;

                case 'assessmentSection':
                    $items = $currentCmp->getComponentsByClassName('assessmentItemRef')->getArrayCopy();

                    if (count($items) == 0) {
                        // Check for recursion

                        $sectparent = self::checkRecursion($currentCmp, $sections);

                        if ($sectparent != null) {
                            $prevSectPart = null;

                            foreach ($sectparent->getSectionParts() as $key => $scpt) {
                                if ($scpt == $currentCmp) {
                                    break;
                                }

                                $prevSectPart = $scpt;
                            }

                            if ($prevSectPart == null) {
                                $currentCmp = $sectparent;
                            } else {
                                $currentCmp = $prevSectPart;
                            }
                        } else {
                            // No recursion
                            $prevSect = null;
                            $keyFound = null;

                            foreach ($sections as $sect) {
                                if ($sect->getIdentifier() == $currentCmp->getIdentifier()) {
                                    break;
                                } else {
                                    $prevSect = $sect;
                                }
                            }

                            if ($prevSect == null) {
                                return null;
                            } else {
                                $currentCmp = $prevSect;
                            }
                        }
                    } else { // Case with sub items
                        return $items[count($items) - 1];
                    }
                    break;

                case 'testPart':
                    $items = $currentCmp->getComponentsByClassName('assessmentItemRef')->getArrayCopy();

                    if (count($items) == 0) {
                        // First item of the next testpart

                        $prevTest = null;
                        $keyFound = null;

                        foreach ($test->getComponentsByClassName($currentCmp->getQtiClassName()) as $test) {
                            if ($test->getIdentifier() == $currentCmp->getIdentifier()) {
                                break;
                            } else {
                                $prevTest = $test;
                            }
                        }

                        if ($prevTest != null) {
                            $currentCmp = $prevTest;
                        } else {
                            return null;
                        }
                    } else {
                        return $items[count($items) - 1];
                    }
                    break;

                default:
                    return null;
            }
        }
    }
}
