<?php

namespace qtismtest\runtime\tests;

use OutOfBoundsException;
use OutOfRangeException;
use qtism\common\collections\IdentifierCollection;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\ExtendedAssessmentTest;
use qtism\data\ExtendedTestPart;
use qtism\data\SectionPartCollection;
use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;
use qtism\data\TestPart;
use qtism\data\TestPartCollection;
use qtism\runtime\tests\Route;
use qtism\runtime\tests\RouteItem;
use qtismtest\QtiSmRouteTestCase;

class RouteTest extends QtiSmRouteTestCase
{
    public function testRouteTest()
    {
        $assessmentSections = new AssessmentSectionCollection();
        $assessmentSections[] = new AssessmentSection('S1', 'Section 1', true);
        $assessmentSections[] = new AssessmentSection('S2', 'Section 2', true);

        $q1 = new AssessmentItemRef('Q1', 'Q1.xml');
        $q1->setCategories(new IdentifierCollection(['mathematics', 'expert']));
        $q2 = new AssessmentItemRef('Q2', 'Q2.xml');
        $q2->setCategories(new IdentifierCollection(['sciences', 'expert']));
        $q3 = new AssessmentItemRef('Q3', 'Q3.xml');
        $q3->setCategories(new IdentifierCollection(['mathematics']));
        $q4 = new AssessmentItemRef('Q4', 'Q4.xml');
        $sectionPartsS1 = new SectionPartCollection([$q1, $q2, $q3, $q4]);
        $assessmentSections['S1']->setSectionParts($sectionPartsS1);

        $q5 = new AssessmentItemRef('Q5', 'Q5.xml');
        $q6 = new AssessmentItemRef('Q6', 'Q6.xml');
        $q6->setCategories(new IdentifierCollection(['mathematics']));
        $sectionPartsS2 = new SectionPartCollection([$q5, $q6]);
        $assessmentSections['S2']->setSectionParts($sectionPartsS2);

        $testPart = new TestPart('TP1', $assessmentSections);
        $testPart->setAssessmentSections($assessmentSections);
        $assessmentTest = new AssessmentTest('test', 'A Test', new TestPartCollection([$testPart]));

        $route = new Route();
        $route->addRouteItem($sectionPartsS1['Q1'], $assessmentSections['S1'], $testPart, $assessmentTest);
        $route->addRouteItem($sectionPartsS1['Q2'], $assessmentSections['S1'], $testPart, $assessmentTest);
        $route->addRouteItem($sectionPartsS1['Q3'], $assessmentSections['S1'], $testPart, $assessmentTest);
        $route->addRouteItem($sectionPartsS1['Q4'], $assessmentSections['S1'], $testPart, $assessmentTest);
        $route->addRouteItem($sectionPartsS2['Q5'], $assessmentSections['S2'], $testPart, $assessmentTest);
        $route->addRouteItem($sectionPartsS2['Q6'], $assessmentSections['S2'], $testPart, $assessmentTest);

        $this->assertEquals('Q1', $route->getFirstRouteItem()->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('Q6', $route->getLastRouteItem()->getAssessmentItemRef()->getIdentifier());

        // Is Q3 in TP1?
        $this->assertTrue($route->isInTestPart(2, $testPart));

        // What are the RouteItem objects involved in each AssessmentItemRef ?
        $involved = $route->getRouteItemsByAssessmentItemRef($sectionPartsS1['Q1']);
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q1', $involved[0]->getAssessmentItemRef()->getIdentifier());

        $involved = $route->getRouteItemsByAssessmentItemRef($sectionPartsS1['Q2']);
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q2', $involved[0]->getAssessmentItemRef()->getIdentifier());

        $involved = $route->getRouteItemsByAssessmentItemRef($sectionPartsS1['Q3']);
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q3', $involved[0]->getAssessmentItemRef()->getIdentifier());

        $involved = $route->getRouteItemsByAssessmentItemRef($sectionPartsS1['Q4']);
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q4', $involved[0]->getAssessmentItemRef()->getIdentifier());

        $involved = $route->getRouteItemsByAssessmentItemRef($sectionPartsS2['Q5']);
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q5', $involved[0]->getAssessmentItemRef()->getIdentifier());

        $involved = $route->getRouteItemsByAssessmentItemRef($sectionPartsS2['Q6']);
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q6', $involved[0]->getAssessmentItemRef()->getIdentifier());

        // What are the RouteItem objects involded in part 'TP1'?
        $tp1RouteItems = $route->getRouteItemsByTestPart($testPart);
        $this->assertEquals(6, count($tp1RouteItems));
        $tp1RouteItems = $route->getRouteItemsByTestPart('TP1');
        $this->assertEquals(6, count($tp1RouteItems));

        try {
            $tp1RouteItems = $route->getRouteItemsByTestPart('TPX');
            $this->assertFalse(true);
        } catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }

        // What are the RouteItems objects involved in section 'S1'?
        $s1RouteItems = $route->getRouteItemsByAssessmentSection($assessmentSections['S1']);
        $this->assertEquals(4, count($s1RouteItems));
        $this->assertEquals('Q1', $s1RouteItems[0]->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('Q2', $s1RouteItems[1]->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('Q3', $s1RouteItems[2]->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('Q4', $s1RouteItems[3]->getAssessmentItemRef()->getIdentifier());

        // What are the RouteItems objects involved in section 'S2'?
        $s2RouteItems = $route->getRouteItemsByAssessmentSection('S2');
        $this->assertEquals(2, count($s2RouteItems));
        $this->assertEquals('Q5', $s2RouteItems[0]->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('Q6', $s2RouteItems[1]->getAssessmentItemRef()->getIdentifier());

        // What are the RouteItems objects involded in an unknown section :-D ?
        // An OutOfBoundsException must be thrown.
        try {
            $sXRouteItems = $route->getRouteItemsByAssessmentSection(new AssessmentSection('SX', 'Unknown Section', true));
            $this->assertTrue(false, 'An exception must be thrown because the AssessmentSection object is not known by the Route.');
        } catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }

        // Only 1 one occurence of each selected item found?
        foreach (array_merge($sectionPartsS1->getArrayCopy(), $sectionPartsS2->getArrayCopy()) as $itemRef) {
            $this->assertEquals(1, $route->getOccurenceCount($itemRef));
        }

        $assessmentItemRefs = $route->getAssessmentItemRefs();
        $this->assertEquals(6, count($assessmentItemRefs));

        // test to retrieve items by category.
        $mathRefs = $route->getAssessmentItemRefsByCategory('mathematics');
        $this->assertEquals(3, count($mathRefs));

        $sciencesRefs = $route->getAssessmentItemRefsByCategory('sciences');
        $this->assertEquals(1, count($sciencesRefs));

        $mathAndSciences = $route->getAssessmentItemRefsByCategory(new IdentifierCollection(['mathematics', 'sciences']));
        $this->assertEquals(4, count($mathAndSciences));

        $expertRefs = $route->getAssessmentItemRefsByCategory('expert');
        $this->assertEquals(2, count($expertRefs));

        // test to retrieve items by section.
        $section1Refs = $route->getAssessmentItemRefsBySection('S1');
        $this->assertEquals(4, count($section1Refs));

        $section2Refs = $route->getAssessmentItemRefsBySection('S2');
        $this->assertEquals(2, count($section2Refs));

        // test to retrieve items by section/category.
        $section1Refs = $route->getAssessmentItemRefsSubset('S1');
        $this->assertEquals(4, count($section1Refs));

        $mathRefs = $route->getAssessmentItemRefsSubset('', new IdentifierCollection(['mathematics']));
        $this->assertEquals(3, count($mathRefs));

        $s1MathRefs = $route->getAssessmentItemRefsSubset('S1', new IdentifierCollection(['mathematics']));
        $this->assertEquals(2, count($s1MathRefs));

        // go by exclusion.
        $exclusionRefs = $route->getAssessmentItemRefsSubset('', null, new IdentifierCollection(['sciences', 'expert']));
        $this->assertEquals(4, count($exclusionRefs));
        $this->assertTrue(isset($exclusionRefs['Q3']));
        $this->assertTrue(isset($exclusionRefs['Q4']));
        $this->assertTrue(isset($exclusionRefs['Q5']));
        $this->assertTrue(isset($exclusionRefs['Q6']));
    }

    public function testOccurences()
    {
        $assessmentItemRefs = new AssessmentItemRefCollection();
        $assessmentItemRefs[] = new AssessmentItemRef('Q1', 'Q1.xml');
        $assessmentItemRefs[] = new AssessmentItemRef('Q2', 'Q2.xml');
        $assessmentItemRefs[] = new AssessmentItemRef('Q3', 'Q3.xml');

        $assessmentSections = new AssessmentSectionCollection();
        $assessmentSections[] = new AssessmentSection('S1', 'Section 1', true);
        $assessmentSections['S1']->setSectionParts($assessmentItemRefs);

        $testParts = new TestPartCollection();
        $testParts[] = new TestPart('T1', $assessmentSections);
        $assessmentTest = new AssessmentTest('test', 'A Test', $testParts);

        $route = new Route();

        $route->addRouteItem($assessmentItemRefs['Q1'], $assessmentSections['S1'], $testParts['T1'], $assessmentTest);
        $route->addRouteItem($assessmentItemRefs['Q2'], $assessmentSections['S1'], $testParts['T1'], $assessmentTest);
        $route->addRouteItem($assessmentItemRefs['Q3'], $assessmentSections['S1'], $testParts['T1'], $assessmentTest);

        $this->assertEquals(1, $route->getOccurenceCount($assessmentItemRefs['Q1']));
        $this->assertEquals(1, $route->getOccurenceCount($assessmentItemRefs['Q2']));
        $this->assertEquals(1, $route->getOccurenceCount($assessmentItemRefs['Q3']));

        $route->addRouteItem($assessmentItemRefs['Q3'], $assessmentSections['S1'], $testParts['T1'], $assessmentTest);
        $this->assertEquals(2, $route->getOccurenceCount($assessmentItemRefs['Q3']));

        // Get the second route item in the route.
        $routeItem2 = $route->getRouteItemAt(1);
        $this->assertEquals('Q2', $routeItem2->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $routeItem2->getOccurence());

        $routeItem3 = $route->getRouteItemAt(2);
        $this->assertEquals('Q3', $routeItem3->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $routeItem3->getOccurence());

        $routeItem4 = $route->getRouteItemAt(3);
        $this->assertEquals('Q3', $routeItem4->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(1, $routeItem4->getOccurence());
    }

    public function testIsX()
    {
        $route = self::buildSimpleRoute();

        // Q1
        $this->assertTrue($route->isNavigationLinear());
        $this->assertFalse($route->isNavigationNonLinear());
        $this->assertTrue($route->isSubmissionIndividual());
        $this->assertFalse($route->isSubmissionSimultaneous());
        $this->assertTrue($route->isFirst());
        $this->assertFalse($route->isLast());
        $route->next();

        // Q2
        $this->assertTrue($route->isNavigationLinear());
        $this->assertFalse($route->isNavigationNonLinear());
        $this->assertTrue($route->isSubmissionIndividual());
        $this->assertFalse($route->isSubmissionSimultaneous());
        $this->assertFalse($route->isFirst());
        $this->assertFalse($route->isLast());
        $route->next();

        // Q3
        $this->assertTrue($route->isNavigationLinear());
        $this->assertFalse($route->isNavigationNonLinear());
        $this->assertTrue($route->isSubmissionIndividual());
        $this->assertFalse($route->isSubmissionSimultaneous());
        $this->assertFalse($route->isFirst());
        $this->assertTrue($route->isLast());

        $route->next();
        $this->assertFalse($route->valid());
    }

    public function testPreviousNext()
    {
        $route = self::buildSimpleRoute();
        $this->assertEquals(0, $route->getPosition());

        // We are at first position, nothing should happen.
        // Q1
        $route->previous();
        $this->assertEquals(0, $route->getPosition());
        $this->assertEquals('Q1', $route->current()->getAssessmentItemRef()->getIdentifier());

        // go to Q2
        $route->next();
        $this->assertEquals(1, $route->getPosition());
        $this->assertEquals('Q2', $route->current()->getAssessmentItemRef()->getIdentifier());

        // go to Q3
        $route->next();
        $this->assertEquals(2, $route->getPosition());
        $this->assertEquals('Q3', $route->current()->getAssessmentItemRef()->getIdentifier());

        // go back to Q2
        $route->previous();
        $this->assertEquals(1, $route->getPosition());
        $this->assertEquals('Q2', $route->current()->getAssessmentItemRef()->getIdentifier());

        // go to Q3
        $route->next();
        $this->assertEquals('Q3', $route->current()->getAssessmentItemRef()->getIdentifier());

        // go beyond the digital nirvana, end of test.
        $route->next();
        $this->assertFalse($route->valid());
    }

    public function testGetNext()
    {
        $route = self::buildSimpleRoute();

        // Q1 - First position.
        $nextItem = $route->getNext();
        $this->assertEquals('Q2', $nextItem->getAssessmentItemRef()->getIdentifier());
        $route->next();

        // Q2 - Second position.
        $nextItem = $route->getNext();
        $this->assertEquals('Q3', $nextItem->getAssessmentItemRef()->getIdentifier());
        $route->next();

        // Q3 - Thrid position, there is no next route item.
        $this->expectException(OutOfBoundsException::class);
        $nextItem = $route->getNext();
    }

    public function testGetPrevious()
    {
        $route = self::buildSimpleRoute();
        $route->next();

        // Q2 - Second postion.
        $previousItem = $route->getPrevious();
        $this->assertEquals('Q1', $previousItem->getAssessmentItemRef()->getIdentifier());
        $route->next();

        // Q3 - Third position.
        $previousItem = $route->getPrevious();
        $this->assertEquals('Q2', $previousItem->getAssessmentItemRef()->getIdentifier());

        // Go to Q1 to test exception.
        $route->previous();
        $route->previous();

        $this->assertEquals('Q1', $route->current()->getAssessmentItemRef()->getIdentifier());
        $this->expectException(OutOfBoundsException::class);
        $route->getPrevious();
    }

    public function testGetCurrentTestPartRouteItems()
    {
        $route = self::buildSimpleRoute();
        $routeItems = $route->getCurrentTestPartRouteItems();
        $this->assertEquals(3, count($routeItems));
        $this->assertEquals('Q1', $routeItems[0]->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('Q2', $routeItems[1]->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('Q3', $routeItems[2]->getAssessmentItemRef()->getIdentifier());
    }

    public function testGetCategories()
    {
        $route = new Route();
        $assessmentSection = new ExtendedAssessmentSection('S01', 'Section 1', true);
        $assessmentSections = new AssessmentSectionCollection([$assessmentSection]);
        $testPart = new ExtendedTestPart('TP01', $assessmentSections);
        $assessmentTest = new ExtendedAssessmentTest('T01', 'Test 1', new TestPartCollection([$testPart]));
        $assessmentSection->getSectionParts()[] = new ExtendedAssessmentItemRef('Q01', 'Q01.xml', new IdentifierCollection(['Math']));
        $assessmentSection->getSectionParts()[] = new ExtendedAssessmentItemRef('Q02', 'Q02.xml', new IdentifierCollection(['ELA']));
        $assessmentSection->getSectionParts()[] = new ExtendedAssessmentItemRef('Q03', 'Q03.xml', new IdentifierCollection(['Math']));

        $route->addRouteItem($assessmentSection->getSectionParts()['Q01'], $assessmentSections, $testPart, $assessmentTest);
        $route->addRouteItem($assessmentSection->getSectionParts()['Q02'], $assessmentSections, $testPart, $assessmentTest);
        $route->addRouteItem($assessmentSection->getSectionParts()['Q02'], $assessmentSections, $testPart, $assessmentTest);

        $categories = $route->getCategories();
        $this->assertCount(2, $categories);
        $this->assertEquals('Math', $categories[0]);
        $this->assertEquals('ELA', $categories[1]);
    }

    public function testKey()
    {
        $route = self::buildSimpleRoute();

        $this->assertEquals(0, $route->key());
        $route->next();
        $this->assertEquals(1, $route->key());
    }

    public function testGetIdentifierSequence()
    {
        $route = self::buildSimpleRoute();
        $identifiers = $route->getIdentifierSequence(true);

        $this->assertCount(3, $identifiers);
        $this->assertEquals('Q1.1', $identifiers[0]);
        $this->assertEquals('Q2.1', $identifiers[1]);
        $this->assertEquals('Q3.1', $identifiers[2]);

        $identifiers = $route->getIdentifierSequence(false);

        $this->assertCount(3, $identifiers);
        $this->assertEquals('Q1', $identifiers[0]);
        $this->assertEquals('Q2', $identifiers[1]);
        $this->assertEquals('Q3', $identifiers[2]);
    }

    public function testGetAssessmentItemRefsBySectionWhichDoesNotExist()
    {
        $route = self::buildSimpleRoute();
        $assessmentItemRefs = $route->getAssessmentItemRefsBySection('XXX');
        $this->assertCount(0, $assessmentItemRefs);
    }

    public function testGetOccurenceCountNoSuchAssessmentItemRef()
    {
        $route = self::buildSimpleRoute();
        $assessmentItemRef = new AssessmentItemRef('Q0X', 'Q0X.xml');
        $occurenceCount = $route->getOccurenceCount($assessmentItemRef);
        $this->assertEquals(0, $occurenceCount);
    }

    public function testGetRouteItemAtUnknownPosition()
    {
        $route = self::buildSimpleRoute();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("No RouteItem object found at position '1337'.");

        $routeItem = $route->getRouteItemAt(1337);
    }

    public function testGetLastRouteItemOnEmptyRoute()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Cannot get the last RouteItem of the Route while it is empty.");

        $lastRouteItem = $route->getLastRouteItem();
    }

    public function testGetFirstRouteItemOnEmptyRoute()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Cannot get the first RouteItem of the Route while it is empty.");

        $lastRouteItem = $route->getFirstRouteItem();
    }

    public function testIsLastOfTestPartOnEmptyRoute()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Cannot determine if the current RouteItem is the last of its TestPart when the Route is empty.");

        $lastRouteItem = $route->isLastOfTestPart();
    }

    public function testIsFirstOfTestPartOnEmptyRoute()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Cannot determine if the current RouteItem is the first of its TestPart when the Route is empty.");

        $firstRouteItem = $route->isFirstOfTestPart();
    }

    public function testIsInTestPartNoSuchPosition()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("The position '25' is out of the bounds of the route.");

        $isInTestPart = $route->isInTestPart(
            25,
            new TestPart(
                'TP01',
                new SectionPartCollection(
                    [
                        new AssessmentSection('S01', 'Section 1', true),
                    ]
                )
            )
        );
    }

    public function testGetRouteItemsByTestPartNoSuchTestPart()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("The testPart 'TP0X' is not referenced in the Route.");

        $routeItems = $route->getRouteItemsByTestPart(
            new TestPart(
                'TP0X',
                new SectionPartCollection(
                    [
                        new AssessmentSection('S0X', 'Section X', true),
                    ]
                )
            )
        );
    }

