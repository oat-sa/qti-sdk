<?php

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\common\datatypes\QtiDuration;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\AssessmentTest;
use qtism\data\NavigationMode;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\TestPart;
use qtism\data\TestPartCollection;
use qtism\data\TimeLimits;
use qtismtest\QtiSmTestCase;

/**
 * Class AssessmentTestTest
 */
class AssessmentTestTest extends QtiSmTestCase
{
    public function testTimeLimits()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/timelimits.xml');

        $testPart = $doc->getDocumentComponent()->getComponentByIdentifier('testPartId');
        $this::assertTrue($testPart->hasTimeLimits());
        $timeLimits = $testPart->getTimeLimits();

        $this::assertTrue($timeLimits->getMinTime()->equals(new QtiDuration('PT60S')));
        $this::assertTrue($timeLimits->getMaxTime()->equals(new QtiDuration('PT120S')));
        $this::assertTrue($timeLimits->doesAllowLateSubmission());
    }

    public function testCreateAssessmentTestWrongIdentifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $test = new AssessmentTest('999', 'Nine Nine Nine');
    }

    public function testCreateAssessmentTestWrongTitle()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Title must be a string, 'integer' given.");

        $test = new AssessmentTest('ABC', 999);
    }

    public function testSetToolNameWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Toolname must be a string, 'integer' given.");

        $test = new AssessmentTest('ABC', 'ABC');
        $test->setToolName(999);
    }

    public function testSetToolVersionWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("ToolVersion must be a string, 'integer' given.");

        $test = new AssessmentTest('ABC', 'ABC');
        $test->setToolVersion(999);
    }

    public function testComponentsWithTimeLimits()
    {
        $test = new AssessmentTest('ABC', 'ABC');
        $test->setTimeLimits(
            new TimeLimits()
        );

        $components = $test->getComponents();
        $this::assertInstanceOf(TimeLimits::class, $components[count($components) - 1]);
    }

    public function testIsExclusivelyLinearNoTestParts()
    {
        $test = new AssessmentTest('ABC', 'ABC');
        $this::assertFalse($test->isExclusivelyLinear());
    }

    public function testIsExclusivelyLinear()
    {
        $test = new AssessmentTest('ABC', 'ABC');

        $testPart = new TestPart(
            'ABCD',
            new AssessmentSectionCollection(
                [
                    new AssessmentSection('ABCDE', 'ABCDE', true),
                ]
            ),
            NavigationMode::NONLINEAR
        );

        $test->setTestParts(
            new TestPartCollection(
                [
                    $testPart,
                ]
            )
        );

        $this::assertFalse($test->isExclusivelyLinear());

        $testPart->setNavigationMode(NavigationMode::LINEAR);
        $this::assertTrue($test->isExclusivelyLinear());
    }
}
