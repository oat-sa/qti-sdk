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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use InvalidArgumentException;
use qtism\common\utils\Version;
use qtism\common\utils\QtiVersionException;

/**
 * Class XmlResultDocument
 */
class XmlResultDocument extends XmlDocument
{
    /**
     * Get the XML schema to use for a given QTI Result Report version.
     *
     * @return string A filename pointing at an XML Schema file.
     */
    public function getSchemaLocation(): string
    {
        $versionNumber = Version::appendPatchVersion($this->getVersion());

        switch ($versionNumber) {
            case '2.1.1':
            case '2.1.0':
                $filename = __DIR__ . '/schemes/qtiv2p1/imsqti_result_v2p1.xsd';
                break;

            case '2.2.1':
            case '2.2.2':
            case '2.2.0':
                $filename = __DIR__ . '/schemes/qtiv2p2/imsqti_result_v2p2.xsd';
                break;

            default:
                throw QtiVersionException::unsupportedResultVersion($versionNumber);
        }

        return $filename;
    }

    /**
     * Returns the QTI Result Report namespace for the given version
     *
     * @param string $versionNumber
     * @return string
     * @throws InvalidArgumentException when the version is not supported.
     */
    protected function getNamespace(string $versionNumber): string
    {
        switch ($versionNumber) {
            case '2.1.0':
            case '2.1.1':
                $namespace = 'http://www.imsglobal.org/xsd/imsqti_result_v2p1';
                break;

            case '2.2.0':
            case '2.2.1':
            case '2.2.2':
                $namespace = 'http://www.imsglobal.org/xsd/imsqti_result_v2p2';
                break;

            default:
                throw QtiVersionException::unsupportedResultVersion($versionNumber);
        }

        return $namespace;
    }

    /**
     * Returns the QTI Result Report XSD location for the given version
     *
     * @param string $versionNumber
     * @return string
     * @throws InvalidArgumentException when the version is not supported.
     */
    protected function getXsdLocation(string $versionNumber): string
    {
        switch ($versionNumber) {
            case '2.1.0':
            case '2.1.1':
                $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_result_v2p1.xsd';
                break;

            case '2.2.0':
            case '2.2.1':
            case '2.2.2':
                $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_result_v2p2.xsd';
                break;

            default:
                throw QtiVersionException::unsupportedResultVersion($versionNumber);
        }

        return $xsdLocation;
    }

    protected function inferVersion()
    {
        $document = $this->getDomDocument();
        $root = $document->documentElement;
        $version = false;

        if (empty($root) === false) {
            $rootNs = $root->namespaceURI;

            if ($rootNs === 'http://www.imsglobal.org/xsd/imsqti_result_v2p1') {
                $version = '2.1.0';
            } elseif ($rootNs === 'http://www.imsglobal.org/xsd/imsqti_result_v2p2') {
                $version = '2.2.0';
            }
        }

        if ($version === false) {
            $msg = 'Cannot infer QTI Result Report version. Check namespaces and schema locations in XML file.';
            throw new XmlStorageException($msg, XmlStorageException::VERSION);
        }

        return $version;
    }
}
