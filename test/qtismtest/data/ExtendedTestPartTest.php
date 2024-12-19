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

namespace qtismtest\data;

use qtism\data\AssessmentSection;
use qtism\data\ExtendedTestPart;
use qtism\data\ItemSessionControl;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreConditionCollection;
use qtism\data\TestFeedbackCollection;
use qtism\data\TestPart;
use qtism\data\SectionPartCollection;
use qtism\data\TestFeedbackRef;
use PHPUnit\Framework\TestCase;

class ExtendedTestPartTest extends TestCase
{
    public function testCreateFromTestPart()
    {
        $assessmentSection = $this->createMock(AssessmentSection::class);
        $preConditionCollection = $this->createMock(PreConditionCollection::class);
        $branchRuleCollection = $this->createMock(BranchRuleCollection::class);
        $itemSessionControl = $this->createMock(ItemSessionControl::class);
        $testFeedbackRefCollection = $this->createMock(TestFeedbackCollection::class);
        $testPart = $this->createMock(TestPart::class);

        $sectionPartCollection = new SectionPartCollection();
        $sectionPartCollection->attach($assessmentSection);


        $testPart->method('getIdentifier')->willReturn('testIdentifier');
        $testPart->method('getAssessmentSections')->willReturn($sectionPartCollection);
        $testPart->method('getNavigationMode')->willReturn(1);
        $testPart->method('getSubmissionMode')->willReturn(1);
        $testPart->method('getPreConditions')->willReturn($preConditionCollection);
        $testPart->method('getBranchRules')->willReturn($branchRuleCollection);
        $testPart->method('getItemSessionControl')->willReturn($itemSessionControl);
        $testPart->method('getTestFeedbacks')->willReturn($testFeedbackRefCollection);

        $extendedTestPart = ExtendedTestPart::createFromTestPart($testPart);

        $this->assertInstanceOf(ExtendedTestPart::class, $extendedTestPart);
        $this->assertEquals('testIdentifier', $extendedTestPart->getIdentifier());
    }

    public function testAddAndRemoveTestFeedbackRef()
    {
        $assessmentSection = $this->createMock(AssessmentSection::class);
        $sectionPartCollection = new SectionPartCollection();
        $sectionPartCollection->attach($assessmentSection);

        $extendedTestPart = new ExtendedTestPart('testIdentifier', $sectionPartCollection);
        $testFeedbackRef = $this->createMock(TestFeedbackRef::class);

        $extendedTestPart->addTestFeedbackRef($testFeedbackRef);
        $this->assertCount(1, $extendedTestPart->getTestFeedbackRefs());

        $extendedTestPart->removeTestFeedbackRef($testFeedbackRef);
        $this->assertCount(0, $extendedTestPart->getTestFeedbackRefs());
    }
}
