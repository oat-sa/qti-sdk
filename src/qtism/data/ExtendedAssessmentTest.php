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

/**
 * The ExtendedAssessmentTest class is an extended representation of the QTI
 * AssessmentTest class. It gathers together the AssessmentTest + additional references
 * to testFeedback components.
 */
class ExtendedAssessmentTest extends AssessmentTest
{
    /**
     * A collection of TestFeedbackRef objects.
     *
     * @var TestFeedbackRefCollection
     * @qtism-bean-property
     */
    private $testFeedbackRefs;

    /**
     * Create a new ExtendedAssessmentTest object.
     *
     * @param string $identifier A QTI identifier.
     * @param string $title A title.
     * @param TestPartCollection $testParts A collection of ExtendedTestPart objects.
     */
    public function __construct($identifier, $title, ?TestPartCollection $testParts = null)
    {
        parent::__construct($identifier, $title, $testParts);
        $this->setTestFeedbackRefs(new TestFeedbackRefCollection());
    }

    /**
     * Set the collection of TestFeedbackRef objects.
     *
     * @param TestFeedbackRefCollection $testFeedbackRefs
     */
    public function setTestFeedbackRefs(TestFeedbackRefCollection $testFeedbackRefs): void
    {
        $this->testFeedbackRefs = $testFeedbackRefs;
    }

    /**
     * Get the collection of TestFeedbackRef objects.
     *
     * @return TestFeedbackRefCollection
     */
    public function getTestFeedbackRefs(): TestFeedbackRefCollection
    {
        return $this->testFeedbackRefs;
    }

    /**
     * Add a TestFeedbackRef object.
     *
     * @param TestFeedbackRef $testFeedbackRef
     */
    public function addTestFeedbackRef(TestFeedbackRef $testFeedbackRef): void
    {
        $this->getTestFeedbackRefs()->attach($testFeedbackRef);
    }

    /**
     * Remove a TestFeedbackRef object.
     *
     * @param TestFeedbackRef $testFeedbackRef
     */
    public function removeTestFeedbackRef(TestFeedbackRef $testFeedbackRef): void
    {
        $this->getTestFeedbackRefs()->detach($testFeedbackRef);
    }

    /**
     * Create an ExtendedAssessmentTest object from an AssessmentTest object.
     *
     * @param AssessmentTest $assessmentTest
     * @return ExtendedAssessmentTest
     */
    public static function createFromAssessmentTest(AssessmentTest $assessmentTest): ExtendedAssessmentTest
    {
        $ref = new ExtendedAssessmentTest(
            $assessmentTest->getIdentifier(),
            $assessmentTest->getTitle(),
            $assessmentTest->getTestParts()
        );

        $ref->setTimeLimits($assessmentTest->getTimeLimits());
        $ref->setOutcomeDeclarations($assessmentTest->getOutcomeDeclarations());
        $ref->setOutcomeProcessing($assessmentTest->getOutcomeProcessing());
        $ref->setTestFeedbacks($assessmentTest->getTestFeedbacks());
        $ref->setToolName($assessmentTest->getToolName());
        $ref->setToolVersion($assessmentTest->getToolVersion());

        return $ref;
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        $components = array_merge(
            parent::getComponents()->getArrayCopy(),
            $this->getTestFeedbackRefs()->getArrayCopy()
        );

        return new QtiComponentCollection($components);
    }
}
