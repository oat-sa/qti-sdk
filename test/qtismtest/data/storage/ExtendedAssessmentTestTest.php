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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtismtest\data\storage;

use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentTest;
use qtism\data\processing\OutcomeProcessing;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\TestFeedbackCollection;
use qtism\data\TestPartCollection;
use qtism\data\TestFeedbackRef;
use PHPUnit\Framework\TestCase;

class ExtendedAssessmentTestTest extends TestCase
{
    public function testCreateFromAssessmentTest()
    {
        $identifier = 'test-id';
        $title = 'Test Title';
        $testParts = new TestPartCollection();
        $assessmentTest = $this->createMock(AssessmentTest::class);
        $outcomeProcessing = $this->createMock(OutcomeProcessing::class);
        $testFeedbackCollection = $this->createMock(TestFeedbackCollection::class);
        $outcomeDeclarationCollection = $this->createMock(OutcomeDeclarationCollection::class);
        $assessmentTest->method('getIdentifier')->willReturn($identifier);
        $assessmentTest->method('getTitle')->willReturn($title);
        $assessmentTest->method('getTestParts')->willReturn($testParts);
        $assessmentTest->method('getOutcomeProcessing')->willReturn($outcomeProcessing);
        $assessmentTest->method('getOutcomeDeclarations')->willReturn($outcomeDeclarationCollection);
        $assessmentTest->method('getTestFeedbacks')->willReturn($testFeedbackCollection);
        $assessmentTest->method('getToolName')->willReturn('Tool Name');
        $assessmentTest->method('getToolVersion')->willReturn('1.0');

        $extendedTest = ExtendedAssessmentTest::createFromAssessmentTest($assessmentTest);

        $this->assertInstanceOf(ExtendedAssessmentTest::class, $extendedTest);
        $this->assertEquals($identifier, $extendedTest->getIdentifier());
        $this->assertEquals($title, $extendedTest->getTitle());
        $this->assertEquals($testParts, $extendedTest->getTestParts());
    }

    public function testAddAndRemoveTestFeedbackRef()
    {
        $extendedTest = new ExtendedAssessmentTest('test-id', 'Test Title');
        $testFeedbackRef = $this->createMock(TestFeedbackRef::class);

        $extendedTest->addTestFeedbackRef($testFeedbackRef);
        $this->assertCount(1, $extendedTest->getTestFeedbackRefs());

        $extendedTest->removeTestFeedbackRef($testFeedbackRef);
        $this->assertCount(0, $extendedTest->getTestFeedbackRefs());
    }
}
