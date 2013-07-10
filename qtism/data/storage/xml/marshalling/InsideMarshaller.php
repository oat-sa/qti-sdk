<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\Inside;
use qtism\common\datatypes\Shape;
use qtism\common\datatypes\Coords;
use qtism\data\storage\Utils;
use \DOMElement;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of inside QTI operators.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class InsideMarshaller extends OperatorMarshaller {
	
	/**
	 * Unmarshall an Inside object into a QTI inside element.
	 * 
	 * @param QtiComponent The Inside object to marshall.
	 * @param array An array of child DOMEelement objects.
	 * @return DOMElement The marshalled QTI inside element.
	 */
	protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
		$element = self::getDOMCradle()->createElement($component->getQTIClassName());
		self::setDOMElementAttribute($element, 'shape', Shape::getNameByConstant($component->getShape()));
		self::setDOMElementAttribute($element, 'coords', $component->getCoords());
		
		foreach ($elements as $elt) {
			$element->appendChild($elt);
		}
		
		return $element;
	}
	
	/**
	 * Unmarshall a QTI inside operator element into an Inside object.
	 *
	 * @param DOMElement The inside element to unmarshall.
	 * @param QtiComponentCollection A collection containing the child Expression objects composing the Operator.
	 * @return QtiComponent An Inside object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
		if (($shape = static::getDOMElementAttributeAs($element, 'shape')) !== null) {
			
			if (($coords = static::getDOMElementAttributeAs($element, 'coords')) !== null ) {
				
				$shape = Shape::getConstantByName($shape);
				$coords = Utils::stringToCoords($coords, $shape);
				
				$object = new Inside($children, $shape, $coords);
				return $object;
			}
			else {
				$msg = "The mandatory attribute 'coords' is missing from element '" . $element->nodeName . "'.";
				throw new UnmarshallingException($msg, $element);
			}
		}
		else {
			$msg = "The mandatory attribute 'shape' is missing from element '" . $element->nodeName . "'.";
			throw new UnmarshallingException($msg, $element);
		}
	}
}