<?php

declare(strict_types=1);

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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\pci\json;

use InvalidArgumentException;
use qtism\common\datatypes\files\FileHash;
use qtism\common\datatypes\files\FileManager;
use qtism\common\datatypes\files\FileManagerException;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiDatatype;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFile;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\utils\Arrays;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\RecordContainer;

/**
 * This class aims at providing the necessary behaviours to
 * unmarshall JSON PCI representations of QTI data into the QTISM Runtime model.
 *
 * The JSON data given to this implementation must respect the structure formulated
 * by the IMS Global Portable Custom Interaction Version 1.0 Candidate Final specification
 * in order to be correctly handled.
 *
 * @see http://www.imsglobal.org/assessment/pciv1p0cf/imsPCIv1p0cf.html#_Toc353965343
 */
class Unmarshaller
{
    /**
     * A FileManager object making the JSON Unmarshaller able to build
     * QTI Files from a PCI JSON representation.
     *
     * @var FileManager
     */
    private $fileManager;

    /**
     * Create a new JSON Unmarshaller object.
     *
     * @param FileManager $fileManager A FileManager object making the unmarshaller able to build QTI Files from PCI JSON representation.
     */
    public function __construct(FileManager $fileManager)
    {
        $this->setFileManager($fileManager);
    }

    /**
     * Set the FileManager object making the Unmarshaller able to build QTI Files from
     * a PCI JSON representation.
     *
     * @param FileManager $fileManager A FileManager object.
     */
    protected function setFileManager(FileManager $fileManager): void
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Get the FileManager object making the Unmarshaller able to build QTI Files from
     * a PCI JSON representation.
     *
     * @return FileManager A FileManager object.
     */
    protected function getFileManager(): FileManager
    {
        return $this->fileManager;
    }

    /**
     * Transform a PCI JSON representation of QTI data into the QTISM runtime model.
     *
     * @param string|array $json The json data to be transformed.
     * @return null|QtiDatatype|array
     * @throws FileManagerException
     * @throws UnmarshallingException If an error occurs while processing $json.
     */
    public function unmarshall($json)
    {
        if (is_string($json)) {
            $tmpJson = @json_decode($json, true);
            if ($tmpJson === null) {
                // An error occurred while decoding.
                $msg = "An error occurred while decoding the following JSON data '" . mb_substr($json, 0, 30, 'UTF-8') . "...'.";
                $code = UnmarshallingException::JSON_DECODE;
                throw new UnmarshallingException($msg, $code);
            }

            $json = $tmpJson;
        }

        if (is_array($json) === false || count($json) === 0) {
            $msg = "The '" . get_class($this) . "::unmarshall' method only accepts a JSON string or a non-empty array as argument, '";
            if (is_object($json)) {
                $msg .= get_class($json);
            } else {
                $msg .= gettype($json);
            }

            $msg .= "' given.";
            $code = UnmarshallingException::NOT_SUPPORTED;
            throw new UnmarshallingException($msg, $code);
        }

        if (Arrays::isAssoc($json) === false) {
            $msg = "The '" . get_class($this) . "::unmarshall' does not accepts non-associative arrays.";
            $code = UnmarshallingException::NOT_SUPPORTED;
            throw new UnmarshallingException($msg, $code);
        }

        // Check whether or not $json is a state (no 'base' nor 'list' keys found),
        // a base, a list or a record.
        $keys = array_keys($json);

        if (in_array('base', $keys)) {
            // This is a base.
            return $this->unmarshallUnit($json);
        } elseif (in_array('list', $keys)) {
            return $this->unmarshallList($json);
        } elseif (in_array('record', $keys)) {
            // This is a record.
            $returnValue = new RecordContainer();

            if (count($json['record']) === 0) {
                return $returnValue;
            }

            foreach ($json['record'] as $v) {
                if (isset($v['name']) === false) {
                    $msg = "No 'name' key found in record field.";
                    $code = UnmarshallingException::NOT_PCI;
                    throw new UnmarshallingException($msg, $code);
                }

                if (isset($v['base'])) {
                    $returnValue[$v['name']] = $this->unmarshallUnit(['base' => $v['base']]);
                } elseif (isset($v['list'])) {
                    $returnValue[$v['name']] = $this->unmarshallList($v);
                } else {
                    // No value found, let's go for a null value.
                    $returnValue[$v['name']] = $this->unmarshallUnit(['base' => null]);
                }
            }

            return $returnValue;
        } else {
            // This is a state.
            $state = [];

            foreach ($json as $k => $j) {
                $state[$k] = $this->unmarshall($j);
            }

            return $state;
        }
    }

