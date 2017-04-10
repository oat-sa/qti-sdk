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

class AssessmentTestSessionPossiblePathsTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testgetPossibleRoutes()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath.xml');
        $route = $session->getRoute();
        $it = [];
        
        for($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possible_paths = [];
        $possible_paths2 = [];

        $possible_paths[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[3], $it[4], $it[5]]));
        $possible_paths[] = new RouteItemCollection(([$it[0], $it[2], $it[3], $it[4], $it[5]]));
        $possible_paths[] = new RouteItemCollection(([$it[0], $it[1], $it[3], $it[4], $it[5]]));
        $possible_paths[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[5]]));
        $possible_paths[] = new RouteItemCollection(([$it[0], $it[2], $it[5]]));
        
        foreach ($possible_paths as $path) {
            $possible_paths2[] = $path->getArrayCopy();
        }

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));
        $this->assertEquals($possible_paths2, $route->getPossibleRoutes(true));

        // Test with no item

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/assessmentwithnoitem.xml');
        $route = $session->getRoute();

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection();
        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));


        // Test with item's branching targeting on section

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsecttarget.xml');
        $route = $session->getRoute();
        $it = [];

        for($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection(([$it[0], $it[1], $it[2], $it[3], $it[4], $it[5]]));
        $possible_paths[] = new RouteItemCollection(([$it[0], $it[1], $it[3], $it[4], $it[5]]));
        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));
    }

    public function testPossiblePathswithPreCondition()
    {
        // Case 1

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre.xml');
        $route = $session->getRoute();
        $it = [];

        for($i = 0; $i < 5; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection($it);
        $possible_paths[] = new RouteItemCollection([$it[0], $it[2], $it[3], $it[4]]);
        $possible_paths[] = new RouteItemCollection([$it[0], $it[1], $it[2], $it[4]]);
        $possible_paths[] = new RouteItemCollection([$it[0], $it[2], $it[4]]);
        $possible_paths[] = new RouteItemCollection([$it[0], $it[1], $it[2], $it[3]]);
        $possible_paths[] = new RouteItemCollection([$it[0], $it[2], $it[3]]);
        $possible_paths[] = new RouteItemCollection([$it[0], $it[1], $it[2]]);
        $possible_paths[] = new RouteItemCollection([$it[0], $it[2]]);

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));

        // Case with duplicates

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/testnoduplicatepaths.xml');
        $route = $session->getRoute();
        $it = [];

        for($i = 0; $i < 3; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection($it);
        $possible_paths[] = new RouteItemCollection([$it[0], $it[2]]);

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));
    }

    public function testPossiblePathsWitPreOnSectionsAndTPs()
    {
        // Case with testParts and sections

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre2.xml');
        $route = $session->getRoute();

        $it = [];

        for ($i = 0; $i <= 7; $i++) {
            $it[$i + 1] = $route->getRouteItemAt($i);
        }

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection($it);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[8]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7]]);

        // var_dump($possible_paths);
        // var_dump($route->getPossibleRoutes(false));
        // $route->getPossibleRoutes(false);


        // $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));
    }

    public function testPossiblePathsWitPreOnSubSections()
    {
        /*
        // Case with subsections
        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections.xml');
        $route = $session->getRoute();
        $it = array();

        for ($i = 1; $i <= 9; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);
        }

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7], $it[8]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[8]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7]]);
        $possible_paths[] = new RouteItemCollection($it);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[8], $it[9]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[9]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[9]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7], $it[9]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[5], $it[6], $it[7], $it[9]]);

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));*/
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

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection($it);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[6], $it[7]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5], $it[6], $it[7]]);

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));

        // Testing branching on testparts and sections


        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingtestparts.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i < 9; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);
        }

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection($it);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[6], $it[7], $it[8]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5], $it[6], $it[7], $it[8]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[8]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[6], $it[8]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[5], $it[6], $it[8]]);

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));
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
        /*
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingwithexitmentions.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 9; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection($it);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[6], $it[7], $it[8], $it[9]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[5], $it[6], $it[7], $it[8]]);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[6], $it[7], $it[8]]);

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingexitsession.xml');
        $route = $session->getRoute();
        $it = [];

        for ($i = 1; $i <= 6; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possible_paths = [];
        $possible_paths[] = new RouteItemCollection($it);

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));

        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingwithexitmentions2.xml');
        $route = $session->getRoute();
        $possible_paths = [];
        $it = [];

        for ($i = 1; $i <= 7; $i++) {
            $it[$i] = $route->getRouteItemAt($i - 1);;
        }

        $possible_paths[] = new RouteItemCollection($it);
        $possible_paths[] = new RouteItemCollection([$it[1], $it[2], $it[3], $it[4], $it[6], $it[7]]);

        $this->assertEquals($possible_paths, $route->getPossibleRoutes(false));*/
    }

    public function testGetShortestRoutes()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath.xml');
        $route = $session->getRoute();
        $it = [];

        for($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $shortest_paths = [];
        $path = new RouteItemCollection([$it[0], $it[2], $it[5]]);
        $shortest_paths[] = $path;

        $this->assertEquals($shortest_paths, $route->getShortestRoutes());

        // With multiple shortest paths
        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/multipleshortpaths.xml');
        $route = $session->getRoute();
        $it = [];

        for($i = 0; $i < 4; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $shortest_paths = [];
        $path1 = new RouteItemCollection([$it[0], $it[1], $it[3]]);
        $path2 = new RouteItemCollection([$it[0], $it[2], $it[3]]);
        $shortest_paths[] = $path2;
        $shortest_paths[] = $path1;

        $this->assertEquals($shortest_paths, $route->getShortestRoutes());
    }

    public function testGetLongestPaths()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpath.xml');
        $route = $session->getRoute();
        $it = [];

        for($i = 0; $i < 6; $i++) {
            $it[] = $route->getRouteItemAt($i);
        }

        $longest_paths[] = new RouteItemCollection($it);

        $this->assertEquals($longest_paths, $route->getLongestRoutes());
    }

    public function testGetFirstItem()
    {
        /*
        // Simple cases

        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre2.xml');
        $route = $session->getRoute();
        $sections = $route->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($route->getComponentByIdentifier('Q01'),
            DataUtils::getFirstItem($route, $route->getComponentByIdentifier('Q01'), $sections));
        $this->assertEquals($route->getComponentByIdentifier('Q03'),
            DataUtils::getFirstItem($route, $route->getComponentByIdentifier('S02'), $sections));
        $this->assertEquals($route->getComponentByIdentifier('Q05'),
            DataUtils::getFirstItem($route, $route->getComponentByIdentifier('TP02'), $sections));

        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections.xml');
        $route = $session->getRoute();
        $sections = $route->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($route->getComponentByIdentifier('Q05'),
            DataUtils::getFirstItem($route, $route->getComponentByIdentifier('S04'), $sections));
        $this->assertEquals($route->getComponentByIdentifier('Q03'),
            DataUtils::getFirstItem($route, $route->getComponentByIdentifier('S03'), $sections));
        $this->assertEquals($route->getComponentByIdentifier('Q01'),
            DataUtils::getFirstItem($route, $route->getComponentByIdentifier('TP01'), $sections));
        $this->assertEquals(null, DataUtils::getFirstItem($route, $route, $sections));*/
    }

    public function testgetFirstItem2()
    {
        /*
        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingtestparts.xml');
        $route = $session->getRoute();
        $sections = $route->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($route->getComponentByIdentifier('Q06'),
            DataUtils::getFirstItem($route, $route->getComponentByIdentifier('TP03'), $sections));*/
    }

    public function testGetLastItem()
    {
        // Simple cases
/*
        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingpathwithpre2.xml');
        $route = $session->getRoute();
        $sections = $route->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($route->getComponentByIdentifier('Q01'),
            DataUtils::getLastItem($route, $route->getComponentByIdentifier('Q01'), $sections));
        $this->assertEquals($route->getComponentByIdentifier('Q04'),
            DataUtils::getLastItem($route, $route->getComponentByIdentifier('S02'), $sections));
        $this->assertEquals($route->getComponentByIdentifier('Q08'),
            DataUtils::getLastItem($route, $route->getComponentByIdentifier('TP02'), $sections));

        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingsubsections.xml');
        $route = $session->getRoute();
        $sections = $route->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($route->getComponentByIdentifier('Q09'),
            DataUtils::getLastItem($route, $route->getComponentByIdentifier('S04'), $sections));
        $this->assertEquals($route->getComponentByIdentifier('Q04'),
            DataUtils::getLastItem($route, $route->getComponentByIdentifier('S03'), $sections));
        $this->assertEquals($route->getComponentByIdentifier('Q09'),
            DataUtils::getLastItem($route, $route->getComponentByIdentifier('TP01'), $sections));
        $this->assertEquals(null, DataUtils::getLastItem($route, $route, $sections));*/
    }

    public function testgetLastItem2()
    {
        /*
        
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/possiblepaths/branchingtestparts.xml');
        $route = $session->getRoute();
        $sections = $route->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($route->getComponentByIdentifier('Q05'),
            DataUtils::getLastItem($route, $route->getComponentByIdentifier('TP03'), $sections));*/
    }
}