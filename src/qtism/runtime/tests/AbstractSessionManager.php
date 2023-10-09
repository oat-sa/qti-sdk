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
 * Copyright (c) 2013-2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\tests;

use qtism\common\datatypes\files\FileManager;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\AssessmentTest;
use qtism\data\IAssessmentItem;
use qtism\data\NavigationMode;
use qtism\data\SubmissionMode;

/**
 * The AbstractSessionManager class is a bed for instantiating
 * various implementations of AssessmentTestSession and AssessmentItemSession.
 *
 * The AbstractSessionManager constructor takes in argument a FileManager object that
 * will be used to deal with QTI file datatypes during execution.
 */
abstract class AbstractSessionManager
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * Create a new AbstractSessionManager object.
     *
     * @param FileManager $fileManager A FileManager object.
     */
    public function __construct(FileManager $fileManager)
    {
        $this->setFileManager($fileManager);
    }

    /**
     * Set the FileManager object to be used.
     *
     * @param FileManager $fileManager
     */
    public function setFileManager(FileManager $fileManager): void
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Get the FileManager object in use.
     *
     * @return FileManager
     */
    public function getFileManager(): FileManager
    {
        return $this->fileManager;
    }

    /**
     * Create a new AssessmentTestSession object.
     *
     * @param AssessmentTest $test The AssessmentTest definition.
     * @param Route $route (optional) The Route to be taken.
     * @param int $config (optional) The configuration of the AssessmentTestSession object.
     *
     * @return AssessmentTestSession An AssessmentTestSession object.
     */
    public function createAssessmentTestSession(AssessmentTest $test, Route $route = null, $config = 0): AssessmentTestSession
    {
        return $this->instantiateAssessmentTestSession($test, $this->getRoute($test, $route), $config);
    }

    /**
     * Create an AssessmentItemSession object.
     *
     * @param IAssessmentItem $assessmentItem
     * @param int $navigationMode A value from the NavigationMode enumeration.
     * @param int $submissionMode A value from the SubmissionMode enumeration $submissionMode.
     *
     * @return AssessmentItemSession
     */
    public function createAssessmentItemSession(IAssessmentItem $assessmentItem, $navigationMode = NavigationMode::LINEAR, $submissionMode = SubmissionMode::INDIVIDUAL): AssessmentItemSession
    {
        return $this->instantiateAssessmentItemSession($assessmentItem, $navigationMode, $submissionMode);
    }

    /**
     * Contains the logic of instantiating the appropriate AssessmentTestSession implementation.
     *
     * @param AssessmentTest $test
     * @param Route $route
     * @param int $config (optional) The configuration of the AssessmentTestSession object.
     * @return AssessmentTestSession A freshly instantiated AssessmentTestSession.
     */
    abstract protected function instantiateAssessmentTestSession(AssessmentTest $test, Route $route, $config = 0): AssessmentTestSession;

    /**
     * Contains the logic of instantiating the appropriate AssessmentItemSession implementation.
     *
     * @param IAssessmentItem $assessmentItem
     * @param int $navigationMode A value from the NavigationMode enumeration.
     * @param int $submissionMode A value from the SubmissionMode enumeration.
     * @return AssessmentItemSession A freshly instantiated AssessmentItemSession.
     */
    abstract protected function instantiateAssessmentItemSession(IAssessmentItem $assessmentItem, $navigationMode, $submissionMode): AssessmentItemSession;

    /**
     * Contains the Route create logic depending on whether or not
     * an optional Route to be used is given or not.
     *
     * @param AssessmentTest $test
     * @param Route $route
     * @return Route
     */
    protected function getRoute(AssessmentTest $test, Route $route = null): Route
    {
        return $route ?? $this->createRoute($test);
    }

    /**
     * Allow to recreate route items when AssessmentTest structure is changed
     *
     * @param AssessmentTest $test
     * @return Route
     */
    public function recreateRoute(AssessmentTest $test): Route
    {
        return $this->createRoute($test);
    }

    /**
     * Contains the logic of creating the Route of a brand new AssessmentTestSession object.
     * The resulting Route object will be injected in the created AssessmentTestSession.
     *
     * @param AssessmentTest $test
     * @return Route A newly instantiated Route object.
     */
    protected function createRoute(AssessmentTest $test): Route
    {
        $routeStack = [];

        foreach ($test->getTestParts() as $testPart) {
            $assessmentSectionStack = [];

            foreach ($testPart->getAssessmentSections() as $assessmentSection) {
                $trail = [];
                $mark = [];

                array_push($trail, $assessmentSection);

                while (count($trail) > 0) {
                    $current = array_pop($trail);

                    if (!in_array($current, $mark, true) && $current instanceof AssessmentSection) {
                        // 1st pass on assessmentSection.
                        $currentAssessmentSection = $current;
                        array_push($assessmentSectionStack, $currentAssessmentSection);

                        array_push($mark, $current);
                        array_push($trail, $current);

                        foreach (array_reverse($current->getSectionParts()->getArrayCopy()) as $sectionPart) {
                            array_push($trail, $sectionPart);
                        }
                    } elseif (in_array($current, $mark, true)) {
                        // 2nd pass on assessmentSection.
                        // Pop N routeItems where N is the children count of $current.
                        $poppedRoutes = [];
                        for ($i = 0; $i < count($current->getSectionParts()); $i++) {
                            $poppedRoutes[] = array_pop($routeStack);
                        }

                        $selection = new BasicSelection($current, new SelectableRouteCollection(array_reverse($poppedRoutes)));
                        $selectedRoutes = $selection->select();

                        // Shuffling can be applied on selected routes.
                        // $route will contain the final result of the selection + ordering.
                        $ordering = new BasicOrdering($current, $selectedRoutes);
                        $selectedRoutes = $ordering->order();

                        $route = new SelectableRoute($current->isFixed(), $current->isRequired(), $current->isVisible(), $current->mustKeepTogether());
                        foreach ($selectedRoutes as $r) {
                            $route->appendRoute($r);
                        }

                        // Add to the last item of the selection the branch rules of the AssessmentSection/testPart
                        // on which the selection is applied... Only if the route contains something (empty assessmentSection edge case).
                        if ($route->count() > 0) {
                            $route->getLastRouteItem()->addBranchRules($current->getBranchRules());

                            // Do the same as for branch rules for pre conditions, except that they must be
                            // attached on the first item of the route.
                            $route->getFirstRouteItem()->addPreConditions($current->getPreConditions());
                        }

                        array_push($routeStack, $route);
                        array_pop($assessmentSectionStack);
                    } elseif ($current instanceof AssessmentItemRef) {
                        // leaf node.
                        $route = new SelectableRoute($current->isFixed(), $current->isRequired());
                        $route->addRouteItem($current, new AssessmentSectionCollection($assessmentSectionStack), $testPart, $test);
                        array_push($routeStack, $route);
                    }
                }
            }
        }

        $finalRoutes = $routeStack;
        $route = new SelectableRoute();
        foreach ($finalRoutes as $finalRoute) {
            $route->appendRoute($finalRoute);
        }

        return $route;
    }
}