    /**
     * Unmarshall a unit of data into QTISM runtime model.
     *
     * @param array $unit
     * @return QtiDatatype|null
     * @throws FileManagerException
     * @throws UnmarshallingException
     */
    protected function unmarshallUnit(array $unit): ?QtiDatatype
    {
        if ($unit['base'] === null) {
            return null;
        }

        // Primitive base type.
        try {
            $keys = array_keys($unit['base']);
            switch ($keys[0]) {
                case 'boolean':
                    return $this->unmarshallBoolean($unit);
                    break;

                case 'integer':
                    return $this->unmarshallInteger($unit);
                    break;

                case 'float':
                    return $this->unmarshallFloat($unit);
                    break;

                case 'string':
                    return $this->unmarshallString($unit);
                    break;

                case 'point':
                    return $this->unmarshallPoint($unit);
                    break;

                case 'pair':
                    return $this->unmarshallPair($unit);
                    break;

                case 'directedPair':
                    return $this->unmarshallDirectedPair($unit);
                    break;

                case 'duration':
                    return $this->unmarshallDuration($unit);
                    break;

                case 'file':
                    return $this->unmarshallFile($unit);
                    break;

                case FileHash::FILE_HASH_KEY:
                    return $this->unmarshallFileHash($unit);
                    break;

                case 'uri':
                    return $this->unmarshallUri($unit);
                    break;

                case 'intOrIdentifier':
                    return $this->unmarshallIntOrIdentifier($unit);
                    break;

                case 'identifier':
                    return $this->unmarshallIdentifier($unit);
                    break;

                default:
                    throw new UnmarshallingException("Unknown QTI baseType '" . $keys[0] . "'");
                    break;
            }
        } catch (InvalidArgumentException $e) {
            $msg = 'A value does not satisfy its baseType.';
            throw new UnmarshallingException($msg, UnmarshallingException::NOT_PCI, $e);
        }
    }

    /**
     * Unmarshall a boolean JSON PCI representation.
     *
     * @param array $unit
     * @return QtiBoolean
     */
    protected function unmarshallBoolean(array $unit): QtiBoolean
    {
        return new QtiBoolean($unit['base']['boolean']);
    }

    /**
     * Unmarshall an integer JSON PCI representation.
     *
     * @param array $unit
     * @return QtiInteger
     */
    protected function unmarshallInteger(array $unit): QtiInteger
    {
        return new QtiInteger($unit['base']['integer']);
    }

    /**
     * Unmarshall a float JSON PCI representation.
     *
     * @param array $unit
     * @return QtiFloat
     */
    protected function unmarshallFloat(array $unit): QtiFloat
    {
        $val = $unit['base']['float'];

        if (is_int($val)) {
            $val = (float)$val;
        }

        return new QtiFloat($val);
    }

    /**
     * Unmarshall a string JSON PCI representation.
     *
     * @param array $unit
     * @return QtiString
     */
    protected function unmarshallString(array $unit): QtiString
    {
        return new QtiString($unit['base']['string']);
    }

    /**
     * Unmarshall a point JSON PCI representation.
     *
     * @param array $unit
     * @return QtiPoint
     */
    protected function unmarshallPoint(array $unit): QtiPoint
    {
        return new QtiPoint($unit['base']['point'][0], $unit['base']['point'][1]);
    }

    /**
     * Unmarshall a pair JSON PCI representation.
     *
     * @param array $unit
     * @return QtiPair
     */
    protected function unmarshallPair(array $unit): QtiPair
    {
        return new QtiPair($unit['base']['pair'][0], $unit['base']['pair'][1]);
    }

    /**
     * Unmarshall a directed pair JSON PCI representation.
     *
     * @param array $unit
     * @return QtiDirectedPair
     */
    protected function unmarshallDirectedPair(array $unit): QtiDirectedPair
    {
        return new QtiDirectedPair($unit['base']['directedPair'][0], $unit['base']['directedPair'][1]);
    }

