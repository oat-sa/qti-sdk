<?php

namespace qtismtest\data;

use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use qtism\data\SectionPartCollection;
use qtismtest\QtiSmTestCase;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiIdentifiableCollection;

/**
 * Class QtiComponentTest
 */
class QtiComponentTest extends QtiSmTestCase
{
    public function testGetComponentByIdOrClassNameSimple()
    {
        $id = 'assessmentSection1';
        $title = 'Assessment Section Title';
        $assessmentSection = new AssessmentSection($id, $title, true);

        $sectionParts = new SectionPartCollection();
        $sectionParts[] = new AssessmentItemRef('Q01', './Q01.xml');
        $sectionParts[] = new AssessmentItemRef('Q02', './Q02.xml');
        $sectionParts[] = new AssessmentItemRef('Q03', './Q03.xml');
        $sectionParts[] = new AssessmentItemRef('Q04', './Q04.xml');
        $assessmentSection->setSectionParts($sectionParts);

        // -- search by identifier.
        $search = $assessmentSection->getComponentByIdentifier('Q02');
        $this::assertSame($sectionParts['Q02'], $search);

        $search = $assessmentSection->getComponentByIdentifier('Q03', false);
        $this::assertSame($sectionParts['Q03'], $search);

        // -- search by QTI class name.
        $search = $assessmentSection->getComponentsByClassName('correct');
        $this::assertCount(0, $search);

        $search = $assessmentSection->getComponentsByClassName('assessmentItemRef');
        $this::assertCount(4, $search);

        $search = $assessmentSection->getComponentsByClassName(['assessmentItemRef', 'correct', 'sum'], false);
        $this::assertCount(4, $search);
    }

    public function testGetComponentByIdOrClassNameComplex()
    {
        $id = 'assessmentSectionRoot';
        $title = 'Assessment Section Root';
        $assessmentSectionRoot = new AssessmentSection($id, $title, true);

        // -- subAssessmentSection1
        $id = 'subAssessmentSection1';
        $title = 'Sub-AssessmentSection 1';
        $subAssessmentSection1 = new AssessmentSection($id, $title, true);

        $sectionParts = new SectionPartCollection();
        $sectionParts[] = new AssessmentItemRef('Q01', './Q01.xml');
        $sectionParts[] = new AssessmentItemRef('Q02', './Q02.xml');
        $sectionParts[] = new AssessmentItemRef('Q03', './Q03.xml');
        $sectionParts[] = new AssessmentItemRef('Q04', './Q04.xml');
        $subAssessmentSection1->setSectionParts($sectionParts);

        // -- subAssessmentSection2
        $id = 'subAssessmentSection2';
        $title = 'Sub-AssessmentSection 1';
        $subAssessmentSection2 = new AssessmentSection($id, $title, true);

        $sectionParts = new SectionPartCollection();
        $sectionParts[] = new AssessmentItemRef('Q05', './Q05.xml');
        $sectionParts[] = new AssessmentItemRef('Q06', './Q06.xml');
        $sectionParts[] = new AssessmentItemRef('Q07', './Q07.xml');
        $subAssessmentSection2->setSectionParts($sectionParts);

        // -- bind the whole thing together.
        $sectionParts = new SectionPartCollection();
        $sectionParts[] = $subAssessmentSection1;
        $sectionParts[] = $subAssessmentSection2;
        $assessmentSectionRoot->setSectionParts($sectionParts);

        // -- recursive search testing.
        $search = $assessmentSectionRoot->getComponentByIdentifier('Q02');
        $this::assertEquals('Q02', $search->getIdentifier());

        $search = $assessmentSectionRoot->getComponentByIdentifier('Q04');
        $this::assertEquals('Q04', $search->getIdentifier());

        $search = $assessmentSectionRoot->getComponentByIdentifier('Q05');
        $this::assertEquals('Q05', $search->getIdentifier());

        $search = $assessmentSectionRoot->getComponentByIdentifier('Q07');
        $this::assertEquals('Q07', $search->getIdentifier());

        $search = $assessmentSectionRoot->getComponentByIdentifier('subAssessmentSection1');
        $this::assertEquals('subAssessmentSection1', $search->getIdentifier());

        $search = $assessmentSectionRoot->getComponentByIdentifier('subAssessmentSection2');
        $this::assertEquals('subAssessmentSection2', $search->getIdentifier());

        // -- non recursive search testing.
        $search = $assessmentSectionRoot->getComponentByIdentifier('Q02', false);
        $this::assertSame($search, null);

        $search = $assessmentSectionRoot->getComponentByIdentifier('subAssessmentSection1', false);
        $this::assertEquals('subAssessmentSection1', $search->getIdentifier());

        $search = $assessmentSectionRoot->getComponentByIdentifier('assessmentSectionRoot', false);
        $this::assertSame($search, null);

        // -- recursive class name search.
        $search = $assessmentSectionRoot->getComponentsByClassName('assessmentSection');
        $this::assertCount(2, $search);

        $search = $assessmentSectionRoot->getComponentsByClassName('assessmentItemRef');
        $this::assertCount(7, $search);

        $search = $assessmentSectionRoot->getComponentsByClassName(['assessmentSection', 'assessmentItemRef']);
        $this::assertCount(9, $search);

        $search = $assessmentSectionRoot->getComponentsByClassName('microMachine');
        $this::assertCount(0, $search);

        // -- non recursive class name search.
        $search = $assessmentSectionRoot->getComponentsByClassName('assessmentSection', false);
        $this::assertCount(2, $search);

        $search = $assessmentSectionRoot->getComponentsByClassName('assessmentItemRef', false);
        $this::assertCount(0, $search);

        $search = $assessmentSectionRoot->getComponentsByClassName(['assessmentSection', 'assessmentItemRef'], false);
        $this::assertCount(2, $search);
    }

