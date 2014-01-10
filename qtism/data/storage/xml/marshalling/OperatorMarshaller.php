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

use qtism\data\expressions\operators\CustomOperator;
use qtism\common\utils\Reflection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Operator;
use \DOMElement;
use \DOMNode;
use \ReflectionClass;

/**
 * The OperatorMarshaller class focuses on Marshaller/Unmarshalling
 * the QTI Operators (a.k.a. hierarchical expressions).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OperatorMarshaller extends RecursiveMarshaller {
	
	private static $operators = array('roundTo',
			'statsOperator',
			'max',
			'min',
			'mathOperator',
			'gcd',
			'lcm',
			'repeat',
			'multiple',
			'ordered',
			'containerSize',
			'isNull',
			'index',
			'fieldValue',
			'random',
			'member',
			'delete',
			'contains',
			'substring',
			'not',
			'and',
			'or',
			'anyN',
			'match',
			'stringMatch',
			'patternMatch',
			'equal',
			'equalRounded',
			'inside',
			'lt',
			'gt',
			'lte',
			'gte',
			'durationLT',
			'durationGTE',
			'sum',
			'product',
			'subtract',
			'divide',
			'power',
			'integerDivide',
			'integerModulus',
			'truncate',
			'round',
			'integerToFloat',
			'customOperator');
	
	/**
	 * Get the list of operator QTI class names.
	 *
	 * @return array An array of string.
	 */
	public static function getOperators() {
		return self::$operators;
	}
	
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		
		// Some exceptions applies on instanciation e.g. the And operator is named
		// AndOperator because of PHP reserved words restriction.
		
		if ($element->localName === 'and') {
			$className = 'qtism\\data\\expressions\\operators\\AndOperator';
		}
		else if ($element->localName === 'or') {
			$className = 'qtism\\data\\expressions\\operators\\OrOperator';
		}
		else {
			$className = 'qtism\\data\\expressions\\operators\\' . ucfirst($element->localName);
		}
		
		$class = new ReflectionClass($className);
		$params = array($children);
		
		if ($element->localName === 'customOperator') {
		    // Retrieve XML content as a string. 
		    $params[] = $element->ownerDocument->saveXML($element);
		    $component = Reflection::newInstance($class, $params);
		    
		    if (($class = self::getDOMElementAttributeAs($element, 'class')) !== null) {
		        $component->setClass($class);
		    }
		    
		    if (($definition = self::getDOMElementAttributeAs($element, 'definition')) !== null) {
		        $component->setDefinition($definition);
		    }
		    
		    return $component;
		}
		else {
		    return Reflection::newInstance($class, $params);
		}
	}
	
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		
		$element = self::getDOMCradle()->createElement($component->getQtiClassName());
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		if ($component instanceof CustomOperator) {
		    if ($component->hasClass() === true) {
		        self::setDOMElementAttribute($element, 'class', $component->getClass());
		    }
		    
		    if ($component->hasDefinition() === true) {
		        self::setDOMElementAttribute($element, 'definition', $component->getDefinition());
		    }
		    
		    // Now, we have to extract the LAX content of the custom operator and put it into
		    // what we are putting out. (It is possible to have no LAX content at all, it is not mandatory).
		    $xml = $component->getXml();
		    if ($xml->ownerDocument !== null) {
		        $operatorElt = $xml->ownerDocument->cloneNode();
		        $qtiOperatorElts = self::getChildElementsByTagName($operatorElt, self::$operators);
		        foreach ($qtiOperatorElts as $qtiOperatorElt) {
		            $operatorElt->removeChild($qtiOperatorElt);
		        }
		        
		        for ($i = 0; $i < $operatorElt->childNodes; $i++) {
		            $node = $element->ownerDocument->importNode($operatorElt->childNodes->item($i));
		            $element->appendChild($node);
		        }
		    }
		    
		    
		}
		
		return $element;
	}
	
	protected function isElementFinal(DOMNode $element) {
		return !in_array($element->localName, static::getOperators());
	}
	
	protected function isComponentFinal(QtiComponent $component) {
		return !$component instanceof Operator;
	}
	
	protected function getChildrenElements(DOMElement $element) {
		return self::getChildElements($element);
	}
	
	protected function getChildrenComponents(QtiComponent $component) {
		if ($component instanceof Operator) {
			return $component->getExpressions()->getArrayCopy();
		}
		else {
			return array();
		}
	}
	
	protected function createCollection(DOMElement $currentNode) {
		return new ExpressionCollection();
	}
	
	public function getExpectedQtiClassName() {
		return '';
	}
}
