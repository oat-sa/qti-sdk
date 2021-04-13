<?php

namespace qtismtest\data\storage\xml;

use LogicException;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;

/**
 * Class XmlDocumentTemplateLocationTest
 */
class XmlDocumentTemplateLocationTest extends QtiSmTestCase
{
    /**
     * @param $file
     * @param $filesystem
     * @throws XmlStorageException
     * @dataProvider correctlyFormedProvider
     */
    public function testCorrectlyFormed($file, $filesystem)
    {
        $doc = new XmlDocument();

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $doc->load($file, true);

        $responseProcessings = $doc->getDocumentComponent()->getComponentsByClassName('responseProcessing');
        $this::assertCount(1, $responseProcessings);
        $this::assertEquals('template_location_rp.xml', $responseProcessings[0]->getTemplateLocation());

        $doc->resolveTemplateLocation(true);

        $responseProcessings = $doc->getDocumentComponent()->getComponentsByClassName('responseProcessing');
        $this::assertCount(1, $responseProcessings);
        $this::assertEquals('http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct', $responseProcessings[0]->getTemplate());
    }

    /**
     * @return array
     */
    public function correctlyFormedProvider()
    {
        return [
            [self::samplesDir() . 'custom/items/template_location/template_location_item.xml', false],
            ['custom/items/template_location/template_location_item.xml', true],
        ];
    }

    public function testNotLoaded()
    {
        $doc = new XmlDocument();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot resolve template location before loading any file.');
        $doc->resolveTemplateLocation();
    }

    /**
     * @param $file
     * @param $filesystem
     * @throws XmlStorageException
     * @dataProvider wrongTargetProvider
     */
    public function testWrongTarget($file, $filesystem)
    {
        $doc = new XmlDocument();

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $doc->load($file, true);

        $this->expectException(XmlStorageException::class);
        $doc->resolveTemplateLocation();
    }

    /**
     * @return array
     */
    public function wrongTargetProvider()
    {
        return [
            [self::samplesDir() . 'custom/items/template_location/template_location_item_wrong_target.xml', false],
            ['custom/items/template_location/template_location_item_wrong_target.xml', true],
        ];
    }

    /**
     * @param $file
     * @param $filesystem
     * @throws XmlStorageException
     * @dataProvider invalidTargetNoValidationProvider
     */
    public function testInvalidTargetNoValidation($file, $filesystem)
    {
        $doc = new XmlDocument();

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $doc->load($file, true);

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage("'responseProcessingZ' components are not supported in QTI version '2.1.0'.");
        $this->expectExceptionCode(XmlStorageException::VERSION);
        $doc->resolveTemplateLocation();
    }

    /**
     * @return array
     */
    public function invalidTargetNoValidationProvider()
    {
        return [
            [self::samplesDir() . 'custom/items/template_location/template_location_item_invalid_target.xml', false],
            ['custom/items/template_location/template_location_item_invalid_target.xml', true],
        ];
    }

    /**
     * @param $file
     * @param $filesystem
     * @throws XmlStorageException
     * @dataProvider invalidTargetValidationProvider
     */
    public function testInvalidTargetValidation($file, $filesystem)
    {
        $doc = new XmlDocument();

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $doc->load($file, true);

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionCode(XmlStorageException::XSD_VALIDATION);
        $doc->resolveTemplateLocation(true);
    }

    /**
     * @return array
     */
    public function invalidTargetValidationProvider()
    {
        return [
            [self::samplesDir() . 'custom/items/template_location/template_location_item_invalid_target.xml', false],
            ['custom/items/template_location/template_location_item_invalid_target.xml', true],
        ];
    }
}
