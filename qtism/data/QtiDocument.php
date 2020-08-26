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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use InvalidArgumentException;
use qtism\data\storage\StorageException;
use qtism\data\storage\xml\versions\QtiVersion;

/**
 * Class QtiDocument
 */
abstract class QtiDocument
{
    /**
     * The version of the document.
     *
     * @var QtiVersion
     */
    protected $version;

    /**
     * The root QTI Component of the document.
     *
     * @var QtiComponent
     */
    private $documentComponent;

    /**
     * @var string
     */
    private $url;

    /**
     * QtiDocument constructor.
     *
     * @param string $versionNumber
     * @param QtiComponent|null $documentComponent
     */
    public function __construct($versionNumber = '2.1.0', QtiComponent $documentComponent = null)
    {
        $this->setVersion($versionNumber);
        $this->setDocumentComponent($documentComponent);
    }

    /**
     * Set the QTI $version in use for this document.
     *
     * @param string $versionNumber A QTI version number e.g. '2.1.1'.
     * @throws InvalidArgumentException If $version is unknown regarding existing QTI versions.
     */
    public function setVersion(string $versionNumber)
    {
        $this->version = QtiVersion::create($versionNumber);
    }

    /**
     * The QTI version in use within the document.
     *
     * @return string A Semantic Versioning version number with major, minor and patch version e.g. '2.1.0'.
     */
    public function getVersion(): string
    {
        return (string)$this->version;
    }

    /**
     * Set the root component of the document.
     *
     * @param QtiComponent $documentComponent A QTI Component object.
     */
    public function setDocumentComponent(QtiComponent $documentComponent = null)
    {
        $this->documentComponent = $documentComponent;
    }

    /**
     * Get the root component of the document.
     *
     * @return QtiComponent
     */
    public function getDocumentComponent()
    {
        return $this->documentComponent;
    }

    /**
     * @param $url
     */
    protected function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @throws StorageException
     */
    abstract public function load($url);

    /**
     * @param string $url
     * @throws StorageException
     */
    abstract public function save($url);

    /**
     * Load the document content from a string.
     *
     * @param string $data
     * @return string
     * @throws StorageException
     */
    abstract public function loadFromString($data);

    /**
     * Save the document content as a string.
     *
     * @return string
     */
    abstract public function saveToString();
}
