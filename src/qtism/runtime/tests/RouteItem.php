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

use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\AssessmentTest;
use qtism\data\content\RubricBlockCollection;
use qtism\data\content\RubricBlockRefCollection;
use qtism\data\rules\BranchRule;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreCondition;
use qtism\data\rules\PreConditionCollection;
use qtism\data\TestPart;

/**
 * The RouteItem class describes the composite items of a Route object.
 */
class RouteItem
{
    /**
     * The AssessmentTest the RouteItem is bound to.
     *
     * @var AssessmentTest
     */
    private $assessmentTest;

    /**
     * The AssessmentItemRef object bound to the RouteItem.
     *
     * @var AssessmentItemRef
     */
    private $assessmentItemRef;

    /**
     * The TestPart object bound to the RouteItem.
     *
     * @var TestPart
     */
    private $testPart;

    /**
     * The AssessmentSectionCollection object bound to the RouteItem.
     *
     * @var AssessmentSectionCollection
     */
    private $assessmentSections;

    /**
     * The BranchRule objects to be applied after the RouteItem.
     *
     * @var BranchRuleCollection
     */
    private $branchRules;

    /**
     * The PreCondition objects to be applied prior to the RouteItem.
     *
     * @var PreConditionCollection
     */
    private $preConditions;

    /**
     * The occurence number.
     *
     * @var int
     */
    private $occurence = 0;

    /**
     * Create a new RouteItem object.
     *
     * @param AssessmentItemRef $assessmentItemRef The AssessmentItemRef object bound to the RouteItem.
     * @param AssessmentSection|AssessmentSectionCollection $assessmentSections The AssessmentSection object bound to the RouteItem.
     * @param TestPart $testPart The TestPart object bound to the RouteItem.
     * @param AssessmentTest $assessmentTest The AssessmentTest object bound to the RouteItem.
     */
    public function __construct(AssessmentItemRef $assessmentItemRef, $assessmentSections, TestPart $testPart, AssessmentTest $assessmentTest)
    {
        $this->setAssessmentItemRef($assessmentItemRef);
        $this->setAssessmentTest($assessmentTest);

        if ($assessmentSections instanceof AssessmentSection) {
            $this->setAssessmentSection($assessmentSections);
        } else {
            $this->setAssessmentSections($assessmentSections);
        }

        $this->setTestPart($testPart);
        $this->setBranchRules(new BranchRuleCollection());
        $this->setPreConditions(new PreConditionCollection());

        $this->addBranchRules($assessmentItemRef->getBranchRules());
        $this->addPreConditions($assessmentItemRef->getPreConditions());
    }

    /**
     * Set the AssessmentTest object bound to the RouteItem.
     *
     * @param AssessmentTest $assessmentTest An AssessmentTest object.
     */
    public function setAssessmentTest(AssessmentTest $assessmentTest): void
    {
        $this->assessmentTest = $assessmentTest;
    }

    /**
     * Get the AssessmentTest object bound to the RouteItem.
     *
     * @return AssessmentTest An AssessmentTest object.
     */
    public function getAssessmentTest(): AssessmentTest
    {
        return $this->assessmentTest;
    }

    /**
     * Set the AssessmentItemRef object bound to the RouteItem.
     *
     * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     */
    public function setAssessmentItemRef(AssessmentItemRef $assessmentItemRef): void
    {
        $this->assessmentItemRef = $assessmentItemRef;
    }

    /**
     * Get the AssessmentItemRef object bound to the RouteItem.
     *
     * @return AssessmentItemRef An AssessmentItemRef object.
     */
    public function getAssessmentItemRef(): AssessmentItemRef
    {
        return $this->assessmentItemRef;
    }

    /**
     * Set the TestPart object bound to the RouteItem.
     *
     * @param TestPart $testPart A TestPart object.
     */
    public function setTestPart(TestPart $testPart): void
    {
        $this->testPart = $testPart;
    }

    /**
     * Get the TestPart object bound to the RouteItem.
     *
     * @return TestPart A TestPart object.
     */
    public function getTestPart(): TestPart
    {
        return $this->testPart;
    }

    /**
     * Set the AssessmentSection object bound to the RouteItem.
     *
     * @param AssessmentSection $assessmentSection An AssessmentSection object.
     */
    public function setAssessmentSection(AssessmentSection $assessmentSection): void
    {
        $this->assessmentSections = new AssessmentSectionCollection([$assessmentSection]);
    }

    /**
     * Set the AssessmentSection objects bound to the RouteItem.
     *
     * @param AssessmentSectionCollection $assessmentSections A collection of AssessmentSection objects.
     */
    public function setAssessmentSections(AssessmentSectionCollection $assessmentSections): void
    {
        $this->assessmentSections = $assessmentSections;
    }

    /**
     * Set the occurence number.
     *
     * @param int $occurence An occurence number.
     */
    public function setOccurence($occurence): void
    {
        $this->occurence = $occurence;
    }

