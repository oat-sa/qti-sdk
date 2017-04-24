<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 07.04.17
 * Time: 14:04
 */

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\BranchRuleTargetException;
use qtism\runtime\tests\RouteItemCollection;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

class AssessmentTestSessionPossibleRoutesTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testgetPossibleRoutes()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath_v2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes2 = [];

        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[3], $it[4], $it[5]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[2], $it[3], $it[4], $it[5]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[1], $it[3], $it[4], $it[5]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[5]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[2], $it[5]]));

        foreach ($possibleRoutes as $path) {
            $possibleRoutes2[] = $path->getArrayCopy();
        }

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));
        $this->assertEquals($possibleRoutes2, $route->getPossibleRoutes(true));

        // Test with no item

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/assessmentwithnoitem.xml');
        $route = $session->getRoute();

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection();
        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        // Test with item's branching targeting on section

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsecttarget.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[3], $it[4], $it[5]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[1], $it[3], $it[4], $it[5]]));
        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        // Tests with subsections

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 5; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[2], $it[3], $it[4]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[4]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[2], $it[4]]));

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections3.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 5; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[2], $it[3], $it[4]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[4]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[2], $it[4]]));
        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections4.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 5; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[2], $it[3], $it[4]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[4]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[0], $it[2], $it[4]]));
        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));
    }

    public function testPossibleRouteswithPreCondition()
    {
        // Case 1

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 5; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[0], $it[2], $it[3], $it[4]]);
        $possibleRoutes[] = new RouteItemCollection([$it[0], $it[1], $it[2], $it[4]]);
        $possibleRoutes[] = new RouteItemCollection([$it[0], $it[2], $it[4]]);
        $possibleRoutes[] = new RouteItemCollection([$it[0], $it[1], $it[2], $it[3]]);
        $possibleRoutes[] = new RouteItemCollection([$it[0], $it[2], $it[3]]);
        $possibleRoutes[] = new RouteItemCollection([$it[0], $it[1], $it[2]]);
        $possibleRoutes[] = new RouteItemCollection([$it[0], $it[2]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        // Case with duplicates

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/testnoduplicatepaths.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 3; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[0], $it[2]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));
    }

    public function testPossibleRoutesWitPreOnSectionsAndTPs()
    {
        // Case with testParts and sections

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre2.xml');
        $route = $session->getRoute();

        $it = [];

        for ($i = 0; $i <= 7; $i++) {
            $it[$i + 1] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre3.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i <= 7; $i++) {
            $it[$i + 1] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[4], $it[5], $it[6], $it[7], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[4]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));
    }

    public function testPossibleRoutesWitPreOnSubSections()
    {
        // Case with subsections

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 9; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[8], $it[9]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7], $it[9]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[9]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));
    }

    public function testPossiblePathWithSectsAndTPs()
    {
        // Testing branching on sections

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsections.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 7; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[6], $it[7]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5], $it[6], $it[7]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        // Testing branching on testparts and sections

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingtestparts.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i < 9; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[6], $it[7], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5], $it[6], $it[7], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[6], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5], $it[6], $it[8]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));
    }

    // Testing special cases

    public function testRecursiveBranching()
    {
        $this->expectException(BranchRuleTargetException::class);
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingrecursive.xml');
        $route = $session->getRoute();
        $route->getPossibleRoutes(false);
    }

    public function testRecursiveBranching2()
    {
        $this->expectException(BranchRuleTargetException::class);
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingstartandend3.xml');
        $route = $session->getRoute();
        $route->getPossibleRoutes(false);
    }

    public function testRecursiveBranching3()
    {
        $this->expectException(BranchRuleTargetException::class);
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingstartandend4.xml');
        $route = $session->getRoute();
        $route->getPossibleRoutes(false);
    }

    public function testRecursiveBranching4()
    {
        $this->expectException(BranchRuleTargetException::class);
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingstartandend5.xml');
        $route = $session->getRoute();
        $route->getPossibleRoutes(false);
    }

    public function testRecursiveBranching5()
    {
        $this->expectException(BranchRuleTargetException::class);
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingstartandend6.xml');
        $route = $session->getRoute();
        $route->getPossibleRoutes(false);
    }

    public function testBackwardBranching()
    {
        $this->expectException(BranchRuleTargetException::class);
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingbackward.xml');
        $route = $session->getRoute();
        $route->getPossibleRoutes(false);
    }

    public function testWrongTargetBranching()
    {
        $this->expectException(BranchRuleTargetException::class);
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingwrongtarget.xml');
        $route = $session->getRoute();
        $route->getPossibleRoutes(false);
    }

    public function testgetPossibleRoutesWithExitMentions()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingwithexitmentions.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 9; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[6], $it[7], $it[8], $it[9]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7], $it[8]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[6], $it[7], $it[8]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingexitsession.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 6; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingwithexitmentions2.xml');
        $route = $session->getRoute();
        $possibleRoutes = [];
        $it = [];

        for ($i = 1; $i <= 7; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[6], $it[7]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));
    }

    public function testGetShortestRoutes()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath_v2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $shortestRoutes = [];
        $path = new RouteItemCollection([$it[0], $it[2], $it[5]]);
        $shortestRoutes[] = $path;

        $this->assertEquals($shortestRoutes, $route->getShortestRoutes());

        // With multiple shortest paths

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/multipleshortpaths.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $shortestRoutes = [];
        $path1 = new RouteItemCollection([$it[0], $it[1], $it[2], $it[3], $it[5]]);
        $path2 = new RouteItemCollection([$it[0], $it[1], $it[2], $it[4], $it[5]]);
        $shortestRoutes[] = $path2;
        $shortestRoutes[] = $path1;

        $this->assertEquals($shortestRoutes, $route->getShortestRoutes());
    }

    public function testGetLongestRoutes()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath_v2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $longestRoutes[] = new RouteItemCollection($it);

        $this->assertEquals($longestRoutes, $route->getLongestRoutes());
    }

    public function testGetFirstItem()
    {
        // Simple cases

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre2.xml');
        $route = $session->getRoute();
        $test = $route->getRouteItemAt(0)->getAssessmentTest();

        $this->assertEquals($test->getComponentByIdentifier('Q01'), $route->getFirstItem($test->getComponentByIdentifier('Q01')));
        $this->assertEquals($test->getComponentByIdentifier('Q03'), $route->getFirstItem($test->getComponentByIdentifier('S02')));
        $this->assertEquals($test->getComponentByIdentifier('Q05'), $route->getFirstItem($test->getComponentByIdentifier('TP02')));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections.xml');
        $route = $session->getRoute();
        $test = $route->getRouteItemAt(0)->getAssessmentTest();

        $this->assertEquals($test->getComponentByIdentifier('Q05'), $route->getFirstItem($test->getComponentByIdentifier('S04')));
        $this->assertEquals($test->getComponentByIdentifier('Q03'), $route->getFirstItem($test->getComponentByIdentifier('S03')));
        $this->assertEquals($test->getComponentByIdentifier('Q01'), $route->getFirstItem($test->getComponentByIdentifier('TP01')));
        $this->assertEquals(null, $route->getFirstItem($test));
    }

    public function testGetLastItem()
    {
        // Simple cases

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre2.xml');
        $route = $session->getRoute();
        $test = $route->getRouteItemAt(0)->getAssessmentTest();

        $this->assertEquals($test->getComponentByIdentifier('Q01'), $route->getLastItem($test->getComponentByIdentifier('Q01')));
        $this->assertEquals($test->getComponentByIdentifier('Q04'), $route->getLastItem($test->getComponentByIdentifier('S02')));
        $this->assertEquals($test->getComponentByIdentifier('Q08'), $route->getLastItem($test->getComponentByIdentifier('TP02')));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections.xml');
        $route = $session->getRoute();
        $test = $route->getRouteItemAt(0)->getAssessmentTest();

        $this->assertEquals($test->getComponentByIdentifier('Q09'), $route->getLastItem($test->getComponentByIdentifier('S04')));
        $this->assertEquals($test->getComponentByIdentifier('Q04'), $route->getLastItem($test->getComponentByIdentifier('S03')));
        $this->assertEquals($test->getComponentByIdentifier('Q09'), $route->getLastItem($test->getComponentByIdentifier('TP01')));
        $this->assertEquals(null, $route->getLastItem($test));
    }

    public function testgetPossibleRoutesFromCurrentPosition()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath_v2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes2 = [];
        $possibleRoutes3 = [];

        $possibleRoutes[] = new RouteItemCollection(([$it[2], $it[3], $it[4], $it[5]]));
        $possibleRoutes[] = new RouteItemCollection(([$it[2], $it[5]]));

        $possibleRoutes2[] = new RouteItemCollection(([$it[5]]));

        $possibleRoutes3[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[3], $it[4], $it[5]]));
        $possibleRoutes3[] = new RouteItemCollection(([$it[0], $it[2], $it[3], $it[4], $it[5]]));
        $possibleRoutes3[] = new RouteItemCollection(([$it[0], $it[1], $it[3], $it[4], $it[5]]));
        $possibleRoutes3[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[5]]));
        $possibleRoutes3[] = new RouteItemCollection(([$it[0], $it[2], $it[5]]));

        $routes = $route->getPossibleRoutes();

        $route->setPosition(2);
        $this->assertEquals($possibleRoutes, $route->getPossibleRoutesFromCurrentPosition($routes));

        $route->setPosition(5);
        $this->assertEquals($possibleRoutes2, $route->getPossibleRoutesFromCurrentPosition($routes));

        $route->setPosition(0);
        $this->assertEquals($possibleRoutes3, $route->getPossibleRoutesFromCurrentPosition($routes));
    }

    public function testgetShortestRoutesFromCurrentPosition()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath_v2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $shortestRoutes = [];
        $shortestRoutes2 = [];
        $shortestRoutes3 = [];
        $shortestRoutes4 = [];

        $shortestRoutes[] = new RouteItemCollection(([$it[2], $it[5]]));
        $shortestRoutes2[] = new RouteItemCollection(([$it[5]]));
        $shortestRoutes3[] = new RouteItemCollection(([$it[0], $it[2], $it[5]]));
        $shortestRoutes4[] = new RouteItemCollection(([$it[1], $it[2], $it[5]]));

        $routes = $route->getPossibleRoutes();

        $route->setPosition(2);
        $this->assertEquals($shortestRoutes, $route->getShortestRoutesFromCurrentPosition($routes));

        $route->setPosition(5);
        $this->assertEquals($shortestRoutes2, $route->getShortestRoutesFromCurrentPosition($routes));

        $route->setPosition(0);
        $this->assertEquals($shortestRoutes3, $route->getShortestRoutesFromCurrentPosition($routes));

        $route->setPosition(1);
        $this->assertEquals($shortestRoutes4, $route->getShortestRoutesFromCurrentPosition($routes));

        // With multiple shortest paths

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/multipleshortpaths.xml');
        $route = $session->getRoute();
        $routes = $route->getPossibleRoutes();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $shortestRoutes = [];
        $path1 = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5]]);
        $path2 = new RouteItemCollection([$it[1], $it[2], $it[4], $it[5]]);
        $shortestRoutes[] = $path2;
        $shortestRoutes[] = $path1;

        $route->setPosition(1);
        $this->assertEquals($shortestRoutes, $route->getShortestRoutesFromCurrentPosition($routes));
    }

    public function testgetLongestRoutesFromCurrentPosition()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath_v2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possibleRoutes = [];
        $possibleRoutes2 = [];
        $possibleRoutes3 = [];

        $possibleRoutes[] = new RouteItemCollection(([$it[2], $it[3], $it[4], $it[5]]));

        $possibleRoutes2[] = new RouteItemCollection(([$it[5]]));

        $possibleRoutes3[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[3], $it[4], $it[5]]));

        $routes = $route->getPossibleRoutes();

        $route->setPosition(2);
        $this->assertEquals($possibleRoutes, $route->getLongestRoutesFromCurrentPosition($routes));

        $route->setPosition(5);
        $this->assertEquals($possibleRoutes2, $route->getLongestRoutesFromCurrentPosition($routes));

        $route->setPosition(0);
        $this->assertEquals($possibleRoutes3, $route->getLongestRoutesFromCurrentPosition($routes));
    }

    public function testAlwaysTrueorFalseBranches()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 6; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[4], $it[5], $it[6]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[6]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[3], $it[6]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath_v4.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 6; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[4], $it[5], $it[6]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[6]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[3], $it[6]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath_v3.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 6; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection($it);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[3], $it[4], $it[5], $it[6]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[4], $it[5], $it[6]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/multipletruebranching.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 8; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[4], $it[5], $it[7], $it[8]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));
    }

    public function testAlwaysTrueOrFalsePres()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/preconditionpath.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 6; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[6]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[4], $it[5]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/preconditionpath_v2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 6; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/preconditionsonsections.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 5; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[3], $it[5]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[3]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/preconditionsonsections2.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 5; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[5]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2], $it[3]]);
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[2]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));

        // Unreachable item (always FALSE precondition) with a branch : the branch should not be taken

        /*
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingunreachable.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 7; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possibleRoutes = [];
        $possibleRoutes[] = new RouteItemCollection([$it[1], $it[3], $it[4], $it[7]]);

        $this->assertEquals($possibleRoutes, $route->getPossibleRoutes(false));*/
    }
}