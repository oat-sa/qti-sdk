<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\versions;

use DOMDocument;
use InvalidArgumentException;
use qtism\common\utils\Version;
use qtism\data\storage\xml\marshalling\MarshallerFactory;
use qtism\data\storage\xml\Utils;
use qtism\data\storage\xml\XmlStorageException;

/**
 * Generic QTI version.
 */
class QtiVersion extends Version
{
    const SUPPORTED_VERSIONS = [
        '2.0.0' => QtiVersion200::class,
        '2.1.0' => QtiVersion210::class,
        '2.1.1' => QtiVersion211::class,
        '2.2.0' => QtiVersion220::class,
        '2.2.1' => QtiVersion221::class,
        '2.2.2' => QtiVersion222::class,
        '2.2.3' => QtiVersion223::class,
        '2.2.4' => QtiVersion224::class,
        '3.0.0' => QtiVersion300::class,
    ];

    const UNSUPPORTED_VERSION_MESSAGE = 'QTI version "%s" is not supported.';

    /** @var string */
    private $versionNumber;

    /**
     * QtiVersion constructor.
     *
     * @param string $versionNumber
     */
    public function __construct(string $versionNumber)
    {
        $this->versionNumber = $versionNumber;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->versionNumber;
    }

    /**
     * Creates a new Version given the version number.
     *
     * @param string $versionNumber
     * @return $this
     */
    public static function create(string $versionNumber): self
    {
        $versionNumber = self::sanitize($versionNumber);
        $class = static::SUPPORTED_VERSIONS[$versionNumber];
        return new $class($versionNumber);
    }

    /**
     * Checks that the given version is supported.
     *
     * @param string $version a semantic version
     * @throws InvalidArgumentException when the version is not supported.
     */
    protected static function checkVersion(string $version)
    {
        if (!isset(static::SUPPORTED_VERSIONS[$version])) {
            throw QtiVersionException::unsupportedVersion(static::UNSUPPORTED_VERSION_MESSAGE, $version, static::SUPPORTED_VERSIONS);
        }
    }

    /**
     * Infer the QTI version of the document from its XML definition.
     *
     * @param DOMDocument $document
     * @return string a semantic QTI version inferred from the document.
     * @throws XmlStorageException when the version can not be inferred.
     */
    public static function infer(DOMDocument $document): string
    {
        $root = $document->documentElement;
        $version = '';

        if ($root !== null) {
            $rootNs = $root->namespaceURI;
            if ($rootNs !== null) {
                $version = static::findVersionInDocument($rootNs, $document);
            }
        }

        if ($version === '') {
            $msg = 'Cannot infer QTI version. Check namespaces and schema locations in XML file.';
            throw new XmlStorageException($msg, XmlStorageException::VERSION);
        }

        return $version;
    }

    /**
     * Finds the version of the document given the namespace and Xsd location.
     *
     * @param string $rootNs
     * @param DOMDocument $document
     * @return string
     */
    public static function findVersionInDocument(string $rootNs, DOMDocument $document): string
    {
        switch ($rootNs) {
            case QtiVersion200::XMLNS:
                return '2.0.0';

            case QtiVersion210::XMLNS:
                if (Utils::getXsdLocation($document, $rootNs) === QtiVersion211::XSD) {
                    return '2.1.1';
                }
                return '2.1.0';

            case QtiVersion220::XMLNS:
                switch (Utils::getXsdLocation($document, $rootNs)) {
                    case QtiVersion221::XSD:
                        return '2.2.1';
                    case QtiVersion222::XSD:
                        return '2.2.2';
                    case QtiVersion223::XSD:
                        return '2.2.3';
                    case QtiVersion224::XSD:
                        return '2.2.4';
                }
                return '2.2.0';

            case QtiVersion300::XMLNS:
                return '3.0.0';
        }

        return '';
    }

    public function getLocalXsd(): string
    {
        return __DIR__ . '/../schemes/' . static::LOCAL_XSD;
    }

    public function getNamespace(): string
    {
        return static::XMLNS;
    }

    public function getXsdLocation(): string
    {
        return static::XSD;
    }

    public function getExternalSchemaLocation(string $prefix): string
    {
        return '';
    }

    public function getExternalNamespace(string $prefix): string
    {
        return '';
    }

    public function getMarshallerFactory(): MarshallerFactory
    {
        $factoryClass = static::MARSHALLER_FACTORY;
        return new $factoryClass();
    }
}
