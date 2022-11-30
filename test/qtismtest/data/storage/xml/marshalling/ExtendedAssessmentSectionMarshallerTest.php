<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\AssessmentSectionRef;
use qtism\data\content\RubricBlockRef;
use qtism\data\content\RubricBlockRefCollection;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\SectionPartCollection;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;

/**
 * Class ExtendedAssessmentSectionMarshallerTest
 */
class ExtendedAssessmentSectionMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall(): void
    {
        $elt = $this->createDOMElement('
            <assessmentSection identifier="S01" title="Section 01" visible="true">
                <assessmentSectionRef identifier="SR01" href="./SR01.xml"/>
                <rubricBlockRef identifier="R01" href="./R01.xml"/>
            </assessmentSection>
        ');

        $factory = new Compact21MarshallerFactory();
        $marshaller = $factory->createMarshaller($elt);

        $section = $marshaller->unmarshall($elt);
        $this::assertInstanceOf(ExtendedAssessmentSection::class, $section);
        $this::assertEquals('S01', $section->getIdentifier());
        $this::assertEquals('Section 01', $section->getTitle());
        $this::assertTrue($section->isVisible());

        $sectionParts = $section->getSectionParts();
        $this::assertCount(1, $sectionParts);
        $this::assertInstanceOf(AssessmentSectionRef::class, $sectionParts['SR01']);
        $this::assertEquals('SR01', $sectionParts['SR01']->getIdentifier());
        $this::assertEquals('./SR01.xml', $sectionParts['SR01']->getHref());

        $rubricBlockRefs = $section->getRubricBlockRefs();
        $this::assertCount(1, $rubricBlockRefs);
        $this::assertInstanceOf(RubricBlockRef::class, $rubricBlockRefs['R01']);
        $this::assertEquals('R01', $rubricBlockRefs['R01']->getIdentifier());
        $this::assertEquals('./R01.xml', $rubricBlockRefs['R01']->getHref());

        $this::assertCount(0, $section->getRubricBlocks());
    }

    public function testMarshall(): void
    {
        $section = new ExtendedAssessmentSection('S01', 'Section 01', true);
        $section->setSectionParts(new SectionPartCollection([new AssessmentSectionRef('SR01', './SR01.xml')]));
        $section->setRubricBlockRefs(new RubricBlockRefCollection([new RubricBlockRef('R01', './R01.xml')]));

        $factory = new Compact21MarshallerFactory();
        $marshaller = $factory->createMarshaller($section);
        $elt = $marshaller->marshall($section);

        $this::assertEquals('assessmentSection', $elt->nodeName);
        $this::assertEquals('S01', $elt->getAttribute('identifier'));
        $this::assertEquals('Section 01', $elt->getAttribute('title'));
        $this::assertEquals('true', $elt->getAttribute('visible'));

        $assessmentSectionRefElts = $elt->getElementsByTagName('assessmentSectionRef');
        $this::assertEquals(1, $assessmentSectionRefElts->length);
        $assessmentSectionRefElt = $assessmentSectionRefElts->item(0);
        $this::assertEquals('SR01', $assessmentSectionRefElt->getAttribute('identifier'));
        $this::assertEquals('./SR01.xml', $assessmentSectionRefElt->getAttribute('href'));

        $rubricBlockRefElts = $elt->getElementsByTagName('rubricBlockRef');
        $this::assertEquals(1, $rubricBlockRefElts->length);
        $rubricBlockRefElt = $rubricBlockRefElts->item(0);
        $this::assertEquals('R01', $rubricBlockRefElt->getAttribute('identifier'));
        $this::assertEquals('./R01.xml', $rubricBlockRefElt->getAttribute('href'));
    }
}
