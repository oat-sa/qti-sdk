<?php

namespace qtismtest\data\storage\xml;

use DOMDocument;
use Exception;
use PHPUnit\Framework\TestCase;
use qtism\data\storage\xml\ManifestDocument;
use qtism\data\storage\xml\XmlStorageException;

class ManifestDocumentTest extends TestCase
{
    /**
     * @throws XmlStorageException
     */
    public function testLoadFromStringThrowsException(): void
    {
        $this->expectException(XmlStorageException::class);

        $domDocumentMock = $this->createMock(DOMDocument::class);
        $domDocumentMock->expects($this->once())->method('loadXml')->willThrowException(new Exception());

        $manifestDocument = new ManifestDocument($domDocumentMock);
        $manifestDocument->loadFromString('<?xml version="1.0" encoding="UTF-8"?>');
    }

    /**
     * @throws XmlStorageException
     *
     * @dataProvider provideInterpretationData
     */
    public function testLoadFromFile(string $uri, array $expectedInterpretation = null): void
    {
        $manifestDocument = new ManifestDocument();
        $manifestDocument->loadFromString(file_get_contents(__DIR__ . '/../../../../resources/imsmanifest.xml'));

        $this->assertSame($expectedInterpretation, $manifestDocument->getInterpretation($uri));
    }

    public function provideInterpretationData(): array
    {
        return [
            ['', null],
            [
                'http://www.tao.lu/Ontologies/TAO.rdf#CERF-A1-A2',
                [
                    'uri' => 'http://www.tao.lu/Ontologies/TAO.rdf#CERF-A1-A2',
                    'label' => 'CEFR SCALE A1-A2',
                    'domain' => 'http://www.tao.lu/Ontologies/TAO.rdf#Scale',
                    'scale' => [
                        1 => 'Under A1',
                        2 => 'A1',
                        3 => 'A2'
                    ]
                ]
            ]
        ];
    }
}