    /**
     * Get the occurence number.
     *
     * @return int An occurence number.
     */
    public function getOccurence(): int
    {
        return $this->occurence;
    }

    /**
     * Get the BranchRule objects to be applied after the RouteItem.
     *
     * @return BranchRuleCollection A collection of BranchRule objects.
     */
    public function getBranchRules(): BranchRuleCollection
    {
        return $this->branchRules;
    }

    /**
     * Set the BranchRule objects to be applied after the RouteItem.
     *
     * @param BranchRuleCollection $branchRules A collection of BranchRule objects.
     */
    public function setBranchRules(BranchRuleCollection $branchRules): void
    {
        $this->branchRules = $branchRules;
    }

    /**
     * Add a BranchRule object to be applied after the RouteItem.
     *
     * @param BranchRule $branchRule A BranchRule object to be added.
     */
    public function addBranchRule(BranchRule $branchRule): void
    {
        $this->branchRules->attach($branchRule);
    }

    /**
     * Add some BranchRule objects to be applied after the RouteItem.
     *
     * @param BranchRuleCollection $branchRules A collection of BranchRule object.
     */
    public function addBranchRules(BranchRuleCollection $branchRules): void
    {
        foreach ($branchRules as $branchRule) {
            $this->addBranchRule($branchRule);
        }
    }

    /**
     * Get the PreCondition objects to be applied prior to the RouteItem.
     *
     * @return PreConditionCollection A collection of PreCondition objects.
     */
    public function getPreConditions(): PreConditionCollection
    {
        return $this->preConditions;
    }

    /**
     * Get the PreConditions that actually need to be applied considering all the parent elements: TestPart and Section
     *
     * @return PreConditionCollection A collection of PreCondition objects.
     */
    public function getEffectivePreConditions(): PreConditionCollection
    {
        $routeItemPreConditions = new PreConditionCollection([]);

        foreach ($this->getTestPart()->getPreConditions() as $preCondition) {
            $routeItemPreConditions->attach($preCondition);
        }

        foreach ($this->getAssessmentSection()->getPreConditions() as $preCondition) {
            $routeItemPreConditions->attach($preCondition);
        }

        foreach ($this->getPreConditions() as $preCondition) {
            $routeItemPreConditions->attach($preCondition);
        }

        return $routeItemPreConditions;
    }

    /**
     * Set the PreCondition objects to be applied prior to the RouteItem.
     *
     * @param PreConditionCollection $preConditions A collection of PreCondition objects.
     */
    public function setPreConditions(PreConditionCollection $preConditions): void
    {
        $this->preConditions = $preConditions;
    }

    /**
     * Add a PreCondition object to be applied prior to the RouteItem.
     *
     * @param PreCondition $preCondition A PreCondition object to be added.
     */
    public function addPreCondition(PreCondition $preCondition): void
    {
        $this->preConditions->attach($preCondition);
    }

    /**
     * Add some PreConditon objects to be applied prior to the RouteItem.
     *
     * @param PreConditionCollection $preConditions A collection of PreCondition object.
     */
    public function addPreConditions(PreConditionCollection $preConditions): void
    {
        foreach ($preConditions as $preCondition) {
            $this->addPreCondition($preCondition);
        }
    }

    /**
     * Increment the occurence number by 1.
     */
    public function incrementOccurenceNumber(): void
    {
        $this->setOccurence($this->getOccurence() + 1);
    }

    /**
     * Get the unique AssessmentSection object bound to the RouteItem. If the RouteItem
     * is bound to multiple assessment sections, the nearest parent of the RouteItem's item's assessment section
     * will be returned.
     *
     * @return AssessmentSection An AssessmentSection object.
     */
    public function getAssessmentSection(): AssessmentSection
    {
        $assessmentSections = $this->getAssessmentSections()->getArrayCopy();

        return $assessmentSections[count($assessmentSections) - 1];
    }

    /**
     * Get the AssessmentSection objects bound to the RouteItem.
     *
     * @return AssessmentSectionCollection An AssessmentSectionCollection object.
     */
    public function getAssessmentSections(): AssessmentSectionCollection
    {
        return $this->assessmentSections;
    }

    /**
     * Get the ItemSessionControl in force for this RouteItem as a RouteItemSessionControl object.
     *
     * @return RouteItemSessionControl|null The ItemSessionControl in force or null if the RouteItem is not under ItemSessionControl.
     */
    public function getItemSessionControl(): ?RouteItemSessionControl
    {
        if (($isc = $this->getAssessmentItemRef()->getItemSessionControl()) !== null) {
            return RouteItemSessionControl::createFromItemSessionControl($isc, $this->getAssessmentItemRef());
        } else {
            $inForce = null;

            // Look in assessmentSections.
            foreach ($this->getAssessmentSections() as $section) {
                if (($isc = $section->getItemSessionControl()) !== null) {
                    $inForce = RouteItemSessionControl::createFromItemSessionControl($isc, $section);
                }
            }

            // Nothing found in assessmentSections, look in testPart.
            if ($inForce === null && ($isc = $this->getTestPart()->getItemSessionControl()) !== null) {
                $inForce = RouteItemSessionControl::createFromItemSessionControl($isc, $this->getTestPart());
            }

            return $inForce;
        }
    }

