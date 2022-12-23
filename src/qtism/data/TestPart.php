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
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreConditionCollection;
use SplObjectStorage;

/**
 * The TestPart class.
 */
class TestPart extends QtiComponent implements QtiIdentifiable
{
    use QtiIdentifiableTrait;

    /**
     * From IMS QTI:
     *
     * The identifier of the test part must be unique within the test and must not be
     * the identifier of any assessmentSection or assessmentItemRef.
     *
     * @var string
     * @qtism-bean-property
     */
    private $identifier;

    /**
     * The navigation mode, a value of the NavigationMode enumeration.
     *
     * @var int
     * @qtism-bean-property
     */
    private $navigationMode = NavigationMode::LINEAR;

    /**
     * The submission mode, a value of the SubmissionMode enumeration.
     *
     * @var int
     * @qtism-bean-property
     */
    private $submissionMode = SubmissionMode::INDIVIDUAL;

    /**
     * From IMS QTI:
     *
     * A set of conditions evaluated during the test, that determine
     * if this part is to be skipped.
     *
     * @var PreConditionCollection
     * @qtism-bean-property
     */
    private $preConditions;

    /**
     * From IMS QTI:
     *
     * A set of rules, evaluated during the test, for setting an alternative
     * target as the next part of the test.
     *
     * @var BranchRuleCollection
     * @qtism-bean-property
     */
    private $branchRules;

    /**
     * From IMS QTI:
     *
     * Parameters used to control the allowable states of each item session in this part.
     * These values may be overridden at section and item level.
     *
     * @var ItemSessionControl
     * @qtism-bean-property
     */
    private $itemSessionControl = null;

    /**
     * From IMS QTI:
     *
     * Optionally controls the amount of time a candidate is allowed for this part of the test.
     *
     * @var TimeLimits
     * @qtism-bean-property
     */
    private $timeLimits = null;

    /**
     * From IMS QTI:
     *
     * The items contained in each testPart are arranged into sections and sub-sections.
     *
     * @var SectionPartCollection
     * @qtism-bean-property
     */
    private $assessmentSections;

    /**
     * From IMS QTI:
     *
     * Test-level feedback specific to this part of the test.
     *
     * @var TestFeedbackCollection
     * @qtism-bean-property
     */
    private $testFeedbacks;

    /**
     * Create a new instance of TestPart.
     *
     * @param string $identifier A QTI Identifier;
     * @param SectionPartCollection $assessmentSections A collection of AssessmentSection or AssessmentSectionRef objects objects.
     * @param int $navigationMode A value of the NavigationMode enumeration.
     * @param int $submissionMode A value of the SubmissionMode enumeration.
     * @throws InvalidArgumentException If an argument has the wrong type or format.
     */
    public function __construct($identifier, SectionPartCollection $assessmentSections, $navigationMode = NavigationMode::LINEAR, $submissionMode = SubmissionMode::INDIVIDUAL)
    {
        $this->setObservers(new SplObjectStorage());

        $this->setIdentifier($identifier);
        $this->setAssessmentSections($assessmentSections);
        $this->setNavigationMode($navigationMode);
        $this->setSubmissionMode($submissionMode);
        $this->setPreConditions(new PreConditionCollection());
        $this->setBranchRules(new BranchRuleCollection());
        $this->setTestFeedbacks(new TestFeedbackCollection());
    }

    /**
     * Get the identifier of the Test Part.
     *
     * @return string A QTI identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the identifier of the Test Part.
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
     * Get the navigation mode of the Test Part.
     *
     * @return int A value of the Navigation enumeration.
     */
    public function getNavigationMode(): int
    {
        return $this->navigationMode;
    }

