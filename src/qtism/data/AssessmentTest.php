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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\processing\OutcomeProcessing;
use qtism\data\rules\BranchRule;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\Utils as DataUtils;
use SplObjectStorage;

/**
 * From IMS QTI:
 *
 * A test is a group of assessmentItems with an associated set of rules that determine
 * which of the items the candidate sees, in what order, and in what way the candidate
 * interacts with them. The rules describe the valid paths through the test, when responses
 * are submitted for response processing and when (if at all) feedback is to be given.
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
     * @var OutcomeDeclarationCollection
     * @qtism-bean-property
     */
    private $outcomeDeclarations;

    /**
     * From IMS QTI:
     *
     * Optionally controls the amount of time a candidate is allowed for the entire test.
     *
     * @var TimeLimits
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
     * @var TestPartCollection
     * @qtism-bean-property
     */
    private $testParts;

    /**
     * From IMS QTI:
     *
     * The set of rules used for calculating the values of the test outcomes.
     *
     * @var OutcomeProcessing
     * @qtism-bean-property
     */
    private $outcomeProcessing = null;

    /**
     * From IMS QTI:
     *
     * Contains the test-level feedback controlled by the test outcomes.
     *
     * @var TestFeedbackCollection
     * @qtism-bean-property
     */
    private $testFeedbacks;

    /**
     * AssessmentTest constructor.
     *
     * @param $identifier
     * @param $title
     * @param TestPartCollection|null $testParts
     */
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
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the identifier of the AssessmentTest.
     *
     * @param string $identifier A QTI Identifier.
     * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
     */
    public function setIdentifier($identifier): void
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the title of the AssessmentTest.
     *
     * @param string $title A title.
     * @throws InvalidArgumentException If $title is not a string.
     */
    public function setTitle($title): void
    {
        if (is_string($title)) {
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
    public function getToolName(): string
    {
        return $this->toolName;
    }

    /**
     * Set the name of the tool that was used to author the AssessmentTest.
     *
     * @param string $toolName A tool name.
     * @throws InvalidArgumentException If $toolName is not a string.
     */
    public function setToolName($toolName): void
    {
        if (is_string($toolName)) {
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
    public function getToolVersion(): string
    {
        return $this->toolVersion;
    }

    /**
     * Set the version of the tool that was used to author the AssessmentTest. Returns an
     * empty string if it was not specified.
     *
     * @param string $toolVersion A tool version.
     * @throws InvalidArgumentException If $toolVersion is not a string.
     */
    public function setToolVersion($toolVersion): void
    {
        if (is_string($toolVersion)) {
            $this->toolVersion = $toolVersion;
        } else {
            $msg = "ToolVersion must be a string, '" . gettype($toolVersion) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get a collection of OutcomeDeclaration objects bound to the AssessmentTest.
     *
     * @return OutcomeDeclarationCollection A collection of OutcomeDeclaration objects.
     */
    public function getOutcomeDeclarations(): OutcomeDeclarationCollection
    {
        return $this->outcomeDeclarations;
    }

    /**
     * Set a collection of OutcomeDeclaration objects bound to the AssessmentTest.
     *
     * @param OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations): void
    {
        $this->outcomeDeclarations = $outcomeDeclarations;
    }

    /**
     * Get the time limits of this AssessmentTest. Returns null if not specified.
     *
     * @return TimeLimits A TimeLimits object or null value if not specified.
     */
    public function getTimeLimits(): ?TimeLimits
    {
        return $this->timeLimits;
    }

    /**
     * Set the time limits of this AssessmentTest.
     *
     * @param TimeLimits $timeLimits A TimeLimits object.
     */
    public function setTimeLimits(TimeLimits $timeLimits = null): void
    {
        $this->timeLimits = $timeLimits;
    }

    /**
     * Get the test parts that form the AssessmentTest.
     *
     * @return TestPartCollection A collection of TestPart objects.
     */
    public function getTestParts(): TestPartCollection
    {
        return $this->testParts;
    }

    /**
     * Set the test parts that form the AssessmentTest.
     *
     * @param TestPartCollection $testParts A collection of TestPart objects.
     */
    public function setTestParts(TestPartCollection $testParts): void
    {
        $this->testParts = $testParts;
    }

    /**
     * Get the OutcomeProcessing of the AssessmentTest. Returns null if it was not
     * specified.
     *
     * @return OutcomeProcessing An OutcomeProcessing object or null if not specified.
     */
    public function getOutcomeProcessing(): ?OutcomeProcessing
    {
        return $this->outcomeProcessing;
    }

    /**
     * Set the OutcomeProcessing of the AssessmentTest.
     *
     * @param OutcomeProcessing $outcomeProcessing An OutcomeProcessing object.
     */
    public function setOutcomeProcessing(OutcomeProcessing $outcomeProcessing = null): void
    {
        $this->outcomeProcessing = $outcomeProcessing;
    }

    /**
     * Whether the AssessmentTest holds an OutcomeProcessing object.
     *
     * @return bool
     */
    public function hasOutcomeProcessing(): bool
    {
        return $this->getOutcomeProcessing() !== null;
    }

    /**
     * Get the feedbacks associated to the AssessmentTest.
     *
     * @return TestFeedbackCollection A collection of TestFeedback objects.
     */
    public function getTestFeedbacks(): TestFeedbackCollection
    {
        return $this->testFeedbacks;
    }

    /**
     * Set the feedbacks associated to the AssessmentTest.
     *
     * @param TestFeedbackCollection A collection of TestFeedback objects.
     */
    public function setTestFeedbacks(TestFeedbackCollection $testFeedbacks): void
    {
        $this->testFeedbacks = $testFeedbacks;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'assessmentTest';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
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
     * @return bool
     */
    public function isExclusivelyLinear(): bool
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
     * @return bool
     */
    public function hasTimeLimits(): bool
    {
        return $this->getTimeLimits() !== null;
    }

    public function __clone()
    {
        $this->setObservers(new SplObjectStorage());
    }

    /**
     * Finds the new paths available with a branch. The branch must already have been analysed.
     *
     * Using the prevItem and the targetItem set as parameter, this method goes through all existing paths, to check
     * on which path a shortcut between the prevItem and the targetItem can be used : if it's the case, a new
     * path, taking the shortcut, is created.
     *
     * @param array $paths of \qtism\data\AssessmentItemRefCollection The list of possible paths already known
     * @param AssessmentItem $prevItem the last AssessmentItem that will be prompted before the branch.
     * @param AssessmentItem $targetItem the first AssessmentItem that will be prompted after the branch.
     * @param array $itemidToIndex of int A hashmap with identifier as keys, int array's indexes as values. It's
     * necessary to check for backward branching.
     * @param QtiComponent $component The BranchRule's QtiComponent.
     * @return array of \qtism\data\AssessmentItemRefCollection Returns the paths that can be added to the possible
     * paths due to the new possibilities afforded by the branch.
     * @throws BranchRuleTargetException if backward or recursive branching is found.
     */
    private function addPathsWithBranches($paths, $prevItem, $targetItem, $itemidToIndex, $component): array
    {
        $newPaths = [];

        if (($prevItem == null) and ($targetItem == null)) {
            if (!in_array(new AssessmentItemRefCollection(), $paths)) {
                $newPaths[] = new AssessmentItemRefCollection();
            }
        } elseif ($targetItem == null) { // Branching to the end of the test
            foreach ($paths as $path) {
                // get the index of the current item and of the target item

                $keyCurrentItem = null;

                $pathkeys = $path->getKeys();

                foreach ($pathkeys as $identifier) {
                    if ($prevItem->getIdentifier() == $identifier) {
                        $keyCurrentItem = $prevItem->getIdentifier();
                        break;
                    }
                }

                if ($keyCurrentItem != null) {
                    $newPath = new AssessmentItemRefCollection($path->getArrayCopy());
                    $deleteKeys = false;

                    // Delete from new path everything between $keyCurrentItem and $keyTargetItem

                    foreach ($pathkeys as $identifier) {
                        if ($deleteKeys) {
                            unset($newPath[$identifier]);
                        }

                        if ($path[$identifier] == $keyCurrentItem) {
                            $deleteKeys = true;
                        }
                    }

                    if (!in_array($newPath, $paths)) {
                        $newPaths[] = $newPath;
                    }
                }
            }
        } elseif ($prevItem == null) { // Branching starts at the beginning of the test
            foreach ($paths as $path) {
                // get the index of the current item and of the target item

                $keyTargetItem = null;

                $pathkeys = $path->getKeys();

                foreach ($pathkeys as $identifier) {
                    if ($targetItem->getIdentifier() == $identifier) {
                        $keyTargetItem = $targetItem->getIdentifier();
                    }
                }

                if ($keyTargetItem != null) {
                    $newPath = new AssessmentItemRefCollection($path->getArrayCopy());

                    // Delete from new path everything between $keyCurrentItem and $keyTargetItem

                    foreach ($pathkeys as $identifier) {
                        if ($identifier == $keyTargetItem) {
                            break;
                        }

                        unset($newPath[$identifier]);
                    }

                    if (!in_array($newPath, $paths)) {
                        $newPaths[] = $newPath;
                    }
                }
            }
        } else { // Normal case
            foreach ($paths as $path) {
                // get the index of the current item and of the target item

                $keyCurrentItem = null;
                $keyTargetItem = null;

                $pathkeys = $path->getKeys();

                foreach ($pathkeys as $identifier) {
                    if ($prevItem->getIdentifier() == $identifier) {
                        $keyCurrentItem = $prevItem->getIdentifier();
                    }

                    if ($targetItem->getIdentifier() == $identifier) {
                        $keyTargetItem = $targetItem->getIdentifier();
                    }
                }

                if (($keyCurrentItem != null) and ($keyTargetItem != null)) {
                    if ($keyCurrentItem == $keyTargetItem) {
                        throw new BranchRuleTargetException(
                            'Recursive branching is not allowed.',
                            BranchRuleTargetException::RECURSIVE_BRANCHING,
                            $component
                        );
                    }

                    if ($itemidToIndex[$keyCurrentItem] > $itemidToIndex[$keyTargetItem]) {
                        throw new BranchRuleTargetException(
                            'Branching backward is not allowed.',
                            BranchRuleTargetException::BACKWARD_BRANCHING,
                            $component
                        );
                    }

                    if ($itemidToIndex[$keyCurrentItem] < $itemidToIndex[$keyTargetItem]) {
                        $newPath = new AssessmentItemRefCollection($path->getArrayCopy());
                        $deleteKeys = false;

                        // Delete from new path everything between $keyCurrentItem and $keyTargetItem

                        foreach ($pathkeys as $identifier) {
                            if ($identifier == $keyTargetItem) {
                                break;
                            }

                            if ($deleteKeys) {
                                unset($newPath[$identifier]);
                            }

                            if ($path[$identifier] == $keyCurrentItem) {
                                $deleteKeys = true;
                            }
                        }

                        if (!in_array($newPath, $paths)) {
                            $newPaths[] = $newPath;
                        }
                    }
                }
            }
        }

        return $newPaths;
    }

    /**
     * Returns an array with all possible paths for an AssessmentTest.
     *
     * Create the list with all possible paths that a student can take through an AssessmentTest.
     * It first gets the base path, with all items. Then it creates new shorter paths, that can
     * been taken with branches targeting further forward. Then it creates the new path possible
     * with items that are not mandatory due to the precondition.
     *
     * @param Boolean $asArray true to return an array, false to return an \qtism\data\AssessmentItemRefCollection.
     * @return array of array of \qtism\data\AssessmentItemRef | array of \qtism\data\AssessmentItemRefCollection
     * @throws BranchRuleTargetException if branching is recursive of backward.
     */
    public function getPossiblePaths($asArray): array
    {
        $paths = [];

        $items = new AssessmentItemRefCollection();
        $sections = new AssessmentSectionCollection();
        $testparts = new TestPartCollection();

        foreach ($this->getComponentsByClassName(['assessmentItemRef', 'assessmentSection', 'testPart']) as $cp) {
            switch ($cp->getQtiClassName()) {
                case 'assessmentItemRef':
                    $items[] = $cp;
                    break;

                case 'assessmentSection':
                    $sections[] = $cp;
                    break;

                case 'testPart':
                    $testparts[] = $cp;
                    break;

                default:
                    break;
            }
        }

        $paths[] = $items;

        $itemidList = $items->getKeys(); // list of the identifiers
        $itemidToIndex = array_flip($itemidList); // get the index of the item with its ID, needed to order branches

        // Array associating to each item the possible successor item, the same for the sections and parts

        $succsItem = [];
        $succsItem[0] = [];

        // Association of the successor item to the next one

        for ($i = 0; $i < count($items); $i++) {
            $succsItem[$itemidList[$i]] = [];

            if ($i < (count($items) - 1)) {
                $succsItem[$itemidList[$i]][] = $items[$itemidList[$i + 1]];
            }
        }

        // Checking existing branches to add other possible previous items

        foreach ($testparts as $tp) {
            foreach ($tp->getBranchRules() as $branch) {
                $paths = $this->branchAnalysis($branch, $tp, $paths, $succsItem, $itemidToIndex, $items, $sections, $testparts);
            }
        }

        foreach ($sections as $sect) {
            foreach ($sect->getBranchRules() as $branch) {
                $paths = $this->branchAnalysis($branch, $sect, $paths, $succsItem, $itemidToIndex, $items, $sections, $testparts);
            }
        }

        foreach ($items as $item) {
            foreach ($item->getBranchRules() as $branch) {
                $paths = $this->branchAnalysis($branch, $item, $paths, $succsItem, $itemidToIndex, $items, $sections, $testparts);
            }
        }

        // Checking preConditions in tests, sections and items

        foreach ($testparts as $tp) {
            if (count($tp->getPreConditions()) > 0) {
                $tpItems = $tp->getComponentsByClassName('assessmentItemRef')->getArrayCopy();

                // for each existing, duplicate it and remove the current item
                // (because it may not exist with the precondition)

                foreach ($paths as $path) {
                    $newPath = null;

                    if (count(array_intersect($tpItems, $path->getArrayCopy())) == count($tpItems)) {
                        $newPath = new AssessmentItemRefCollection($path->getArrayCopy());

                        foreach ($tpItems as $item) {
                            unset($newPath[$item->getIdentifier()]);
                        }
                    }

                    // Check if new path does't already exists in paths

                    if (($newPath != null) and (!in_array($newPath, $paths))) {
                        $paths[] = $newPath;
                    }
                }
            }
        }

        foreach ($sections as $sect) {
            if (count($sect->getPreConditions()) > 0) {
                $sectItems = $sect->getComponentsByClassName('assessmentItemRef')->getArrayCopy();

                // for each existing, duplicate it and remove the current item
                // (because it may not exist with the precondition)

                foreach ($paths as $path) {
                    $newPath = null;

                    if (count(array_intersect($sectItems, $path->getArrayCopy())) == count($sectItems)) {
                        $newPath = new AssessmentItemRefCollection($path->getArrayCopy());

                        foreach ($sectItems as $item) {
                            unset($newPath[$item->getIdentifier()]);
                        }
                    }

                    // Check if new path does't already exists in paths

                    if (($newPath != null) and (!in_array($newPath, $paths))) {
                        $paths[] = $newPath;
                    }
                }
            }
        }

        foreach ($items as $item) {
            if (count($item->getPreconditions()) > 0) {
                // for each existing, duplicate it and remove the current item
                // (because it may not exist with the precondition)

                foreach ($paths as $path) {
                    $newPath = null;

                    if (in_array($item, $path->getArrayCopy())) {
                        $newPath = new AssessmentItemRefCollection($path->getArrayCopy());
                        unset($newPath[$item->getIdentifier()]);
                    }

                    // Check if new path does't already exists in paths

                    if (($newPath != null) and (!in_array($newPath, $paths))) {
                        $paths[] = $newPath;
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
     * Analyse the branch set as parameter and returns the list of possible paths updated with the new possibilities
     * given by the branch.
     *
     * First analyse the branch, behave appropriate if special EXIT mention, finds where the branch can create
     * shortcuts in the paths and adds these to the possible paths.
     *
     * @param BranchRule $branch The BranchRule to analyse.
     * @param QtiComponent $component The BranchRule's QtiComponent.
     * @param array $paths of \qtism\data\AssessmentItemRefCollection The list of possible paths already known.
     * @param array $succsItem of array of string For each AssessmentItem + the start (indexed as 0), a list of the
     * identifiers of the possible successor after the AssessmentItem. Necessary to avoid duplicating branches thus
     * paths. Argument passed by reference
     * @param array $itemidToIndex of int A hashmap with identifier as keys, int array's indexes as values. It's
     * necessary to check for backward branching.
     * @param AssessmentItemRefCollection $items The collection of all items in this AssessmentTest.
     * @param AssessmentSectionCollection $sections The collection of all sections in this AssessmentTest.
     * @param AssessmentTest $testparts The collection of all tests in this AssessmentTest.
     * @return array of \qtism\data\AssessmentItemRefCollection The list of possible paths updated with the new
     * possibilities given by the branch.
     * @throws BranchRuleTargetException if branching is recursive of backward.
     */
    private function branchAnalysis($branch, $component, $paths, &$succsItem, $itemidToIndex, $items, $sections, $testparts): array
    {
        // Special cases

        switch ($branch->getTarget()) {
            case 'EXIT_TEST':
                $prevItem = DataUtils::getLastItem($this, $component, $sections);

                if ($prevItem == null) {
                    $succsItem[0][] = null;
                    $paths = array_merge(
                        $paths,
                        $this->addPathsWithBranches($paths, $prevItem, null, $itemidToIndex, $component)
                    );
                } elseif (!in_array(null, $succsItem[$prevItem->getIdentifier()])) {
                    $succsItem[$prevItem->getIdentifier()][] = null;

                    // new successor possible => new paths possible

                    $paths = array_merge(
                        $paths,
                        $this->addPathsWithBranches($paths, $prevItem, null, $itemidToIndex, $component)
                    );
                }
                break;

            case 'EXIT_TESTPART':
                $prevItem = DataUtils::getLastItem($this, $component, $sections);
                $targetItem = null;
                $currentTpFound = false;

                // Find the beginning of the next testpart

                foreach ($testparts as $tp) {
                    if ($currentTpFound) {
                        $targetItem = DataUtils::getFirstItem($this, $tp, $sections);
                        break;
                    }

                    if (
                        (in_array(
                            $component,
                            $tp->getComponentsByClassName($component->getQtiClassName())->getArrayCopy()
                        )) or
                        ($component->getIdentifier() == $tp->getIdentifier())
                    ) {
                        $currentTpFound = true;
                    }
                }

                if ($prevItem == null) {
                    $succsItem[0][] = $targetItem;
                    $paths = array_merge(
                        $paths,
                        $this->addPathsWithBranches($paths, $prevItem, $targetItem, $itemidToIndex, $component)
                    );
                } elseif (!in_array($targetItem, $succsItem[$prevItem->getIdentifier()])) {
                    $succsItem[$prevItem->getIdentifier()][] = $targetItem;

                    $paths = array_merge(
                        $paths,
                        $this->addPathsWithBranches($paths, $prevItem, $targetItem, $itemidToIndex, $component)
                    );
                }
                break;

            case 'EXIT_SECTION':
                if ($component->getQtiClassName() == 'testPart') {
                    break;
                }

                $prevItem = DataUtils::getLastItem($this, $component, $sections);
                $targetItem = null;
                $prevSect = null;

                // Find the beginning of the next section

                foreach ($sections as $sect) {
                    if (
                        ($component->getQtiClassName() == 'assessmentSection') and
                        ($component->getIdentifier() == $sect->getIdentifier())
                    ) {
                        $prevSect = $sect;
                        break;
                    } elseif (
                        (($component->getQtiClassName() != 'assessmentSection')) and (in_array(
                            $component,
                            $sect->getComponentsByClassName($component->getQtiClassName())->getArrayCopy()
                        ))
                    ) {
                        $prevSect = $sect;
                        // No break to be sure that the deepest section is taken
                    }
                }

                $currentSctFound = false;

                foreach ($sections as $sect) {
                    if (
                        $currentSctFound and (!in_array(
                            $sect,
                            $prevSect->getSectionParts()->getArrayCopy()
                        ))
                    ) {
                        $targetItem = DataUtils::getFirstItem($this, $sect, $sections);
                        break;
                    }

                    if ($sect->getIdentifier() == $prevSect->getIdentifier()) {
                        $currentSctFound = true;
                    }
                }

                if ($prevItem == null) {
                    $succsItem[0][] = $targetItem;
                    $paths = array_merge(
                        $paths,
                        $this->addPathsWithBranches($paths, $prevItem, $targetItem, $itemidToIndex, $component)
                    );
                } elseif (!in_array($targetItem, $succsItem[$prevItem->getIdentifier()])) {
                    $succsItem[$prevItem->getIdentifier()][] = $targetItem;
                    $paths = array_merge(
                        $paths,
                        $this->addPathsWithBranches($paths, $prevItem, $targetItem, $itemidToIndex, $component)
                    );
                }
                break;

            default:
                // $target = $this->getComponentByIdentifier($branch->getTarget());

                $target = null;

                if ($items[$branch->getTarget()] != null) {
                    $target = $items[$branch->getTarget()];
                } elseif ($sections[$branch->getTarget()] != null) {
                    $target = $sections[$branch->getTarget()];
                } elseif ($testparts[$branch->getTarget()] != null) {
                    $target = $testparts[$branch->getTarget()];
                }

                if ($target == null) {
                    throw new BranchRuleTargetException(
                        "Target '" . $branch->getTarget() . "' doesn't exist.",
                        BranchRuleTargetException::UNKNOWN_TARGET,
                        $component
                    );
                }

                $targetItem = DataUtils::getFirstItem($this, $target, $sections);
                $prevItem = DataUtils::getLastItem($this, $component, $sections);

                if ($prevItem == null) {
                    $succsItem[0][] = $targetItem;
                    $paths = array_merge(
                        $paths,
                        $this->addPathsWithBranches($paths, $prevItem, $targetItem, $itemidToIndex, $component)
                    );
                } elseif (!in_array($targetItem, $succsItem[$prevItem->getIdentifier()])) {
                    $succsItem[$prevItem->getIdentifier()][] = $targetItem;

                    // new successor possible => new paths possible

                    $paths = array_merge(
                        $paths,
                        $this->addPathsWithBranches($paths, $prevItem, $targetItem, $itemidToIndex, $component)
                    );
                }
                break;
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
     * @throws BranchRuleTargetException
     */
    public function getShortestPaths(): array
    {
        $paths = $this->getPossiblePaths(false);
        $minCount = PHP_INT_MAX;
        $minPaths = [];

        foreach ($paths as $path) {
            if (sizeof($path) < $minCount) {
                $minCount = sizeof($path);
                $minPaths = [];
            }

            if (sizeof($path) <= $minCount) {
                $minPaths[] = $path;
            }
        }

        return $minPaths;
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
     * @throws BranchRuleTargetException
     */
    public function getLongestPaths(): array
    {
        $paths = $this->getPossiblePaths(false);
        $maxCount = 0;
        $maxPaths = [];

        foreach ($paths as $path) {
            if (sizeof($path) > $maxCount) {
                $maxCount = sizeof($path);
                $maxPaths = [];
            }

            if (sizeof($path) >= $maxCount) {
                $maxPaths[] = $path;
            }
        }

        return $maxPaths;
    }
}
