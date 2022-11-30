<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml;

use qtism\data\AssessmentSection;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\data\View;
use qtismtest\QtiSmTestCase;
use qtism\data\AssessmentItemRef;
use qtism\data\SectionPartCollection;
use qtism\data\content\RubricBlockCollection;

/**
 * Class XmlAssessmentSectionDocumentTest
 */
class XmlAssessmentSectionDocumentTest extends QtiSmTestCase
{
    /**
     * @param AssessmentSection|null $assessmentSection
     * @throws XmlStorageException
     */
    public function testLoad(AssessmentSection $assessmentSection = null): void
    {
        if (empty($assessmentSection)) {
            $uri = self::samplesDir() . 'custom/standalone_assessmentsection.xml';
            $doc = new XmlDocument();
            $doc->load($uri);

            $this::assertInstanceOf(XmlDocument::class, $doc);
            $this::assertInstanceOf(AssessmentSection::class, $doc->getDocumentComponent());

            $assessmentSection = $doc->getDocumentComponent();
        }

        $rubricBlocks = $assessmentSection->getRubricBlocks();
        $this::assertInstanceOf(RubricBlockCollection::class, $rubricBlocks);
        $this::assertCount(1, $rubricBlocks);

        $rubricBlock = $rubricBlocks[0];
        $views = $rubricBlock->getViews();
        $this::assertCount(1, $views);
        $this::assertEquals(View::CANDIDATE, $views[0]);

        $assessmentItemRefs = $assessmentSection->getSectionParts();
        $this::assertInstanceOf(SectionPartCollection::class, $assessmentItemRefs);

        foreach ($assessmentItemRefs as $itemRef) {
            $this::assertInstanceOf(AssessmentItemRef::class, $itemRef);
        }
    }

    public function testWrite(): void
    {
        $uri = self::samplesDir() . 'custom/standalone_assessmentsection.xml';
        $doc = new XmlDocument();
        $doc->load($uri);

        $assessmentSection = $doc->getDocumentComponent();

        // Write the file.
        $uri = tempnam('/tmp', 'qsm');
        $doc->save($uri);
        $this::assertFileExists($uri);

        // Reload it.
        $doc->load($uri);
        $this::assertInstanceOf(AssessmentSection::class, $doc->getDocumentComponent());

        // Retest.
        $this->testLoad($doc->getDocumentComponent());

        unlink($uri);
    }
}
