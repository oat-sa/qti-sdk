<?php

namespace qtismtest;

use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\AssessmentTest;
use qtism\data\TestPart;
use qtism\data\TestPartCollection;
use qtism\runtime\tests\Route;

abstract class QtiSmRouteTestCase extends QtiSmTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Build a simple route:
     *
     * $testPartCount = 1; $itemCount = 3
     *
     * * Q1 - S1 - T1
     * * Q2 - S1 - T1
     * * Q3 - S1 - T1
     *
     * $testPartCount = 2; $itemCount = 1
     *
     * * Q1 - S1 - T1
     * * Q2 - S2 - T2
     *
     * @return Route
     */
    public static function buildSimpleRoute($routeClass = 'qtism\\runtime\\tests\\Route', $testPartCount = 1, $itemCount = 3)
    {
        $route = new $routeClass();
        $assessmentTest = new AssessmentTest('test', 'A Test');

        for ($i = 0; $i < $testPartCount; $i++) {
            $partNum = $i + 1;
            $assessmentItemRefs = new AssessmentItemRefCollection();

            for ($j = 0; $j < $itemCount; $j++) {
                $itemNum = $j + 1 + ($itemCount * $i);
                $assessmentItemRefs[] = new AssessmentItemRef("Q${itemNum}", "Q${itemNum}.xml");
            }

            $assessmentSections = new AssessmentSectionCollection();
            $assessmentSections[] = new AssessmentSection("S${partNum}", "Section ${partNum}", true);
            $assessmentSections["S${partNum}"]->setSectionParts($assessmentItemRefs);

            $testParts = new TestPartCollection();
            $testParts[] = new TestPart("T${partNum}", $assessmentSections);

            for ($j = 0; $j < count($assessmentItemRefs); $j++) {
                $itemNum = $j + 1 + ($itemCount * $i);
                $route->addRouteItem($assessmentItemRefs["Q${itemNum}"], $assessmentSections["S${partNum}"], $testParts["T${partNum}"], $assessmentTest);
            }
        }

        $assessmentTest->setTestParts($testParts);

        return $route;
    }
}
