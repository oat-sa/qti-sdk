<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Bogaerts Jérôme <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\data\storage\xml;

use DateTime;
use InvalidArgumentException;
use qtism\data\results\AssessmentResult;
use qtism\data\results\Context;
use qtism\data\results\SessionIdentifier;
use qtism\data\results\SessionIdentifierCollection;
use qtism\data\storage\xml\XmlResultDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;

/**
 * Class XmlResultDocumentTest
 */
class XmlResultDocumentTest extends QtiSmTestCase
{
    public function testLoad()
    {
        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result.xml', true);

        $this::assertEquals('2.1.0', $xmlDoc->getVersion());

        /** @var AssessmentResult $assessmentResult */
        $assessmentResult = $xmlDoc->getDocumentComponent();
        $this::assertInstanceOf(AssessmentResult::class, $assessmentResult);

        $context = $assessmentResult->getContext();
        $this::assertInstanceOf(Context::class, $context);

        $sessionIdentifiers = $context->getSessionIdentifiers();
        $this::assertInstanceOf(SessionIdentifierCollection::class, $sessionIdentifiers);

        /** @var SessionIdentifier $sessionIdentifier1 */
        $sessionIdentifier1 = $sessionIdentifiers[0];
        $this::assertEquals('sessionIdentifier1-id', $sessionIdentifier1->getIdentifier());
        $this::assertEquals('http://sessionIdentifier1-sourceID', $sessionIdentifier1->getSourceID());

        /** @var SessionIdentifier $sessionIdentifier2 */
        $sessionIdentifier2 = $sessionIdentifiers[1];
        $this::assertEquals('sessionIdentifier2-id', $sessionIdentifier2->getIdentifier());
        $this::assertEquals('http://sessionIdentifier2-sourceID', $sessionIdentifier2->getSourceID());

        $testResult = $assessmentResult->getTestResult();
        $this::assertEquals('fixture-test-identifier', $testResult->getIdentifier());
        $this::assertInstanceOf(DateTime::class, $testResult->getDatestamp());

        $this::assertCount(2, $testResult->getItemVariables());
    }

    public function testLoadMissingData()
    {
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage('The document could not be validated with XML Schema');

        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result-missing-data.xml', true);
    }

    public function testSaveToString()
    {
        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result.xml', true);

        $expected = file_get_contents(self::samplesDir() . 'results/simple-assessment-result-saved-to-string.xml');

        $this::assertEquals($expected, $xmlDoc->saveToString(true));
    }

    /**
     * @dataProvider inferVersionAndSchemaValidateProvider
     * @param string $testFile
     * @param string $expectedVersion
     * @throws XmlStorageException
     */
    public function testInferVersionAndSchemaValidate(string $testFile, string $expectedVersion)
    {
        $xmlDoc = new XmlResultDocument();
        $xmlDoc->load($testFile, true);

        $this::assertEquals($expectedVersion, $xmlDoc->getVersion());
    }

    /**
     * @return array
     */
    public function inferVersionAndSchemaValidateProvider(): array
    {
        return [
            [self::samplesDir() . 'results/simple-assessment-result.xml', '2.1.0'],
            [self::samplesDir() . 'results/simple-assessment-result-v2p2.xml', '2.2.0'],
        ];
    }

    public function testInferVersionWithMissingNamespaceReturnsDefaultVersion()
    {
        $xmlDoc = new XmlResultDocument();

        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result-missing-namespace.xml');

        $this::assertEquals('2.1.0', $xmlDoc->getVersion());
    }

    public function testInferVersionWithWrongNamespaceThrowsException()
    {
        $xmlDoc = new XmlResultDocument();

        $this->expectException(XmlStorageException::class);

        $xmlDoc->load(self::samplesDir() . 'results/simple-assessment-result-wrong-namespace.xml');
    }

    /**
     * @dataProvider changeVersionProvider
     * @param string $fromVersion
     * @param string $fromFile
     * @param string $toVersion
     * @param string $toFile
     * @throws XmlStorageException
     */
    public function testChangeVersion($fromVersion, $fromFile, $toVersion, $toFile)
    {
        $doc = new XmlResultDocument($fromVersion);
        $doc->load($fromFile, true);

        $doc->changeVersion($toVersion);

        $expected = new XmlResultDocument($toVersion);
        $expected->load($toFile, true);

        $this::assertEquals($expected->getDomDocument()->documentElement, $doc->getDomDocument()->documentElement);
    }

    /**
     * @return array
     */
    public function changeVersionProvider(): array
    {
        $path = self::samplesDir() . 'results/simple-assessment-result';
        return [
            ['2.1', $path . '.xml', '2.2', $path . '-v2p2.xml'],
            ['2.2', $path . '-v2p2.xml', '2.1', $path . '.xml'],
        ];
    }

    public function testChangeVersionWithUnknownVersionThrowsException()
    {
        $wrongVersion = '36.15';
        $patchedWrongVersion = $wrongVersion . '.0';
        $file21 = self::samplesDir() . 'results/simple-assessment-result.xml';

        $doc = new XmlResultDocument('2.1');
        $doc->load($file21, true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QTI Result Report is not supported for version "' . $patchedWrongVersion . '".');

        $doc->changeVersion($wrongVersion);
    }
}
