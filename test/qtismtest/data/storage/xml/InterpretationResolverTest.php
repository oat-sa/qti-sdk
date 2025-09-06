<?php

namespace qtismtest\data\storage\xml;

use PHPUnit\Framework\TestCase;
use qtism\data\storage\xml\InterpretationResolver;
use qtism\data\storage\xml\ManifestDocument;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlStorageException;

class InterpretationResolverTest extends TestCase
{
    /**
     * @throws XmlStorageException
     */
    public function testLoadManifestDocument(): void
    {
        $manifestDocumentMock = $this->createMock(ManifestDocument::class);
        $manifestDocumentMock->expects($this->once())->method('loadFromString');

        $interpretationResolver = new InterpretationResolver($manifestDocumentMock);
        $interpretationResolver->loadManifestDocument('<?xml version="1.0" encoding="UTF-8"?>');
    }

    /**
     * @throws XmlStorageException
     */
    public function testResolveInterpretationsWithoutAssessmentTest(): void
    {
        $interpretationResolver = new InterpretationResolver();
        $interpretationResolver->loadManifestDocument(file_get_contents(__DIR__ . '/../../../../resources/imsmanifest.xml'));
        $this->assertSame(
            [],
            $interpretationResolver->resolveInterpretations()
        );
    }

    /**
     * @throws XmlStorageException
     */
    public function testResolveInterpretationsWithAssessmentTest(): void
    {
        $xmlCompactDocument = new XmlCompactDocument();
        $xmlCompactDocument->loadFromString(file_get_contents(__DIR__ . '/../../../../resources/compact-test.xml'));

        $interpretationResolver = new InterpretationResolver();
        $interpretationResolver->loadManifestDocument(file_get_contents(__DIR__ . '/../../../../resources/imsmanifest.xml'));
        $interpretationResolver->setAssessmentTest($xmlCompactDocument->getDocumentComponent());
        $this->assertSame(
            [
                'OUTCOME_2' => [
                    'uri' => 'http://www.tao.lu/Ontologies/TAO.rdf#CERF-A1-A2',
                    'label' => 'CEFR SCALE A1-A2',
                    'domain' => 'http://www.tao.lu/Ontologies/TAO.rdf#Scale',
                    'scale' => [
                        1 => 'Under A1',
                        2 => 'A1',
                        3 => 'A2',
                    ]
                ],
                'OUTCOME_4' => [
                    'uri' => 'http://www.tao.lu/Ontologies/TAO.rdf#CERF-A1-A2',
                    'label' => 'CEFR SCALE A1-A2',
                    'domain' => 'http://www.tao.lu/Ontologies/TAO.rdf#Scale',
                    'scale' => [
                        1 => 'Under A1',
                        2 => 'A1',
                        3 => 'A2',
                    ]
                ],
                'GRADE' => [
                    'uri' => 'http://www.tao.lu/Ontologies/TAO.rdf#CERF-A1-A2',
                    'label' => 'CEFR SCALE A1-A2',
                    'domain' => 'http://www.tao.lu/Ontologies/TAO.rdf#Scale',
                    'scale' => [
                        1 => 'Under A1',
                        2 => 'A1',
                        3 => 'A2',
                    ]
                ],
                'GRADE_MAX' => [
                    'uri' => 'http://www.tao.lu/Ontologies/TAO.rdf#CERF-A1-A2',
                    'label' => 'CEFR SCALE A1-A2',
                    'domain' => 'http://www.tao.lu/Ontologies/TAO.rdf#Scale',
                    'scale' => [
                        1 => 'Under A1',
                        2 => 'A1',
                        3 => 'A2',
                    ]
                ]
            ],
            $interpretationResolver->resolveInterpretations()
        );
    }
}
