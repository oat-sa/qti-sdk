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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package
 */

namespace qtism\data;

use \DOMDocument;
use \RuntimeException;

/**
 * Represents a gateway to a component from an external (non-QTI) namespace.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class ExternalQtiComponent extends QtiComponent {
    
    /**
     * 
     * @var string
     * @qtism-bean-property
     */
    private $xmlString = '';
    
    private $xml = null;
    
    /**
     * 
     * @var string
     * @qtism-bean-property
     */
    private $targetNamespace = '';
    
    /**
     * Create a new ExternalQtiComponent from its $xml string representation. The constructor
     * takes the content representation of the external component as a string because of performance
     * issues. Indeed, the related DOMDocument object will be generated on demand via the getXml() method.
     * 
     * @param string $xmlString An XML string.
     */
    public function __construct($xmlString) {
        $this->buildTargetNamespace();
        $this->setXmlString($xmlString);
    }
    
    /**
     * Returns the XML representation of the external component as
     * a DOMDocument object.
     * 
     * @return DOMDocument|false A DOMDocument object representing the content of the external component.
     * @throws RuntimeException If the root element of the XML representation is not from the target namespace or the XML could not be parsed.
     */
    public function getXml() {
        // Build the DOMDocument object only on demand.
        if ($this->xml === null) {
            
            $xml = new DOMDocument('1.0', 'UTF-8');
            if (@$xml->loadXML($this->getXmlString()) === false) {
                $msg = "The XML content '" . $this->getXmlString() . "' of the '" . $this->getQtiClassName() . "' external component could not be parsed correctly.";
                throw new RuntimeException($msg);
            }
            
            if ($xml->documentElement->namespaceURI !== $this->getTargetNamespace()) {
                $msg = "The XML content' " . $this->getXmlString() . "' of the '" . $this->getQtiClassName() . "' external component is not referenced into target namespace '" . $this->getTargetNamespace() . "'.";
                throw new RuntimeException($msg);
            }
            
            $this->setXml($xml);
        }
        
        return $this->xml;
    }
    
    /**
     * Set the XML representation of the external component from
     * an XML string.
     * 
     * @param string An XML String
     */
    protected function setXml(DOMDocument $xml) {
        $this->xml = $xml;
    }
    
    /**
     * Get the XML String representation of the external component.
     * 
     * @return string
     */
    protected function getXmlString() {
        return $this->xmlString;
    }
    
    /**
     * Set the XML String representation of the external component.
     * 
     * @param string $xmlString
     */
    protected function setXmlString($xmlString) {
        $this->xmlString = $xmlString;
    }
    
    public function getTargetNamespace() {
        return $this->targetNamespace;
    }
    
    protected function setTargetNamespace($targetNamespace) {
        $this->targetNamespace = $targetNamespace;
    }
    
    protected abstract function buildTargetNamespace();
    
    public function getComponents() {
        return new QtiComponentCollection();
    }
}