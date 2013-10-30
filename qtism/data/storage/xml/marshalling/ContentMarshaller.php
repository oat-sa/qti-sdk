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

use qtism\data\content\SimpleBlock;

use qtism\data\content\SimpleInline;
use \DOMElement;
use \DOMNode;
use \DOMText;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;

abstract class ContentMarshaller extends RecursiveMarshaller {
    
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
}