    /**
     * Unmarshall a duration JSON PCI representation.
     *
     * @param array $unit
     * @return QtiDuration
     */
    protected function unmarshallDuration(array $unit): QtiDuration
    {
        return new QtiDuration($unit['base']['duration']);
    }

    /**
     * Unmarshall an uploaded file payload JSON PCI representation.
     *
     * @param array $unit
     * @return QtiFile
     * @throws FileManagerException
     */
    protected function unmarshallFile(array $unit): QtiFile
    {
        $fileArray = $unit['base']['file'];
        return $this->getFileManager()->createFromData(
            base64_decode($fileArray['data']),
            $fileArray['mime'],
            $fileArray['name'] ?? ''
        );
    }

    /**
     * Unmarshall an uploaded file hash JSON PCI representation.
     *
     * This is not a standard QTI feature but a convenience to store only
     * a hash of the file, to avoid storing huge files in the test session.
     * This suppose the following:
     * * Payload of the file has been persisted before.
     * * "id" key contains the persisted file id in the external file store.
     * * "data" key contains the hash, base64_encoded.
     *
     * @param array $unit
     * @return QtiFile
     * @throws FileManagerException
     */
    protected function unmarshallFileHash(array $unit): QtiFile
    {
        $fileHashArray = $unit['base'][FileHash::FILE_HASH_KEY];
        if (empty($fileHashArray['id'])) {
            throw new FileManagerException('To store an uploaded file hash, the file has to be persisted before and the file id provided in the "id" key.');
        }

        return new FileHash(
            $fileHashArray['id'],
            $fileHashArray['mime'],
            $fileHashArray['name'],
            $fileHashArray['data']
        );
    }

    /**
     * Unmarshall a URI JSON PCI representation.
     *
     * @param array $unit
     * @return QtiUri
     */
    protected function unmarshallUri(array $unit): QtiUri
    {
        return new QtiUri($unit['base']['uri']);
    }

    /**
     * Unmarshall an intOrIdentifier JSON PCI representation.
     *
     * @param array $unit
     * @return QtiIntOrIdentifier
     */
    protected function unmarshallIntOrIdentifier(array $unit): QtiIntOrIdentifier
    {
        return new QtiIntOrIdentifier($unit['base']['intOrIdentifier']);
    }

    /**
     * Unmarshall an identifier JSON PCI representation.
     *
     * @param array $unit
     * @return QtiIdentifier
     */
    protected function unmarshallIdentifier(array $unit): QtiIdentifier
    {
        return new QtiIdentifier($unit['base']['identifier']);
    }

    /**
     * Parse associate array, return MultipleContainer
     * which contains converted to BaseType items of 'list'
     *
     * @param array $parsedJson
     * @return MultipleContainer
     * @throws FileManagerException
     * @throws UnmarshallingException
     */
    protected function unmarshallList(array $parsedJson): MultipleContainer
    {
        $list = $parsedJson['list'];
        $key = key($list);

        if ($key === null) {
            $msg = 'No baseType provided for list.';
            throw new UnmarshallingException($msg, UnmarshallingException::NOT_PCI);
        }

        $baseType = BaseType::getConstantByName($key);

        if ($baseType === false) {
            $msg = "Unknown QTI baseType '" . $key . "'.";
            $code = UnmarshallingException::NOT_PCI;
            throw new UnmarshallingException($msg, $code);
        }

        $returnValue = new MultipleContainer($baseType);

        if (!is_array($list[$key])) {
            $msg = 'list is not an array';
            throw new UnmarshallingException($msg, UnmarshallingException::NOT_PCI);
        }

        foreach ($list[$key] as $v) {
            try {
                if ($v === null) {
                    $returnValue[] = $this->unmarshallUnit(['base' => $v]);
                } else {
                    $returnValue[] = $this->unmarshallUnit(['base' => [$key => $v]]);
                }
            } catch (InvalidArgumentException $e) {
                $strBaseType = BaseType::getNameByConstant($baseType);
                $msg = "A value is not compliant with the '${strBaseType}' baseType.";
                $code = UnmarshallingException::NOT_PCI;
                throw new UnmarshallingException($msg, $code);
            }
        }

        return $returnValue;
    }
}
