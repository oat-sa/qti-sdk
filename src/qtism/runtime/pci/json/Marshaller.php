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
use qtism\common\datatypes\files\FileHash;
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
use qtism\common\datatypes\QtiScalar;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\State;

/**
 * This class aims at providing the necessary behaviours to
 * marshall QtiDataType objects into their JSON representation.
 *
 * The JSONified data respects the structure formulated by the IMS Global
 * Portable Custom Interaction Version 1.0 Candidate Final specification.
 *
 * @see http://www.imsglobal.org/assessment/pciv1p0cf/imsPCIv1p0cf.html#_Toc353965343
 */
class Marshaller
{
    /**
     * Output of marshalling as an array.
     *
     * @var int
     */
    const MARSHALL_ARRAY = 0;

    /**
     * Output of marshalling as JSON string.
     *
     * @var int
     */
    const MARSHALL_JSON = 1;

    /**
     * Create a new JSON Marshaller object.
     */
    public function __construct()
    {
    }

    /**
     * Marshall some QTI data into JSON.
     *
     * @param State|QtiDatatype|null $data The data to be marshalled into JSON.
     * @param int How the output will be returned (see class constants). Default is plain JSON string.
     * @return string|array The JSONified data.
     * @throws InvalidArgumentException If $data has not a compliant type.
     * @throws MarshallingException If an error occurs while marshalling $data into JSON.
     */
    public function marshall($data, $output = Marshaller::MARSHALL_JSON)
    {
        if ($data === null) {
            $json = ['base' => $data];
        } elseif ($data instanceof State) {
            $json = [];

            foreach ($data as $variable) {
                $json[$variable->getIdentifier()] = $this->marshallUnit($variable->getValue());
            }
        } elseif ($data instanceof QtiDatatype) {
            $json = $this->marshallUnit($data);
        } else {
            $className = get_class($this);
            $msg = "The '${className}::marshall' method only takes State, QtiDatatype and null values as arguments, '";

            if (is_object($data)) {
                $msg .= get_class($data);
            } else {
                $msg .= gettype($data);
            }

            $msg .= "' given.";
            $code = MarshallingException::NOT_SUPPORTED;
            throw new MarshallingException($msg, $code);
        }

        return ($output === self::MARSHALL_JSON) ? json_encode($json) : $json;
    }

    /**
     * Marshall a single unit of QTI data.
     *
     * @param State|QtiDatatype|null $unit
     * @return array An array representing the JSON data to be encoded later on.
     * @throws MarshallingException
     */
    protected function marshallUnit($unit)
    {
        if ($unit === null) {
            $json = ['base' => null];
        } elseif ($unit instanceof QtiScalar) {
            $json = $this->marshallScalar($unit);
        } elseif ($unit instanceof MultipleContainer) {
            $json = [];
            $strBaseType = BaseType::getNameByConstant($unit->getBaseType());
            $json['list'] = [$strBaseType => []];

            foreach ($unit as $u) {
                $data = $this->marshallUnit($u);
                $json['list'][$strBaseType][] = $data['base'][$strBaseType] ?? null;
            }
        } elseif ($unit instanceof RecordContainer) {
            $json = [];
            $json['record'] = [];

            foreach ($unit as $k => $u) {
                $data = $this->marshallUnit($u);
                $jsonEntry = [];
                $jsonEntry['name'] = $k;

                if (array_key_exists('base', $data)) {
                    // Primitive base type.
                    $jsonEntry['base'] = $data['base'];
                } else {
                    // A nested list.
                    $jsonEntry['list'] = $data['list'];
                }

                $json['record'][] = $jsonEntry;
            }
        } else {
            $json = $this->marshallComplex($unit);
        }

        return $json;
    }

    /**
     * Marshall a single scalar data into a PHP datatype (that can be transformed easilly in JSON
     * later on).
     *
     * @param QtiDatatype|null $scalar A scalar to be transformed into a PHP datatype for later JSON encoding.
     * @return array An array representing the JSON data to be encoded later on.
     * @throws MarshallingException
     */
    protected function marshallScalar($scalar)
    {
        if ($scalar === null) {
            return null;
        }

        if (!$scalar instanceof QtiDatatype) {
            $msg = sprintf("The '%s::marshallScalar' method only accepts to marshall NULL and Scalar QTI Datatypes, '%s' given.",
                get_class($this),
                is_object($scalar)
                    ? get_class($scalar)
                    : gettype($scalar)
            );

            throw new MarshallingException($msg, MarshallingException::NOT_SUPPORTED);
        }

        if ($scalar instanceof QtiBoolean) {
            return $this->marshallBoolean($scalar);
        } elseif ($scalar instanceof QtiInteger) {
            return $this->marshallInteger($scalar);
        } elseif ($scalar instanceof QtiFloat) {
            return $this->marshallFloat($scalar);
        } elseif ($scalar instanceof QtiIdentifier) {
            return $this->marshallIdentifier($scalar);
        } elseif ($scalar instanceof QtiUri) {
            return $this->marshallUri($scalar);
        } elseif ($scalar instanceof QtiString) {
            return $this->marshallString($scalar);
        } elseif ($scalar instanceof QtiIntOrIdentifier) {
            return $this->marshallIntOrIdentifier($scalar);
        } else {
            throw new MarshallingException('Unknown scalar type.', MarshallingException::NOT_SUPPORTED);
        }
    }

