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

namespace qtism\data\content;

use qtism\data\QtiComponentCollection;
use qtism\data\IExternal;
use qtism\data\ExternalQtiComponent;

/**
 * From IMS QTI:
 * 
 * The infoControl element is a means to provide the candidate with extra information about 
 * the item when s/he chooses to trigger the control. The extra information can be a hint, 
 * but could also be additional tools such as a ruler or a (javaScript) calculator.
 * 
 * Unlike endAttemptInteraction, triggering infoControl has no consequence for response processing.
 * That means that its triggering won't be recorded, nor the candidate penalised for triggering it.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class InfoControl extends BodyElement implements BlockStatic, FlowStatic, IExternal {
    
    /**
     * The base URI of the InfoControl.
     *
     * @var string
     * @qtism-bean-property
     */
    private $xmlBase = '';
    
    /**
     * 
     * @var string
     * @qtism-bean-property
     */
    private $xmlString;
    
    /**
     * 
     * @var ExternalQtiComponent
     */
    private $externalComponent;
    
    /**
     * Create a new InfoControl object.
     * 
     * @param string $xmlString The XML content representing the infoControl.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws \InvalidArgumentException If any of the above argument is invalid.
     */
    public function __construct($xmlString, $id = '', $class = '', $lang = '', $label = '') {
        parent::__construct($id, $class, $lang, $label);
        $this->setXmlString($xmlString);
        $this->setExternalComponent(new ExternalQtiComponent($xmlString));
    }
    
    /**
     * 
     * @param string $xmlString
     */
    public function setXmlString($xmlString) {
        $this->xmlString = $xmlString;
    }
    
    /**
     * 
     * @return string
     */
    public function getXmlString() {
        return $this->xmlString;
    }
    
    /**
     * Set the encapsulated ExternalQtiComponent object.
     * 
     * @param ExternalQtiComponent $externalComponent
     */
    private function setExternalComponent(ExternalQtiComponent $externalComponent) {
        $this->externalComponent = $externalComponent;
    }
    
    /**
     * Get the encapsulated ExternalQtiComponent object.
     * 
     * @return ExternalQtiComponent
     */
    private function getExternalComponent() {
        return $this->externalComponent;
    }
    
    /**
     * Get the DOMDocument object corresponding to the XML content of the
     * infoControl.
     * 
     * @return DOMDocument
     * @throws \RuntimeException If the XML content is invalid.
     */
    public function getXml() {
        return $this->getExternalComponent()->getXml();
    }
    
    /**
     * Set the base URI of the InfoControl.
     *
     * @param string $xmlBase A URI.
     * @throws InvalidArgumentException if $base is not a valid URI nor an empty string.
     */
    public function setXmlBase($xmlBase = '') {
        if (is_string($xmlBase) && (empty($xmlBase) || Format::isUri($xmlBase))) {
            $this->xmlBase = $xmlBase;
        }
        else {
            $msg = "The 'xmlBase' argument must be an empty string or a valid URI, '" . $xmlBase . "' given";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the base URI of the InfoControl.
     *
     * @return string An empty string or a URI.
     */
    public function getXmlBase() {
        return $this->xmlBase;
    }
    
    public function hasXmlBase() {
        return $this->getXmlBase() !== '';
    }
    
    public function getQtiClassName() {
        return 'infoControl';
    }
    
    public function getComponents() {
        return new QtiComponentCollection();
    }
}