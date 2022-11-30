<?php

declare(strict_types=1);

namespace qtismtest\runtime\tests;

use OutOfBoundsException;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentItemSession;
use qtismtest\QtiSmAssessmentItemTestCase;

/**
 * Class AssessmentItemSessionShufflingTest
 */
class AssessmentItemSessionShufflingTest extends QtiSmAssessmentItemTestCase
{
    public function testShufflingOccurs(): void
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/choice_fixed.xml');

        $session = $this->createAssessmentItemSession($doc->getDocumentComponent());
        $session->beginItemSession();

        $shufflingStates = $session->getShufflingStates();
        $this::assertCount(1, $shufflingStates);

        $shufflingGroups = $shufflingStates[0]->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertCount(4, $shufflingGroups[0]->getIdentifiers());
        $this::assertTrue($shufflingGroups[0]->getIdentifiers()->contains('ChoiceA'));
        $this::assertTrue($shufflingGroups[0]->getIdentifiers()->contains('ChoiceB'));
        $this::assertTrue($shufflingGroups[0]->getIdentifiers()->contains('ChoiceC'));
        $this::assertTrue($shufflingGroups[0]->getIdentifiers()->contains('ChoiceD'));
    }

    /**
     * @depends testShufflingOccurs
     */
    public function testGetShuffledChoiceIdentifierAt(): void
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/choice_fixed.xml');

        $session = $this->createAssessmentItemSession($doc->getDocumentComponent());
        $session->beginItemSession();

        $identifiers = ['ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD'];

        $this::assertTrue(in_array($session->getShuffledChoiceIdentifierAt(0, 0), $identifiers));
        $this::assertTrue(in_array($session->getShuffledChoiceIdentifierAt(0, 1), $identifiers));
        $this::assertTrue(in_array($session->getShuffledChoiceIdentifierAt(0, 2), $identifiers));
        $this::assertTrue(in_array($session->getShuffledChoiceIdentifierAt(0, 3), $identifiers));
    }

    public function testGetShuffledChoiceIdentifierAtInvalidShufflingStateIndex(): void
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/choice_fixed.xml');

        $session = $this->createAssessmentItemSession($doc->getDocumentComponent());
        $session->beginItemSession();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No Shuffling State at index 1.');
        $session->getShuffledChoiceIdentifierAt(1, 3);
    }

    public function testGetShuffledChoiceIdentifierAtInvalidShuffledChoiceIndex(): void
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/choice_fixed.xml');

        $session = $this->createAssessmentItemSession($doc->getDocumentComponent());
        $session->beginItemSession();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No identifier at index 1337.');
        $session->getShuffledChoiceIdentifierAt(0, 1337);
    }
}