    /**
     * Set the navigation mode of the Test Part.
     *
     * @param int $navigationMode A value of the Navigation enumaration.
     * @throws InvalidArgumentException If $navigation mode is not a value from the Navigation enumeration.
     */
    public function setNavigationMode($navigationMode): void
    {
        if (in_array($navigationMode, NavigationMode::asArray())) {
            $this->navigationMode = $navigationMode;
        } else {
            $msg = "'${navigationMode}' is not a valid value for NavigationMode.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the submission mode of the Test Part.
     *
     * @return int A value of the SubmissionMode enumeration.
     */
    public function getSubmissionMode(): int
    {
        return $this->submissionMode;
    }

    /**
     * Set the submission mode of the Test Part.
     *
     * @param int $submissionMode A value of the SubmissionMode enumeration.
     * @throws InvalidArgumentException If $submissionMode is not a value from the SubmissionMode enumeration.
     */
    public function setSubmissionMode($submissionMode): void
    {
        if (in_array($submissionMode, SubmissionMode::asArray())) {
            $this->submissionMode = $submissionMode;
        } else {
            $msg = "'${submissionMode}' is not a valid value for SubmissionMode.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the PreConditions that must be applied to this Test Part.
     *
     * @return PreConditionCollection A collection of PreCondition objects.
     */
    public function getPreConditions(): PreConditionCollection
    {
        return $this->preConditions;
    }

    /**
     * Set the PreConditions that must be applied to this Test Part.
     *
     * @param PreConditionCollection $preConditions A collection of PreCondition objects.
     */
    public function setPreConditions(PreConditionCollection $preConditions): void
    {
        $this->preConditions = $preConditions;
    }

    /**
     * Get the BranchRules that must be applied to this Test Part.
     *
     * @return BranchRuleCollection A collection of BranchRule objects.
     */
    public function getBranchRules(): BranchRuleCollection
    {
        return $this->branchRules;
    }

    /**
     * Set the BranchRules that must be applied to this Test Part.
     *
     * @param BranchRuleCollection $branchRules A collection of BranchRule objects.
     */
    public function setBranchRules(BranchRuleCollection $branchRules): void
    {
        $this->branchRules = $branchRules;
    }

    /**
     * Get the ItemSessionControl applied to this Test Part. Returns null if there
     * is no ItemSessionControl to apply.
     *
     * @return ItemSessionControl An ItemSessionControl object.
     */
    public function getItemSessionControl(): ?ItemSessionControl
    {
        return $this->itemSessionControl;
    }

    /**
     * Set the ItemSessionControl applied to this Test Part.
     *
     * @param ItemSessionControl $itemSessionControl An ItemSessionControl object.
     */
    public function setItemSessionControl(ItemSessionControl $itemSessionControl = null): void
    {
        $this->itemSessionControl = $itemSessionControl;
    }

    /**
     * Whether the TestPart holds an ItemSessionControl object.
     *
     * @return bool
     */
    public function hasItemSessionControl(): bool
    {
        return $this->getItemSessionControl() !== null;
    }

    /**
     * Get the TimeLimits applied to this Test Part. Returns null if there is no
     * TimeLimits to apply.
     *
     * @return TimeLimits A TimeLimits object.
     */
    public function getTimeLimits(): ?TimeLimits
    {
        return $this->timeLimits;
    }

    /**
     * Set the TimeLimits applied to this Test Part. Returns null if there is no
     * TimeLimits to apply.
     *
     * @param TimeLimits $timeLimits A TimeLimits object.
     */
    public function setTimeLimits(TimeLimits $timeLimits = null): void
    {
        $this->timeLimits = $timeLimits;
    }

    /**
     * Whether the TestPart holds a TimeLimits object.
     *
     * @return bool
     */
    public function hasTimeLimits(): bool
    {
        return $this->getTimeLimits() !== null;
    }

    /**
     * Get the AssessmentSections and/or AssessmentSectionRefs that are part of this Test Part.
     *
     * @return SectionPartCollection A collection of AssessmentSection and/or AssessmentSectionRef objects.
     */
    public function getAssessmentSections(): SectionPartCollection
    {
        return $this->assessmentSections;
    }

    /**
     * Set the AssessmentSections and/or AssessmentSectionRefs that are part of this Test Part.
     *
     * @param SectionPartCollection $assessmentSections A collection of AssessmentSection and/or AssessmentSectionRef objects.
     * @throws InvalidArgumentException If $assessmentSections is an empty collection or contains something else than AssessmentSection and/or AssessmentSectionRef objects.
     */
    public function setAssessmentSections(SectionPartCollection $assessmentSections): void
    {
        if (count($assessmentSections) > 0) {
            // Check that we have only AssessmentSection and/ord AssessmentSectionRef objects.
            foreach ($assessmentSections as $assessmentSection) {
                if (!$assessmentSection instanceof AssessmentSection && !$assessmentSection instanceof AssessmentSectionRef) {
                    $msg = 'A TestPart contain only contain AssessmentSection or AssessmentSectionRef objects.';
                    throw new InvalidArgumentException($msg);
                }
            }

            $this->assessmentSections = $assessmentSections;
        } else {
            $msg = 'A TestPart must contain at least one AssessmentSection.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the feedbacks that are part of this Test Part.
     *
     * @return TestFeedbackCollection A collection of TestFeedback objects.
     */
    public function getTestFeedbacks(): TestFeedbackCollection
    {
        return $this->testFeedbacks;
    }

    /**
     * Set the feedbacks that are part of this Test Part.
     *
     * @param TestFeedbackCollection $testFeedbacks A collection of TestFeedback objects.
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
        return 'testPart';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        $comp = array_merge(
            $this->getAssessmentSections()->getArrayCopy(),
            $this->getBranchRules()->getArrayCopy(),
            $this->getPreConditions()->getArrayCopy(),
            $this->getTestFeedbacks()->getArrayCopy()
        );

        if ($this->getItemSessionControl() !== null) {
            $comp[] = $this->getItemSessionControl();
        }

        if ($this->getTimeLimits() !== null) {
            $comp[] = $this->getTimeLimits();
        }

        return new QtiComponentCollection($comp);
    }

    public function __clone()
    {
        $this->setObservers(new SplObjectStorage());
    }
}
