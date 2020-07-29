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
use qtism\common\utils\Version;
use qtism\data\storage\StorageException;

abstract class QtiDocument
{
    /**
     * The version of the document.
     *
     * @var string
     */
    private $version;

    /**
     * The root QTI Component of the document.
     *
     * @var QtiComponent
     */
    private $documentComponent;

    /**
     *
     * @var string
     */
    private $url;

    public function __construct($version = '2.1.0', QtiComponent $documentComponent = null)
    {
        $this->setVersion($version);
        $this->setDocumentComponent($documentComponent);
    }

    /**
     * Set the QTI $version in use for this document.
     *
     * @param string $version A QTI version number e.g. '2.1.1'.
     * @throws InvalidArgumentException If $version is unknown regarding existing QTI versions.
     */
    public function setVersion($version)
    {
        $this->version = Version::sanitize($version);
    }

    /**
     * The QTI version in use within the document.
     *
     * @return string A Semantic Versioning version number with major, minor and patch version e.g. '2.1.0'.
     */
    public function getVersion()
    {
        return $this->version;
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

    protected function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
     *
     * @param string $url
     * @throws StorageException
     */
    abstract public function load($url);

    /**
     *
     * @param string $url
     * @throws StorageException
     */
    abstract public function save($url);
}