    /**
     * Get the rubricBlocks related to this routeItem. The returned
     * rubricBlocks are be ordered from the top most to the bottom of
     * the assessmentTest hierarchy.
     *
     * @return RubricBlockCollection A collection of RubricBlock objects.
     */
    public function getRubricBlocks(): RubricBlockCollection
    {
        $rubrics = new RubricBlockCollection();

        foreach ($this->getAssessmentSections() as $section) {
            $rubrics->merge($section->getRubricBlocks());
        }

        return $rubrics;
    }

    /**
     * Get the rubricBlockRefs related to this routeItem. The returned
     * rubricBlockRefs are ordered from the top most to the bottom of
     * the assessmentTest hierarchy.
     *
     * @return RubricBlockRefCollection A collection of RubricBlockRef objects.
     */
    public function getRubricBlockRefs(): RubricBlockRefCollection
    {
        $rubrics = new RubricBlockRefCollection();

        foreach ($this->getAssessmentSections() as $section) {
            $rubrics->merge($section->getRubricBlockRefs());
        }

        return $rubrics;
    }

    /**
     * Get the TimeLimits in force for the RouteItem.
     *
     * @param bool $excludeItem Whether or not include the TimeLimits in force for the assessment item of the RouteItem.
     * @return RouteTimeLimitsCollection
     */
    public function getTimeLimits($excludeItem = false): RouteTimeLimitsCollection
    {
        $timeLimits = new RouteTimeLimitsCollection();

        if (($tl = $this->getAssessmentTest()->getTimeLimits()) !== null) {
            $timeLimits[] = RouteTimeLimits::createFromTimeLimits($tl, $this->getAssessmentTest());
        }

        if (($tl = $this->getTestPart()->getTimeLimits()) !== null) {
            $timeLimits[] = RouteTimeLimits::createFromTimeLimits($tl, $this->getTestPart());
        }

        foreach ($this->getAssessmentSections() as $section) {
            if (($tl = $section->getTimeLimits()) !== null) {
                $timeLimits[] = RouteTimeLimits::createFromTimeLimits($tl, $section);
            }
        }

        if ($excludeItem === false && ($tl = $this->getAssessmentItemRef()->getTimeLimits()) !== null) {
            $timeLimits[] = RouteTimeLimits::createFromTimeLimits($tl, $this->getAssessmentItemRef());
        }

        return $timeLimits;
    }

    public function getEffectiveBranchRules(): BranchRuleCollection
    {
        if ($this->getBranchRules()->count() > 0) {
            return $this->getBranchRules();
        }

        $sectionBranchRules = $this->getEffectiveSectionBranchRules();

        if ($sectionBranchRules === null || $sectionBranchRules->count() > 0) {
            return $sectionBranchRules ?? new BranchRuleCollection();
        }

        $testPartSections = $this->getTestPart()->getAssessmentSections()->getArrayCopy();
        $currentItemSections = $this->getAssessmentSections()->getArrayCopy();

        if (
            end($testPartSections) === $currentItemSections[0]
            && $this->getTestPart()->getBranchRules()->count() > 0
        ) {
            return $this->getTestPart()->getBranchRules();
        }

        return new BranchRuleCollection();
    }

    /**
     * Selects branching rules from the section/subsection.
     * Branching rules will be selected only if the item or subsection is the last one in the parent section.
     *
     * @return ?BranchRuleCollection Returns the branching rules for the last section or null if the element/subsection
     *                               is not the last.
     */
    private function getEffectiveSectionBranchRules(): ?BranchRuleCollection
    {
        /** @var AssessmentSection[] $sections */
        $sections = $this->getAssessmentSections()->getArrayCopy();

        // Remove the current section from the section list, as this section contains a list of items, not sections.
        $currentSection = array_pop($sections);
        $currentSectionItems = $currentSection->getSectionParts()->getArrayCopy();

        if (end($currentSectionItems) !== $this->getAssessmentItemRef()) {
            return null;
        }

        if ($currentSection->getBranchRules()->count() > 0) {
            return $currentSection->getBranchRules();
        }

        $lastSection = $currentSection;

        // Iterate through parent sections.
        // Note: $sections should not contain the current section, as `$section->getSectionParts()` would then return a
        // list of items instead of sections.
        foreach (array_reverse($sections) as $section) {
            $sectionParts = $section->getSectionParts()->getArrayCopy();

            if (end($sectionParts) !== $lastSection) {
                return null;
            }

            if ($section->getBranchRules()->count() > 0) {
                return $section->getBranchRules();
            }

            $lastSection = $section;
        }

        return new BranchRuleCollection();
    }
}