    public function testGetIdentifiableComponentsNoCollision()
    {
        $assessmentSection = new AssessmentSection('S01', 'Section S01', true);
        $assessmentItemRef1a = new AssessmentItemRef('Q01', './Q01.xml');
        $assessmentItemRef1b = new AssessmentItemRef('Q02', './Q02.xml');
        $assessmentSection->setSectionParts(new SectionPartCollection([$assessmentItemRef1a, $assessmentItemRef1b]));

        $search = $assessmentSection->getIdentifiableComponents();
        $this::assertInstanceOf(QtiIdentifiableCollection::class, $search);

        $this::assertEquals(['Q01', 'Q02'], $search->getKeys());
    }

    public function testGetIdentifiableComponentsCollision()
    {
        $assessmentSection = new AssessmentSection('S01', 'Section S01', true);
        $assessmentSection1a = new AssessmentSection('S01a', 'Section S01a', true);
        $assessmentSection1b = new AssessmentSection('S01b', 'Section S01b', true);

        $assessmentItemRef1a = new AssessmentItemRef('Q01', './Q01.xml');
        $assessmentItemRef1b = new AssessmentItemRef('Q01', './Q01.xml');

        $assessmentSection1a->setSectionParts(new SectionPartCollection([$assessmentItemRef1a]));
        $assessmentSection1b->setSectionParts(new SectionPartCollection([$assessmentItemRef1b]));
        $assessmentSection->setSectionParts(new SectionPartCollection([$assessmentSection1a, $assessmentSection1b]));

        $search = $assessmentSection->getIdentifiableComponents();
        $this::assertInstanceOf(QtiComponentCollection::class, $search);
        $this::assertCount(4, $search);
        $this::assertSame($assessmentSection1a, $search[0]);
        $this::assertSame($assessmentItemRef1a, $search[1]);
        $this::assertSame($assessmentSection1b, $search[2]);
        $this::assertSame($assessmentItemRef1b, $search[3]);
    }
}
