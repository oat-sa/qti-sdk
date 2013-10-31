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


namespace qtism\data\storage\xml\marshalling;

use qtism\data\content\BodyElement;
use qtism\data\content\SimpleBlock;
use qtism\data\content\SimpleInline;
use \DOMElement;
use \DOMNode;
use \DOMText;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use \InvalidArgumentException;

abstract class ContentMarshaller extends RecursiveMarshaller {
    
    public function __construct() {
        $this->setLookupClasses();
    }
    
    /**
     * 
     * @var array
     */
    protected $lookupClasses;
    
    private static $finals = array('textRun', 'br', 'param', 'hr', 'col', 'img', 'math', 'table',
                                      'printedVariable', 'stylesheet', 'choiceInteraction', 'orderInteraction',
                                      'associateInteraction', 'matchInteraction', 'gapMatchInteraction',
                                      'inlineChoiceInteraction', 'textEntryInteraction', 'extendedTextInteraction',
                                      'hottextInteraction', 'hotspotInteraction', 'selectPointInteraction',
                                      'graphicOrderInteraction', 'graphicAssociateInteraction', 'graphicGapMatchInteraction',
                                      'positionObjectInteraction', 'positionObjectStage', 'sliderInteraction', 'mediaInteraction',
                                      'drawingInteraction', 'uploadInteraction', 'customInteraction');
    
    private static $simpleInlines = array('a', 'abbr', 'acronym', 'b', 'big', 'cite', 'code', 'dfn', 'em', 'feedbackInline', 'i',
                                             'kbd', 'q', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'tt', 'var');
    
    protected function isElementFinal(DOMNode $element) {
        return $element instanceof DOMText || ($element instanceof DOMElement && in_array($element->nodeName, self::$finals));
    }
    
    protected function isComponentFinal(QtiComponent $component) { 
        return in_array($component->getQtiClassName(), self::$finals);
    }
    
    protected function createCollection(DOMElement $currentNode) {
        return new QtiComponentCollection();
    }
    
    protected function getChildrenComponents(QtiComponent $component) {
        if ($component instanceof SimpleInline) {
            return $component->getContent()->getArrayCopy();
        }
    }
    
    protected function getChildrenElements(DOMElement $element) {
        if (in_array($element->nodeName, self::$simpleInlines) === true) {
            return self::getChildElements($element, true);
        }
    }
    
    public function getExpectedQtiClassName() {
        return '';
    }
    
    /**
     * Fill $bodyElement with the following bodyElement:
     * 
     * * id
     * * class
     * * lang
     * * label
     * 
     * @param BodyElement $bodyElement The bodyElement to fill.
     * @param DOMElement $element The DOMElement object from where the attribute values must be retrieved.
     * @throws UnmarshallingException If one of the attributes of $element is not valid.
     */
    protected static function fillBodyElement(BodyElement $bodyElement, DOMElement $element) {
        
        try {
            $bodyElement->setId($element->getAttribute('id'));
            $bodyElement->setClass($element->getAttribute('class'));
            $bodyElement->setLang($element->getAttribute('lang'));
            $bodyElement->setLabel($element->getAttribute('label'));
        }
        catch (InvalidArgumentException $e) {
            $msg = "An error occured while filling the bodyElement attributes (id, class, lang, label).";
            throw new UnmarshallingException($msg, $element, $e);
        }
    }
    
    /**
     * Fill $element with the attributes of $bodyElement.
     * 
     * @param DOMElement $element The element from where the atribute values will be 
     * @param BodyElement $bodyElement The bodyElement to be fill.
     */
    protected function fillElement(DOMElement $element, BodyElement $bodyElement) {
        
        if (($id = $bodyElement->getId()) !== '') {
            $element->setAttribute('id', $id);
        }
        
        if (($class = $bodyElement->getClass()) !== '') {
            $element->setAttribute('class', $class);
        }
        
        if (($lang = $bodyElement->getLang()) !== '') {
            $element->setAttribute('lang', $lang);
        }
        
        if (($label = $bodyElement->getLabel()) != '') {
            $element->setAttribute('label', $label);
        }
    }
    
    protected abstract function setLookupClasses();
    
    /**
     * 
     * @return array
     */
    protected function getLookupClasses() {
        return $this->lookupClasses;
    }
    
    /**
     * Get the related PHP class name of a given $element.
     * 
     * @param DOMElement $element The element you want to know the data model PHP class.
     * @throws UnmarshallingException If no class can be found for $element.
     * @return string A fully qualified class name.
     */
    protected function lookupClass(DOMElement $element) {
        $lookup = $this->getLookupClasses();
        $class = ucfirst($element->nodeName);
        
        foreach ($lookup as $l) {
            $fqClass = $l . "\\" . $class;
        
            if (class_exists($fqClass) === true) {
                return $fqClass;
            }
        }

        $msg = "No class could be found for tag with name '" . $element->nodeName . "'.";
        throw new UnmarshallingException($msg, $element);
    }
}