    public function testGetRouteItemsByTestPartWrongArgumentType()
    {
        $route = new Route();

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("The 'testPart' argument must be a string or a TestPart object.");

        $routeItems = $route->getRouteItemsByTestPart(false);
    }

    public function testGetRouteItemsByAssessmentSectionNoSuchAssessmentSectionOne()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("No assessmentSection with identifier 'S0X' found in the Route.");

        $routeItems = $route->getRouteItemsByAssessmentSection('S0X');
    }

    public function testGetRouteItemsByAssessmentSectionNoSuchAssessmentSectionTwo()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("The assessmentSection 'S0X' is not referenced in the Route.");

        $routeItems = $route->getRouteItemsByAssessmentSection(
            new AssessmentSection('S0X', 'Section X', true)
        );
    }

    public function testGetRouteItemsByAssessmentSectionWrongArgumentType()
    {
        $route = new Route();

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("The 'assessmentSection' argument must be a string or an AssessmentSection object.");

        $routeItems = $route->getRouteItemsByAssessmentSection(false);
    }

    public function testGetRouteItemsByAssessmentItemRef()
    {
        $route = self::buildSimpleRoute();
        $routeItems = $route->getRouteItemsByAssessmentItemRef('Q1');
        $this->assertCount(1, $routeItems);
    }

    public function testGetRouteItemsByAssessmentItemRefNoSuchAssessmentSectionOne()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("No AssessmentItemRef with identifier 'Q0X' found in the Route.");

        $routeItems = $route->getRouteItemsByAssessmentItemRef('Q0X');
    }

    public function testGetRouteItemsByAssessmentItemRefNoSuchAssessmentSectionTwo()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("No AssessmentItemRef with 'identifier' Q0X' found in the Route.");

        $routeItems = $route->getRouteItemsByAssessmentItemRef(
            new AssessmentItemRef('Q0X', 'Question X')
        );
    }

    public function testGetRouteItemsByAssessmentItemRefWrongArgumentType()
    {
        $route = new Route();

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("The 'assessmentItemRef' argument must be a string or an AssessmentItemRef object.");

        $routeItems = $route->getRouteItemsByAssessmentItemRef(false);
    }

    public function testBranchInvalidTarget()
    {
        $route = new Route();

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("The given identifier '|||' is an invalid branching target.");

        $route->branch('|||');
    }

    public function testBranchUnknownTarget()
    {
        $route = new Route();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("No such identifier 'Q1' found in the route for branching.");

        $route->branch('Q1');
    }

    public function testBranchToAssessmentItemRefOutsideOfTestPart()
    {
        $route = self::buildSimpleRoute(Route::class, 2, 1);
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Branchings to items outside of the current testPart is forbidden by the QTI 2.1 specification.");
        $route->branch('Q2');
    }

    public function testBranchToSameTestPart()
    {
        $route = self::buildSimpleRoute(Route::class, 2, 1);
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Cannot branch to the same testPart.");
        $route->branch('T1');
    }

    public function testBranchToAnotherTestPart()
    {
        $route = self::buildSimpleRoute(Route::class, 2, 1);
        $route->branch('T2');
        $this->assertTrue(true);
    }

    public function testBranchToAnotherSection()
    {
        $route = new Route();

        $assessmentItemRef1 = new AssessmentItemRef('Q1', 'Q1.xml');
        $assessmentItemRef2 = new AssessmentItemRef('Q2', 'Q2.xml');

        $assessmentSection = new AssessmentSection('MAIN', 'Main Section', true);
        $assessmentSection1 = new AssessmentSection('S1', 'Section 1', true);
        $assessmentSection1->setSectionParts(new SectionPartCollection([$assessmentItemRef1]));

        $assessmentSection2 = new AssessmentSection('S2', 'Section 2', true);
        $assessmentSection2->setSectionParts(new SectionPartCollection([$assessmentItemRef2]));

        $assessmentSection->setSectionParts(new SectionPartCollection([$assessmentSection1, $assessmentSection2]));

        $testPart = new TestPart('T1', new AssessmentSectionCollection([$assessmentSection]));
        $assessmentTest = new AssessmentTest('Test1', 'Test 1', new TestPartCollection([$testPart]));

        $route->addRouteItem($assessmentItemRef1, new AssessmentSectionCollection([$assessmentSection, $assessmentSection1]), $testPart, $assessmentTest);
        $route->addRouteItem($assessmentItemRef2, new AssessmentSectionCollection([$assessmentSection, $assessmentSection2]), $testPart, $assessmentTest);

        $this->assertEquals(0, $route->getPosition());
        $route->branch('S2');
        $this->assertEquals(1, $route->getPosition());
    }

    public function testBranchToSectionOutsideOfTestPart()
    {
        $route = new Route();

        $assessmentItemRef1 = new AssessmentItemRef('Q1', 'Q1.xml');
        $assessmentItemRef2 = new AssessmentItemRef('Q2', 'Q2.xml');

        $assessmentSection1 = new AssessmentSection('S1', 'Section 1', true);
        $assessmentSection1->setSectionParts(new SectionPartCollection([$assessmentItemRef1]));

        $assessmentSection2 = new AssessmentSection('S2', 'Section 2', true);
        $assessmentSection2->setSectionParts(new SectionPartCollection([$assessmentItemRef2]));

        $testPart1 = new TestPart('T1', new AssessmentSectionCollection([$assessmentSection1]));
        $testPart2 = new TestPart('T2', new AssessmentSectionCollection([$assessmentSection2]));
        $assessmentTest = new AssessmentTest('Test1', 'Test 1', new TestPartCollection([$testPart1, $testPart2]));

        $route->addRouteItem($assessmentItemRef1, $assessmentSection1, $testPart1, $assessmentTest);
        $route->addRouteItem($assessmentItemRef2, $assessmentSection2, $testPart2, $assessmentTest);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Branchings to assessmentSections outside of the current testPart is forbidden by the QTI 2.1 specification.");

        $route->branch('S2');
    }

    public function testGetRouteItemPositionUnknownRouteItem()
    {
        $route = self::buildSimpleRoute();
        $assessmentItemRef = new AssessmentItemRef('Q08', 'Q08.xml');
        $assessmentSection = new AssessmentSection('S09', 'Section 09', true);
        $testPart = new TestPart('T1', new AssessmentSectionCollection([$assessmentSection]));
        $routeItem = new RouteItem($assessmentItemRef, $assessmentSection, $testPart, new AssessmentTest('Test', 'Test'));

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("No such RouteItem object referenced in the Route.");

        $route->getRouteItemPosition($routeItem);
    }
}
