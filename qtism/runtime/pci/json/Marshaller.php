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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 *  
 *
 */

namespace qtism\runtime\pci\json;

use qtism\common\datatypes\QtiFile;
use qtism\common\enums\BaseType;
use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiScalar;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\State;
use qtism\common\datatypes\QtiDatatype;
use \InvalidArgumentException;

/**
 * This class aims at providing the necessary behaviours to
 * marshall QtiDataType objects into their JSON representation.
 * 
 * The JSONified data respects the structure formulated by the IMS Global
 * Portable Custom Interaction Version 1.0 Candidate Final specification.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @see http://www.imsglobal.org/assessment/pciv1p0cf/imsPCIv1p0cf.html#_Toc353965343
 */
class Marshaller {
    
    /**
     * Output of marshalling as an array.
     * 
     * @var integer
     */
    const MARSHALL_ARRAY = 0;
    
    /**
     * Output of marshalling as JSON string.
     * 
     * @var integer
     */
    const MARSHALL_JSON = 1;
    
    /**
     * Create a new JSON Marshaller object.
     * 
     */
    public function __construct() {
        
    }
    
    /**
     * Marshall some QTI data into JSON.
     * 
     * @param State|QtiDatatype|null $data The data to be marshalled into JSON.
     * @param integer How the output will be returned (see class constants). Default is plain JSON string.
     * @return string|array The JSONified data.
     * @throws InvalidArgumentException If $data has not a compliant type.
     * @throws MarshallingException If an error occurs while marshalling $data into JSON.
     */
    public function marshall($data, $output = Marshaller::MARSHALL_JSON) {
        
        if (is_null($data) === true) {
            $json = array('base' => $data);
        }
        else if ($data instanceof State) {
            
            $json = array();
            
            foreach ($data as $variable) {
                $json[$variable->getIdentifier()] = $this->marshallUnit($variable->getValue());
            }
        }
        else if ($data instanceof QtiDatatype) {
            $json = $this->marshallUnit($data);
        }
        else {
            $className = get_class($this);
            $msg = "The '${className}::marshall' method only takes State, QtiDatatype and null values as arguments, '";
            
            if (is_object($data) === true) {
                $msg .= get_class($data);
            }
            else {
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
     */
    protected function marshallUnit($unit) {
        if (is_null($unit) === true) {
            $json = array('base' => null);
        }
        else if ($unit instanceof QtiScalar) {
            $json = $this->marshallScalar($unit);
        }
        else if ($unit instanceof MultipleContainer) {
            $json = array();
            $strBaseType = BaseType::getNameByConstant($unit->getBaseType());
            $json['list'] = array($strBaseType => array());
        
            foreach ($unit as $u) {
                $data = $this->marshallUnit($u);
                $json['list'][$strBaseType][] = $data['base'][$strBaseType];
            }
        }
        else if ($unit instanceof RecordContainer) {
            $json = array();
            $json['record'] = array();
            
            foreach ($unit as $k => $u) {
                $data = $this->marshallUnit($u);
                $jsonEntry = array();
                $jsonEntry['name'] = $k;
                
                if (isset($data['base']) === true || $data['base'] === null) {
                    // Primitive base type.
                    $jsonEntry['base'] = $data['base'];
                }
                else {
                    // A nested list.
                    $jsonEntry['list'] = $data['list'];
                }
                
                $json['record'][] = $jsonEntry;
            }
        }
        else {
            $json = $this->marshallComplex($unit);
        }
        
        return $json;
    }
    
    /**
     * Marshall a single scalar data into a PHP datatype (that can be transformed easilly in JSON
     * later on).
     * 
     * @param null|QtiDatatype $scalar A scalar to be transformed into a PHP datatype for later JSON encoding.
     * @return array An array representing the JSON data to be encoded later on.
     * @throws MarshallingException
     */
    protected function marshallScalar($scalar) {
        if (is_null($scalar) === true) {
            return $scalar;
        }
        else if ($scalar instanceof QtiDatatype) {
            if ($scalar instanceof QtiBoolean) {
                return $this->marshallBoolean($scalar);
            }
            else if ($scalar instanceof QtiInteger) {
                return $this->marshallInteger($scalar);
            }
            else if ($scalar instanceof QtiFloat) {
                return $this->marshallFloat($scalar);
            }
            else if ($scalar instanceof QtiIdentifier) {
                return $this->marshallIdentifier($scalar);
            }
            else if ($scalar instanceof QtiUri) {
                return $this->marshallUri($scalar);
            }
            else if ($scalar instanceof QtiString) {
                return $this->marshallString($scalar);
            }
            else if ($scalar instanceof QtiIntOrIdentifier) {
                return $this->marshallIntOrIdentifier($scalar);
            }
        }
        else {
            $msg = "The '" . get_class($this) . "::marshallScalar' method only accepts to marshall NULL and Scalar QTI Datatypes, '";
            if (is_object($scalar) === true) {
                $msg .= get_class($scalar);
            }
            else {
                $msg .= gettype($scalar);
            }
            
            $msg .= "' given.";
            $code = MarshallingException::NOT_SUPPORTED;
            throw new MarshallingException($msg, $code);
        }
    }
    
    /**
     * Marshall a single complex QtiDataType object.
     * 
     * @param QtiDatatype $complex
     * @throws MarshallingException
     * @return array An array representing the JSON data to be encoded later on.
     */
    protected function marshallComplex(QtiDatatype $complex) {
        if (is_null($complex) === true) {
            return $complex;
        }
        else if ($complex instanceof QtiPoint) {
            return $this->marshallPoint($complex);
        }
        else if ($complex instanceof QtiDirectedPair) {
            return $this->marshallDirectedPair($complex);
        }
        else if ($complex instanceof QtiPair) {
            return $this->marshallPair($complex);
        }
        else if ($complex instanceof QtiDuration) {
            return $this->marshallDuration($complex);
        }
        else if ($complex instanceof QtiFile) {
            return $this->marshallFile($complex);
        }
        else {
            $msg = "The '" . get_class($this) . "::marshallComplex' method only accepts to marshall Complex QTI Datatypes, '";
            if (is_object($scalar) === true) {
                $msg .= get_class($complex);
            }
            else {
                $msg .= gettype($complex);
            }
            
            $msg .= "' given.";
            $code = MarshallingException::NOT_SUPPORTED;
            throw new MarshallingException($msg, $code);
        }
    }
    
    /**
     * Marshall a QTI boolean datatype into its PCI JSON Representation.
     * 
     * @param Boolean $boolean
     * @return array
     */
    protected function marshallBoolean(QtiBoolean $boolean) {
        return array('base' => array('boolean' => $boolean->getValue()));
    }
    
    /**
     * Marshall a QTI integer datatype into its PCI JSON Representation.
     *
     * @param Integer $integer
     * @return array
     */
    protected function marshallInteger(QtiInteger $integer) {
        return array('base' => array('integer' => $integer->getValue()));
    }
    
    /**
     * Marshall a QTI float datatype into its PCI JSON Representation.
     *
     * @param Float $float
     * @return array
     */
    protected function marshallFloat(QtiFloat $float) {
        return array('base' => array('float' => $float->getValue()));
    }
    
    /**
     * Marshall a QTI identifier datatype into its PCI JSON Representation.
     *
     * @param Identifier $identifier
     * @return array
     */
    protected function marshallIdentifier(QtiIdentifier $identifier) {
        return array('base' => array('identifier' => $identifier->getValue()));
    }
    
    /**
     * Marshall a QTI uri datatype into its PCI JSON Representation.
     *
     * @param Uri $uri
     * @return array
     */
    protected function marshallUri(QtiUri $uri) {
        return array('base' => array('uri' => $uri->getValue()));
    }
    
    /**
     * Marshall a QTI string datatype into its PCI JSON Representation.
     *
     * @param String $string
     * @return array
     */
    protected function marshallString(QtiString $string) {
        return array('base' => array('string' => $string->getValue()));
    }
    
    /**
     * Marshall a QTI intOrIdentifier datatype into its PCI JSON Representation.
     *
     * @param IntOrIdentifier $intOrIdentifier
     * @return array
     */
    protected function marshallIntOrIdentifier(QtiIntOrIdentifier $intOrIdentifier) {
        return array('base' => array('intOrIdentifier' => $intOrIdentifier->getValue()));
    }
    
    /**
     * Marshall a QTI point datatype into its PCI JSON Representation.
     *
     * @param Point $point
     * @return array
     */
    protected function marshallPoint(QtiPoint $point) {
        return array('base' => array('point' => array($point->getX(), $point->getY())));
    }
    
    /**
     * Marshall a QTI directedPair datatype into its PCI JSON Representation.
     *
     * @param DirectedPair $directedPair
     * @return array
     */
    protected function marshallDirectedPair(QtiDirectedPair $directedPair) {
        return array('base' => array('directedPair' => array($directedPair->getFirst(), $directedPair->getSecond())));
    }
    
    /**
     * Marshall a QTI pair datatype into its PCI JSON Representation.
     *
     * @param Pair $pair
     * @return array
     */
    protected function marshallPair(QtiPair $pair) {
        return array('base' => array('pair' => array($pair->getFirst(), $pair->getSecond())));
    }
    
    /**
     * Marshall a QTI duration datatype into its PCI JSON Representation.
     *
     * @param Duration $duration
     * @return array
     */
    protected function marshallDuration(QtiDuration $duration) {
        return array('base' => array('duration' => $duration->__toString()));
    }
    
    /**
     * Marshall a QTI file datatype into its PCI JSON Representation.
     *
     * @param File $file
     * @return array
     */
    protected function marshallFile(QtiFile $file) {
        $data = array('base' => array('file' => array('mime' => $file->getMimeType(), 'data' => base64_encode($file->getData()))));
        
        if ($file->hasFilename() === true) {
            $data['base']['file']['name'] = $file->getFilename();
        }
        
        return $data;
    }
}
