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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\pci\json;

use InvalidArgumentException;
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
     * @param FileManager A FileManager object making the unmarshaller able to build QTI Files from PCI JSON representation.
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
    protected function setFileManager(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Get the FileManager object making the Unmarshaller able to build QTI Files from
     * a PCI JSON representation.
     *
     * @return FileManager A FileManager object.
     */
    protected function getFileManager()
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
        if (is_string($json) === true) {
            $tmpJson = @json_decode($json, true);
            if ($tmpJson === null) {
                // An error occured while decoding.
                $msg = "An error occured while decoding the following JSON data '" . mb_substr($json, 0, 30, 'UTF-8') . "...'.";
                $code = UnmarshallingException::JSON_DECODE;
                throw new UnmarshallingException($msg, $code);
            }

            $json = $tmpJson;
        }

        if (is_array($json) === false || count($json) === 0) {
            $msg = "The '" . get_class($this) . "::unmarshall' method only accepts a JSON string or a non-empty array as argument, '";
            if (is_object($json) === true) {
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

        if (in_array('base', $keys) === true) {
            // This is a base.
            return $this->unmarshallUnit($json);
        } elseif (in_array('list', $keys) === true) {
            $keys = array_keys($json['list']);
            if (isset($keys[0]) === false) {
                $msg = "No baseType provided for list.";
                throw new UnmarshallingException($msg, UnmarshallingException::NOT_PCI);
            }

            $baseType = BaseType::getConstantByName($keys[0]);

            if ($baseType === false) {
                $msg = "Unknown QTI baseType '" . $keys[0] . "'.";
                $code = UnmarshallingException::NOT_PCI;
                throw new UnmarshallingException($msg, $code);
            }

            $returnValue = new MultipleContainer($baseType);

            // This is a list.
            foreach ($json['list'][$keys[0]] as $v) {
                if ($v === null) {
                    $returnValue[] = $this->unmarshallUnit(['base' => $v]);
                } else {
                    $returnValue[] = $this->unmarshallUnit(['base' => [$keys[0] => $v]]);
                }
            }

            return $returnValue;
        } elseif (in_array('record', $keys) === true) {
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

                if (isset($v['base']) === true || (array_key_exists('base', $v) && $v['base'] === null)) {
                    $unit = ['base' => $v['base']];
                } else {
                    // No value found, let's go for a null value.
                    $unit = ['base' => null];
                }

                $returnValue[$v['name']] = $this->unmarshallUnit($unit);
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
     * @return null|QtiDatatype
     * @throws FileManagerException
     * @throws UnmarshallingException
     */
    protected function unmarshallUnit(array $unit)
    {
        if (isset($unit['base'])) {
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
                $msg = "A value does not satisfy its baseType.";
                throw new UnmarshallingException($msg, UnmarshallingException::NOT_PCI, $e);
            }
        } elseif ($unit['base'] === null) {
            return null;
        }
    }

    /**
     * Unmarshall a boolean JSON PCI representation.
     *
     * @param array $unit
     * @return QtiBoolean
     */
    protected function unmarshallBoolean(array $unit)
    {
        return new QtiBoolean($unit['base']['boolean']);
    }

    /**
     * Unmarshall an integer JSON PCI representation.
     *
     * @param array $unit
     * @return QtiInteger
     */
    protected function unmarshallInteger(array $unit)
    {
        return new QtiInteger($unit['base']['integer']);
    }

    /**
     * Unmarshall a float JSON PCI representation.
     *
     * @param array $unit
     * @return QtiFloat
     */
    protected function unmarshallFloat(array $unit)
    {
        $val = $unit['base']['float'];

        if (is_int($val) === true) {
            $val = floatval($val);
        }

        return new QtiFloat($val);
    }

    /**
     * Unmarshall a string JSON PCI representation.
     *
     * @param array $unit
     * @return QtiString
     */
    protected function unmarshallString(array $unit)
    {
        return new QtiString($unit['base']['string']);
    }

    /**
     * Unmarshall a point JSON PCI representation.
     *
     * @param array $unit
     * @return QtiPoint
     */
    protected function unmarshallPoint(array $unit)
    {
        return new QtiPoint($unit['base']['point'][0], $unit['base']['point'][1]);
    }

    /**
     * Unmarshall a pair JSON PCI representation.
     *
     * @param array $unit
     * @return QtiPair
     */
    protected function unmarshallPair(array $unit)
    {
        return new QtiPair($unit['base']['pair'][0], $unit['base']['pair'][1]);
    }

    /**
     * Unmarshall a directed pair JSON PCI representation.
     *
     * @param array $unit
     * @return QtiDirectedPair
     */
    protected function unmarshallDirectedPair(array $unit)
    {
        return new QtiDirectedPair($unit['base']['directedPair'][0], $unit['base']['directedPair'][1]);
    }

    /**
     * Unmarshall a duration JSON PCI representation.
     *
     * @param array $unit
     * @return QtiDuration
     */
    protected function unmarshallDuration(array $unit)
    {
        return new QtiDuration($unit['base']['duration']);
    }

    /**
     * Unmarshall a duration JSON PCI representation.
     *
     * @param array $unit
     * @return QtiFile
     * @throws FileManagerException
     */
    protected function unmarshallFile(array $unit)
    {
        $filename = (empty($unit['base']['file']['name']) === true) ? '' : $unit['base']['file']['name'];

        return $this->getFileManager()->createFromData(base64_decode($unit['base']['file']['data']), $unit['base']['file']['mime'], $filename);
    }

    /**
     * Unmarshall a duration JSON PCI representation.
     *
     * @param array $unit
     * @return QtiUri
     */
    protected function unmarshallUri(array $unit)
    {
        return new QtiUri($unit['base']['uri']);
    }

    /**
     * Unmarshall an intOrIdentifier JSON PCI representation.
     *
     * @param array $unit
     * @return QtiIntOrIdentifier
     */
    protected function unmarshallIntOrIdentifier(array $unit)
    {
        return new QtiIntOrIdentifier($unit['base']['intOrIdentifier']);
    }

    /**
     * Unmarshall an identifier JSON PCI representation.
     *
     * @param array $unit
     * @return QtiIdentifier
     */
    protected function unmarshallIdentifier(array $unit)
    {
        return new QtiIdentifier($unit['base']['identifier']);
    }
}