    /**
     * Marshall a single complex QtiDataType object.
     *
     * @param QtiDatatype $complex
     * @return array An array representing the JSON data to be encoded later on.
     * @throws MarshallingException
     */
    protected function marshallComplex(QtiDatatype $complex)
    {
        if ($complex === null) {
            return $complex;
        }

        if ($complex instanceof QtiPoint) {
            return $this->marshallPoint($complex);
        } elseif ($complex instanceof QtiDirectedPair) {
            return $this->marshallDirectedPair($complex);
        } elseif ($complex instanceof QtiPair) {
            return $this->marshallPair($complex);
        } elseif ($complex instanceof QtiDuration) {
            return $this->marshallDuration($complex);
        } elseif ($complex instanceof QtiFile) {
            return $this->marshallFile($complex);
        } else {
            $msg = sprintf("The '%s::marshallComplex' method only accepts to marshall Complex QTI Datatypes, '%s' given.",
                get_class($this),
                is_object($complex)
                    ? get_class($complex)
                    : gettype($complex)
            );

            throw new MarshallingException($msg, MarshallingException::NOT_SUPPORTED);
        }
    }

    /**
     * Marshall a QTI boolean datatype into its PCI JSON Representation.
     *
     * @param QtiBoolean $boolean
     * @return array
     */
    protected function marshallBoolean(QtiBoolean $boolean)
    {
        return ['base' => ['boolean' => $boolean->getValue()]];
    }

    /**
     * Marshall a QTI integer datatype into its PCI JSON Representation.
     *
     * @param QtiInteger $integer
     * @return array
     */
    protected function marshallInteger(QtiInteger $integer)
    {
        return ['base' => ['integer' => $integer->getValue()]];
    }

    /**
     * Marshall a QTI float datatype into its PCI JSON Representation.
     *
     * @param QtiFloat $float
     * @return array
     */
    protected function marshallFloat(QtiFloat $float)
    {
        return ['base' => ['float' => $float->getValue()]];
    }

    /**
     * Marshall a QTI identifier datatype into its PCI JSON Representation.
     *
     * @param QtiIdentifier $identifier
     * @return array
     */
    protected function marshallIdentifier(QtiIdentifier $identifier)
    {
        return ['base' => ['identifier' => $identifier->getValue()]];
    }

    /**
     * Marshall a QTI uri datatype into its PCI JSON Representation.
     *
     * @param QtiUri $uri
     * @return array
     */
    protected function marshallUri(QtiUri $uri)
    {
        return ['base' => ['uri' => $uri->getValue()]];
    }

    /**
     * Marshall a QTI string datatype into its PCI JSON Representation.
     *
     * @param QtiString $string
     * @return array
     */
    protected function marshallString(QtiString $string)
    {
        return ['base' => ['string' => $string->getValue()]];
    }

    /**
     * Marshall a QTI intOrIdentifier datatype into its PCI JSON Representation.
     *
     * @param QtiIntOrIdentifier $intOrIdentifier
     * @return array
     */
    protected function marshallIntOrIdentifier(QtiIntOrIdentifier $intOrIdentifier)
    {
        return ['base' => ['intOrIdentifier' => $intOrIdentifier->getValue()]];
    }

    /**
     * Marshall a QTI point datatype into its PCI JSON Representation.
     *
     * @param QtiPoint $point
     * @return array
     */
    protected function marshallPoint(QtiPoint $point)
    {
        return ['base' => ['point' => [$point->getX(), $point->getY()]]];
    }

    /**
     * Marshall a QTI directedPair datatype into its PCI JSON Representation.
     *
     * @param QtiDirectedPair $directedPair
     * @return array
     */
    protected function marshallDirectedPair(QtiDirectedPair $directedPair)
    {
        return ['base' => ['directedPair' => [$directedPair->getFirst(), $directedPair->getSecond()]]];
    }

    /**
     * Marshall a QTI pair datatype into its PCI JSON Representation.
     *
     * @param QtiPair $pair
     * @return array
     */
    protected function marshallPair(QtiPair $pair)
    {
        return ['base' => ['pair' => [$pair->getFirst(), $pair->getSecond()]]];
    }

    /**
     * Marshall a QTI duration datatype into its PCI JSON Representation.
     *
     * @param QtiDuration $duration
     * @return array
     */
    protected function marshallDuration(QtiDuration $duration)
    {
        return ['base' => ['duration' => $duration->__toString()]];
    }

    /**
     * Marshall a QTI file datatype into its PCI JSON Representation.
     *
     * @param QtiFile $file
     * @return array
     */
    protected function marshallFile(QtiFile $file)
    {
        $fileKey = $file instanceof FileHash ? FileHash::FILE_HASH_KEY : 'file';

        $data = [
            'base' => [
                $fileKey => [
                    'mime' => $file->getMimeType(),
                    'data' => base64_encode($file->getData())
                ]
            ]
        ];

        if ($file->hasFilename()) {
            $data['base'][$fileKey]['name'] = $file->getFilename();
        }

        if ($file instanceof FileHash) {
            $data['base'][$fileKey]['path'] = $file->getPath();
        }

        return $data;
    }
}
