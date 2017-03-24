<?php
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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use phpDocumentor\Reflection\Types\Boolean;
use qtism\common\utils\Exception;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\processing\OutcomeProcessing;
use qtism\common\utils\Format;
use \SplObjectStorage;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 *
 * A test is a group of assessmentItems with an associated set of rules that determine
 * which of the items the candidate sees, in what order, and in what way the candidate
 * interacts with them. The rules describe the valid paths through the test, when responses
 * are submitted for response processing and when (if at all) feedback is to be given.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTest extends QtiComponent implements QtiIdentifiable
{
    use QtiIdentifiableTrait;

    /**
     * From IMS QTI:
     *
     * The principle identifier of the test. This identifier must have a corresponding
     * entry in the test's metadata. See Metadata and Usage Data for more information.
     *
     * @var string
     * @qtism-bean-property
     */
    private $identifier;

    /**
     * From IMS QTI:
     *
     * The title of an assessmentTest is intended to enable the test to be selected outside
     * of any test session. Therefore, delivery engines may reveal the title to candidates
     * at any time, but are not required to do so.
     *
     * @var string
     * @qtism-bean-property
     */
    private $title;

    /**
     * From IMS QTI:
     *
     * The tool name attribute allows the tool creating the test to identify itself.
     * Other processing systems may use this information to interpret the content of
     * application specific data, such as labels on the elements of the test rubric.
     *
     * @var string
     * @qtism-bean-property
     */
    private $toolName = '';

    /**
     * From IMS QTI:
     *
     * The tool version attribute allows the tool creating the test to identify its version. This value must only be interpreted in the context of the toolName.
     *
     * @var string
     * @qtism-bean-property
     */
    private $toolVersion = '';

    /**
     * From IMS QTI:
     *
     * Each test has an associated set of outcomes. The values of these outcomes are set by the
     * test's outcomeProcessing rules.
     *
     * @var \qtism\data\state\OutcomeDeclarationCollection
     * @qtism-bean-property
     */
    private $outcomeDeclarations;

    /**
     * From IMS QTI:
     *
     * Optionally controls the amount of time a candidate is allowed for the entire test.
     *
     * @var \qtism\data\TimeLimits
     * @qtism-bean-property
     */
    private $timeLimits = null;

    /**
     * From IMS QTI:
     *
     * Each test is divided into one or more parts which may in turn be divided into sections,
     * sub-sections and so on. A testPart represents a major division of the test and is used
     * to control the basic mode parameters that apply to all sections and sub-sections within
     * that part.
     *
     * @var \qtism\data\TestPartCollection
     * @qtism-bean-property
     */
    private $testParts;

    /**
     * From IMS QTI:
     *
     * The set of rules used for calculating the values of the test outcomes.
     *
     * @var \qtism\data\processing\OutcomeProcessing
     * @qtism-bean-property
     */
    private $outcomeProcessing = null;

    /**
     * From IMS QTI:
     *
     * Contains the test-level feedback controlled by the test outcomes.
     *
     * @var \qtism\data\TestFeedbackCollection
     * @qtism-bean-property
     */
    private $testFeedbacks;

    public function __construct($identifier, $title, TestPartCollection $testParts = null)
    {
        $this->setObservers(new SplObjectStorage());

        $this->setIdentifier($identifier);
        $this->setTitle($title);
        $this->setOutcomeDeclarations(new OutcomeDeclarationCollection());
        $this->setTestParts((empty($testParts)) ? new TestPartCollection() : $testParts);
        $this->setTestFeedbacks(new TestFeedbackCollection());
    }

    /**
     * Get the identifier of the AssessmentTest.
     *
     * @return string A QTI Identifier.
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the identifier of the AssessmentTest.
     *
     * @param string $identifier A QTI Identifier.
     * @throws \InvalidArgumentException If $identifier is not a valid QTI Identifier.
     */
    public function setIdentifier($identifier)
    {
        if (Format::isIdentifier($identifier, false)) {

            $this->identifier = $identifier;
            $this->notify();
        } else {
            $msg = "'${identifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the title of the AssessmentTest.
     *
     * @return string A title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title of the AssessmentTest.
     *
     * @param string $title A title.
     * @throws \InvalidArgumentException If $title is not a string.
     */
    public function setTitle($title)
    {
        if (gettype($title) === 'string') {
            $this->title = $title;
        } else {
            $msg = "Title must be a string, '" . gettype($title) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the name of the tool that was used to author the AssessmentTest. Returns an
     * empty string if not specfied.
     *
     * @return string A tool name or empty string if not specified.
     */
    public function getToolName()
    {
        return $this->toolName;
    }

    /**
     * Set the name of the tool that was used to author the AssessmentTest.
     *
     * @param string $toolName A tool name.
     * @throws \InvalidArgumentException If $toolName is not a string.
     */
    public function setToolName($toolName)
    {
        if (gettype($toolName) === 'string') {
            $this->toolName = $toolName;
        } else {
            $msg = "Toolname must be a string, '" . gettype($toolName) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the version of the tool that was used to author the AssessmentTest. Returns an
     * empty string if it was not specified.
     *
     * @return string A tool version.
     */
    public function getToolVersion()
    {
        return $this->toolVersion;
    }

    /**
     * Set the version of the tool that was used to author the AssessmentTest. Returns an
     * empty string if it was not specified.
     *
     * @param string $toolVersion A tool version.
     * @throws \InvalidArgumentException If $toolVersion is not a string.
     */
    public function setToolVersion($toolVersion)
    {
        if (gettype($toolVersion) === 'string') {
            $this->toolVersion = $toolVersion;
        } else {
            $msg = "ToolVersion must be a string, '" . gettype($toolVersion) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get a collection of OutcomeDeclaration objects bound to the AssessmentTest.
     *
     * @return \qtism\data\state\OutcomeDeclarationCollection A collection of OutcomeDeclaration objects.
     */
    public function getOutcomeDeclarations()
    {
        return $this->outcomeDeclarations;
    }

    /**
     * Set a collection of OutcomeDeclaration objects bound to the AssessmentTest.
     *
     * @param \qtism\data\state\OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations)
    {
        $this->outcomeDeclarations = $outcomeDeclarations;
    }

    /**
     * Get the time limits of this AssessmentTest. Returns null if not specified.
     *
     * @return \qtism\data\TimeLimits A TimeLimits object or null value if not specified.
     */
    public function getTimeLimits()
    {
        return $this->timeLimits;
    }

    /**
     * Set the time limits of this AssessmentTest.
     *
     * @param \qtism\data\TimeLimits $timeLimits A TimeLimits object.
     */
    public function setTimeLimits(TimeLimits $timeLimits = null)
    {
        $this->timeLimits = $timeLimits;
    }

    /**
     * Get the test parts that form the AssessmentTest.
     *
     * @return \qtism\data\TestPartCollection A collection of TestPart objects.
     */
    public function getTestParts()
    {
        return $this->testParts;
    }

    /**
     * Set the test parts that form the AssessmentTest.
     *
     * @param \qtism\data\TestPartCollection $testParts A collection of TestPart objects.
     */
    public function setTestParts(TestPartCollection $testParts)
    {
        $this->testParts = $testParts;
    }

    /**
     * Get the OutcomeProcessing of the AssessmentTest. Returns null if it was not
     * specified.
     *
     * @return \qtism\data\processing\OutcomeProcessing An OutcomeProcessing object or null if not specified.
     */
    public function getOutcomeProcessing()
    {
        return $this->outcomeProcessing;
    }

    /**
     * Set the OutcomeProcessing of the AssessmentTest.
     *
     * @param \qtism\data\processing\OutcomeProcessing $outcomeProcessing An OutcomeProcessing object.
     */
    public function setOutcomeProcessing(OutcomeProcessing $outcomeProcessing = null)
    {
        $this->outcomeProcessing = $outcomeProcessing;
    }

    /**
     * Whether the AssessmentTest holds an OutcomeProcessing object.
     *
     * @return boolean
     */
    public function hasOutcomeProcessing()
    {
        return is_null($this->getOutcomeProcessing()) !== true;
    }

    /**
     * Get the feedbacks associated to the AssessmentTest.
     *
     * @return \qtism\data\TestFeedbackCollection A collection of TestFeedback objects.
     */
    public function getTestFeedbacks()
    {
        return $this->testFeedbacks;
    }

    /**
     * Set the feedbacks associated to the AssessmentTest.
     *
     * @param \qtism\data\TestFeedbackCollection A collection of TestFeedback objects.
     */
    public function setTestFeedbacks(TestFeedbackCollection $testFeedbacks)
    {
        $this->testFeedbacks = $testFeedbacks;
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'assessmentTest';
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        $comp = array_merge(
            $this->getOutcomeDeclarations()->getArrayCopy(),
            $this->getTestFeedbacks()->getArrayCopy(),
            $this->getTestParts()->getArrayCopy()
        );

        if ($this->getOutcomeProcessing() !== null) {
            $comp[] = $this->getOutcomeProcessing();
        }

        if ($this->getTimeLimits() !== null) {
            $comp[] = $this->getTimeLimits();
        }

        return new QtiComponentCollection($comp);
    }

    /**
     * Whether the AssessmentTest is exclusively linear. Be carefull, if the test has no test part,
     * the result will be false.
     *
     * @return boolean
     */
    public function isExclusivelyLinear()
    {
        $testParts = $this->getTestParts();
        if (count($testParts) === 0) {
            return false;
        }

        $result = true;

        foreach ($testParts as $testPart) {
            if ($testPart->getNavigationMode() !== NavigationMode::LINEAR) {
                $result = false;
                $testParts->rewind();
                break;
            }
        }

        return $result;
    }

    /**
     * Whether the AssessmentTest as a TimeLimits component bound to it.
     *
     * @return boolean
     */
    public function hasTimeLimits()
    {
        return $this->getTimeLimits() !== null;
    }

    public function __clone()
    {
        $this->setObservers(new SplObjectStorage());
    }

    /**
     * @TODO DOC
     *
     * @param $component
     * @param $cp_index
     * @param $components
     */

    public static function getFirstItem($component, $cp_index, $components)
    {
        // @TODO tests

        switch ($component->getQtiClassName()) {
            case "assessmentItemRef":
                return $component;
                break;

            case "assessmentSection":
                $items = $component->getComponentsByClassName("assessmentItemRef")->getArrayCopy();

                // @todo PROBLEM WITH EMPTY SUBSECTION

                if (count($items) == 0) {
                    // Subsection...

                    // @TODO
                }
                else {
                    return $items[0];
                }
                break;

            case "testPart":
                $items = $component->getComponentsByClassName("assessmentItemRef")->getArrayCopy();

                // @todo PROBLEM WITH EMPTY TESTPART

                if (count($items) == 0) {
                    // Go to next testpart

                    // @TODO
                }
                else {
                    return $items[0];
                }
                break;

            default:
                return null;
        }
    }

    /**
     * @TODO DOC
     *
     * @param $component
     * @param $cp_index
     * @param $components
     */

    public static function getLastItem($component, $cp_index, $components)
    {
        // @TODO tests

        switch ($component->getQtiClassName()) {
            case "assessmentItemRef":
                return $component;
                break;

            case "assessmentSection":
                $items = $component->getComponentsByClassName("assessmentItemRef")->getArrayCopy();

                // @todo PROBLEM WITH EMPTY SUBSECTION

                if (count($items) == 0) {
                    // Subsection...

                    // @TODO
                }
                else {
                    return $items[count($items) - 1];
                }
                break;

            case "testPart":
                $items = $component->getComponentsByClassName("assessmentItemRef")->getArrayCopy();

                // @todo PROBLEM WITH EMPTY TESTPART

                if (count($items) == 0) {
                    // Go to previous testpart

                    // @TODO
                }
                else {
                    return $items[count($items) - 1];
                }
                break;

            default:
                return null;
        }
    }

    /**
     * @Todo DOC
     *
     * @param $paths
     * @param $prev_item
     * @param $target_item
     * @param $itemid_to_index
     * @return null
     */

    private function addPathsWithBranches($paths, $prev_item, $target_item, $itemid_to_index)
    {
        $new_paths = [];

        foreach ($paths as $path) {

            // get the index of the current item and of the target item

            $key_current_item = null;
            $key_target_item = null;

            $pathkeys = $path->getKeys();

            foreach ($pathkeys as $identifier) {

                if ($prev_item->getIdentifier() == $identifier) {
                    $key_current_item = $prev_item->getIdentifier();
                }

                if ($target_item->getIdentifier() == $identifier) {
                    $key_target_item = $target_item->getIdentifier();
                }
            }

            if (($key_current_item != null) and ($key_target_item != null)) {

                if ($key_current_item == $key_target_item) {
                    throw new BranchRuleTargetException("Recursive branching is not allowed.");
                }

                if ($itemid_to_index[$key_current_item] > $itemid_to_index[$key_target_item]) {
                    throw new BranchRuleTargetException("Branching backward is not allowed.");
                }

                if ($itemid_to_index[$key_current_item] < $itemid_to_index[$key_target_item]) {

                    $new_path = new AssessmentItemRefCollection($path->getArrayCopy());
                    $delete_keys = false;

                    // Delete from new path everything between $key_current_item and $key_target_item

                    foreach ($pathkeys as $identifier) {

                        if ($identifier == $key_target_item) {
                            break;
                        }

                        if ($delete_keys) {
                            unset($new_path[$identifier]);
                        }

                        if ($path[$identifier] == $key_current_item) {
                            $delete_keys = true;
                        }
                    }

                    $new_paths[] = $new_path;
                }
            }
        }
        
        return $new_paths;
    }

    /**
     * Returns an array with all possible paths for an AssessmentTest.
     *
     * Create the list with all possible paths that a student can take through an AssessmentTest.
     * It first gets the base path, with all items. Then it creates new shorter paths, that can
     * been taken with branches targeting further forward. Then it creates the new path possible
     * with items that are not mandatory due to the precondition.
     *
     * @param Boolean $asArray true to return an array, false to return an qtism\data\AssessmentItemRefCollection.
     * @return array of array of qtism\data\AssessmentItemRef | array of qtism\data\AssessmentItemRefCollection
     * @throws \Exception if branching is recursive of backward.
     */

    public function getPossiblePaths($asArray)
    {
        $paths = [];
        $items = new AssessmentItemRefCollection($this->getComponentsByClassName("assessmentItemRef")->getArrayCopy());
        $sections = new AssessmentSectionCollection($this->getComponentsByClassName("assessmentSection")->getArrayCopy());
        $testparts = new TestPartCollection($this->getComponentsByClassName("testPart")->getArrayCopy());
            
        $paths[] = $items;
        
        $itemid_list = $items->getKeys(); // list of the identifiers
        $itemid_to_index = array_flip($itemid_list); // get the index of the item with its ID, needed to order branches
        $sectid_list = $sections->getKeys();
        $tpid_list = $testparts->getKeys();

        // Array associating to each item the possible successor item, the same for the sections and parts

        $succs_item = [];

        // Association of the successor item to the next one

        for ($i = 0; $i < count($items); $i++) {
            $succs_item[$itemid_list[$i]] = [];

            if ($i < (count($items) - 1)) {
                $succs_item[$itemid_list[$i]][] = $items[$itemid_list[$i + 1]];
            }
        }

        // Checking existing branches to add other possible previous items

        foreach ($testparts as $test) {

            foreach ($test->getBranchRules() as $branch) {

                // Target analysis

                $target = $this->getComponentByIdentifier($branch->getTarget());

                if ($target == null) {
                    throw new BranchRuleTargetException("Target '" . $branch->getTarget() ."' doesn't exist.");
                }

                $target_item = AssessmentTest::getFirstItem($target, $branch->getTarget(), $items);
                $prev_item = AssessmentTest::getLastItem($test, $test->getIdentifier(), $items);

                if (!in_array($target_item, $succs_item[$prev_item->getIdentifier()])) {
                    $succs_item[$prev_item->getIdentifier()][] = $target_item;

                    // new successor possible => new paths possible

                    if ($target_item == null) {
                        var_dump("Code reached");
                        var_dump($branch->getTarget());
                        var_dump($target);
                    }

                    $paths = array_merge($paths, AssessmentTest::addPathsWithBranches($paths, $prev_item, $target_item, $itemid_to_index));
                }
            }
        }

        foreach ($sections as $sect) {

            foreach ($sect->getBranchRules() as $branch) {

                // Target analysis

                $target = $this->getComponentByIdentifier($branch->getTarget());

                if ($target == null) {
                    throw new BranchRuleTargetException("Target '" . $branch->getTarget() ."' doesn't exist.");
                }

                $target_item = AssessmentTest::getFirstItem($target, $branch->getTarget(), $items);
                $prev_item = AssessmentTest::getLastItem($sect, $test->getIdentifier(), $items);

                if (!in_array($target_item, $succs_item[$prev_item->getIdentifier()])) {
                    $succs_item[$prev_item->getIdentifier()][] = $target_item;

                    // new successor possible => new paths possible

                    if ($target_item == null) {
                        var_dump("Code reached");
                        var_dump($branch->getTarget());
                        var_dump($target);
                    }

                    $paths = array_merge($paths, AssessmentTest::addPathsWithBranches($paths, $prev_item, $target_item, $itemid_to_index));
                }
            }
        }

        foreach ($items as $item) {
            
            foreach ($item->getBranchRules() as $branch) {

                // Target analysis

                $target = $this->getComponentByIdentifier($branch->getTarget());

                if ($target == null) {
                    throw new BranchRuleTargetException("Target '" . $branch->getTarget() ."' doesn't exist.");
                }
                
                $target_item = AssessmentTest::getFirstItem($target, $branch->getTarget(), $items);
                $prev_item = AssessmentTest::getLastItem($item, $item->getIdentifier(), $items);

                if (!in_array($target_item, $succs_item[$prev_item->getIdentifier()])) {
                    $succs_item[$prev_item->getIdentifier()][] = $target_item;
                
                /*if (!in_array($target, $succs_item[$item->getIdentifier()])) {
                    $succs_item[$item->getIdentifier()][] = $target_item;*/

                    // new successor possible => new paths possible
                    
                    if ($target_item == null) {
                        var_dump("Code reached");
                        var_dump($branch->getTarget());
                        var_dump($target);
                    }

                    $paths = array_merge($paths, AssessmentTest::addPathsWithBranches($paths, $prev_item, $target_item, $itemid_to_index));
                }
            }
        }

        // Checking preConditions in tests, sections and itens

        foreach ($testparts as $tp) {

            if (count($tp->getPreConditions()) > 0) {

                $tp_items = $tp->getComponentsByClassName("assessmentItemRef")->getArrayCopy();

                // for each existing, duplicate it and remove the current item
                // (because it may not exist with the precondition)

                foreach ($paths as $path) {

                    $new_path = null;

                    if (count(array_intersect($tp_items, $path->getArrayCopy())) == count($tp_items)) {
                        $new_path = new AssessmentItemRefCollection($path->getArrayCopy());

                        foreach ($tp_items as $item) {
                            unset($new_path[$item->getIdentifier()]);
                        }
                    }

                    // Check if new path does't already exists in paths

                    if (($new_path != null) and (!in_array($new_path, $paths))) {
                        $paths[] = $new_path;
                    }
                }
            }
        }

        foreach ($sections as $sect) {

            if (count($sect->getPreConditions()) > 0) {

                $sect_items = $sect->getComponentsByClassName("assessmentItemRef")->getArrayCopy();

                // for each existing, duplicate it and remove the current item
                // (because it may not exist with the precondition)

                foreach ($paths as $path) {
                    
                    $new_path = null;

                    if (count(array_intersect($sect_items, $path->getArrayCopy())) == count($sect_items)) {
                        
                        $new_path = new AssessmentItemRefCollection($path->getArrayCopy());

                        foreach ($sect_items as $item) {
                            unset($new_path[$item->getIdentifier()]);
                        }
                    }

                    // Check if new path does't already exists in paths

                    if (($new_path != null) and (!in_array($new_path, $paths))) {
                        $paths[] = $new_path;
                    }
                }
            }
        }

        foreach ($items as $item) {

            if (count($item->getComponentsByClassName("preCondition")) > 0) {

                // for each existing, duplicate it and remove the current item
                // (because it may not exist with the precondition)

                foreach ($paths as $path) {

                    $new_path = null;

                    if (in_array($item, $path->getArrayCopy())) {
                        $new_path = new AssessmentItemRefCollection($path->getArrayCopy());
                        unset($new_path[$item->getIdentifier()]);
                    }

                    // Check if new path does't already exists in paths

                    if (($new_path != null) and (!in_array($new_path, $paths))) {
                        $paths[] = $new_path;
                    }
                }
            }
        }

        // Transform into array if necessary

        if ($asArray) {
            foreach ($paths as $key => $path) {
                $paths[$key] = $path->getArrayCopy();
            }
        }

        return $paths;
    }

    /**
     * Returns an array with all shortest possible paths for a AssessmentTest.
     *
     * Iterates on all possible paths and when it finds a path shorter than the minimum length,
     * it is stored as the new shortest path.
     *
     * @return array of qtism\data\AssessmentItemRefCollection An array with all shortest possible paths
     * for this AssessmentTest.
     */

    public function getShortestPaths()
    {
        $paths = $this->getPossiblePaths(false);
        $min_count = PHP_INT_MAX;
        $min_paths = [];

        foreach ($paths as $path) {
            if (sizeof($path) < $min_count) {
                $min_count = sizeof($path);
                $min_paths = [];
            }

            if (sizeof($path) <= $min_count) {
                $min_paths[] = $path;
            }
        }

        return $min_paths;
    }

    /**
     * Returns an array with all longest possible paths for a AssessmentTest.
     * Currently it's the path with all items that will always be returned.
     *
     * Iterates on all possible paths and when it finds a path longer than the maximum length,
     * it is stored as the new longest path.
     *
     * @return array of qtism\data\AssessmentItemRefCollection An array with all longest possible paths
     * for this AssessmentTest.
     */

    public function getLongestPaths()
    {
        $paths = $this->getPossiblePaths(false);
        $max_count = 0;
        $max_paths = [];

        foreach ($paths as $path) {
            if (sizeof($path) > $max_count) {
                $max_count = sizeof($path);
                $max_paths = [];
            }

            if (sizeof($path) >= $max_count) {
                $max_paths[] = $path;
            }
        }

        return $max_paths;
    }
}
