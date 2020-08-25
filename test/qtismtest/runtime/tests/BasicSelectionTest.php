<?php

namespace qtismtest\runtime\tests;

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\BasicSelection;
use qtism\runtime\tests\SelectableRoute;
use qtism\runtime\tests\SelectableRouteCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class BasicSelectionTest
 *
 * @package qtismtest\runtime\tests
 */
class BasicSelectionTest extends QtiSmTestCase
{
    public function testBasicSelection()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_ordering/selection_and_ordering.xml');

        $testPart = $doc->getDocumentComponent()->getComponentByIdentifier('testPart');
        $this->assertEquals('testPart', $testPart->getIdentifier());

        $s01 = $doc->getDocumentComponent()->getComponentByIdentifier('S01', true);
        $this->assertEquals('S01', $s01->getIdentifier());

        // Prepare route selection of S01A.
        $s01a = $doc->getDocumentComponent()->getComponentByIdentifier('S01A', true);
        $this->assertEquals('S01A', $s01a->getIdentifier());

        $s01aRoute = new SelectableRoute();
        foreach ($s01a->getSectionParts() as $sectionPart) {
            $s01aRoute->addRouteItem($sectionPart, $s01a, $testPart, $doc->getDocumentComponent());
        }

        // Prepare route selection of S01B.
        $s01b = $doc->getDocumentComponent()->getComponentByIdentifier('S01B', true);
        $this->assertEquals('S01B', $s01b->getIdentifier());

        $s01bRoute = new SelectableRoute();
        foreach ($s01b->getSectionParts() as $sectionPart) {
            $s01bRoute->addRouteItem($sectionPart, $s01b, $testPart, $doc->getDocumentComponent());
        }

        $selection = new BasicSelection($s01, new SelectableRouteCollection([$s01aRoute, $s01bRoute]));
        $selectedRoutes = $selection->select();

        $selectedRoute = new SelectableRoute();
        foreach ($selectedRoutes as $r) {
            $selectedRoute->appendRoute($r);
        }

        $routeCheck1 = self::isRouteCorrect($selectedRoute, ['Q1', 'Q2', 'Q3']);
        $routeCheck2 = self::isRouteCorrect($selectedRoute, ['Q4', 'Q5', 'Q6']);

        $this->assertFalse($routeCheck1 === true && $routeCheck2 === true);
        $this->assertTrue($routeCheck1 === true || $routeCheck2 === true);
    }

    /**
     * @param SelectableRoute $route
     * @param array $expectedIdentifiers
     * @return bool
     */
    private static function isRouteCorrect(SelectableRoute $route, array $expectedIdentifiers)
    {
        $i = 0;
        foreach ($route as $routeItem) {
            if ($routeItem->getAssessmentItemRef()->getIdentifier() !== $expectedIdentifiers[$i]) {
                return false;
            }

            $i++;
        }

        return true;
    }
}
