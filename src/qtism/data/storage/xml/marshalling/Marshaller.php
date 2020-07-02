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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use qtism\common\utils\Version;
use qtism\data\content\BodyElement;
use qtism\data\content\Direction;
use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\Utils as XmlUtils;
use RuntimeException;

abstract class Marshaller
{
    /**
     * The DOMCradle is a DOMDocument object which will be used as a 'DOMElement cradle'. It
     * gives the opportunity to marshallers to create DOMElement that can be imported in an
     * exported document later on.
     *
     * @var DOMDocument
     */
    private static $DOMCradle = null;

    /**
     * A reference to the Marshaller Factory to use when creating other marshallers
     * from this marshaller.
     *
     * @var MarshallerFactory
     */
    private $marshallerFactory = null;

    /**
     * The version on which the Marshaller operates.
     *
     * @var string
     */
    private $version;

    /**
     * An array containing the name of classes
     * that are allowed to have their 'dir' attribute set.
     *
     * @var string[]
     */
    private static $dirClasses = [
        'associateInteraction',
        'choiceInteraction',
        'drawingInteraction',
        'extendedTextInteraction',
        'gapMatchInteraction',
        'graphicAssociateInteraction',
        'hotspotInteraction',
        'hottextInteraction',
        'matchInteraction',
        'mediaInteraction',
        'orderInteraction',
        'selectPointInteraction',
        'sliderInteraction',
        'uploadInteraction',
        'bdo',
        'caption',
        'colgroup',
        'gapImg',
        'gapText',
        'infoControl',
        'inlineChoice',
        'li',
        'prompt',
        'simpleAssociableChoice',
        'simpleChoice',
        'stimulusBody',
        'tbody',
        'tfoot',
        'thead',
        'td',
        'th',
        'tr',
        'customInteraction',
        'graphicGapMatchInteraction',
        'graphicOrderInteraction',
        'inlineChoiceInteraction',
        'positionObjecInteraction',
        'a',
        'dd',
        'div',
        'dl',
        'dt',
        'feedbackBlock',
        'feedbackInline',
        'hottext',
        'abbr',
        'acronym',
        'address',
        'b',
        'big',
        'cite',
        'code',
        'dfn',
        'em',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'i',
        'kbd',
        'p',
        'pre',
        'samp',
        'small',
        'span',
        'strong',
        'sub',
        'sup',
        'tt',
        'var',
        'br',
        'col',
        'hr',
        'img',
        'q',
        'label',
        'object',
        'ul',
        'rubricBlock',
        'table',
        'templateBlock',
        'templateInline',
        'hottext',
    ];

    /**
     * An array containing the QTI class names that are allowed to be Web Component friendly.
     *
     * @var string[]
     */
    public static $webComponentFriendlyClasses = [
        'associableHotspot',
        'gap',
        'gapImg',
        'gapText',
        'simpleAssociableChoice',
        'hotspotChoice',
        'hottext',
        'inlineChoice',
        'simpleChoice',
        'associateInteraction',
        'choiceInteraction',
        'drawingInteraction',
        'extendedTextInteraction',
        'gapMatchInteraction',
        'graphicAssociateInteraction',
        'graphicGapMatchInteraction',
        'graphicOrderInteraction',
        'hotspotInteraction',
        'selectPointInteraction',
        'hottextInteraction',
        'matchInteraction',
        'mediaInteraction',
        'orderInteraction',
        'sliderInteraction',
        'uploadInteraction',
        'customInteraction',
        'endAttemptInteraction',
        'inlineChoiceInteraction',
        'textEntryInteraction',
        'positionObjectInteraction',
        'positionObjectStage',
        'printedVariable',
        'prompt',
        'feedbackBlock',
        'feedbackInline',
        'rubricBlock',
        'templateBlock',
        'templateInline',
        'infoControl',
    ];

    /**
     * An array containing QTI class names preferring aria-flowsto instead of aria-flowto.
     *
     * @var string[]
     */
    private static $flowsToClasses = [
        'associateInteraction',
        'choiceInteraction',
        'drawingInteraction',
        'extendedTextInteraction',
        'gapMatchInteraction',
        'graphicAssociateInteraction',
        'hotspotInteraction',
        'hottextInteraction',
        'matchInteraction',
        'mediaInteraction',
        'orderInteraction',
        'selectPointInteraction',
        'sliderInteraction',
        'uploadInteraction',
        'associableHotspot',
        'br',
        'col',
        'endAttemptInteraction',
        'gap',
        'hotspotChoice',
        'hr',
        'img',
        'textEntryInteraction'
    ];

    /**
     * Create a new Marshaller object.
     *
     * @param string $version The QTI version on which the Marshaller operates e.g. '2.1'.
     */
    public function __construct($version)
    {
        $this->setVersion($version);
    }

    /**
     * Get a DOMDocument to be used by marshaller implementations in order to create
     * new nodes to be imported in a currenlty exported document.
     *
     * @return DOMDocument A unique DOMDocument object.
     */
    protected static function getDOMCradle()
    {
        if (empty(self::$DOMCradle)) {
            self::$DOMCradle = new DOMDocument('1.0', 'UTF-8');
        }

        return self::$DOMCradle;
    }

    /**
     * Set the MarshallerFactory object to use to create other Marshaller objects.
     *
     * @param MarshallerFactory $marshallerFactory A MarshallerFactory object.
     */
    public function setMarshallerFactory(MarshallerFactory $marshallerFactory = null)
    {
        $this->marshallerFactory = $marshallerFactory;
    }

    /**
     * Return the MarshallerFactory object to use to create other Marshaller objects.
     * If no MarshallerFactory object was previously defined, a default 'raw' MarshallerFactory
     * object will be returned.
     *
     * @return MarshallerFactory A MarshallerFactory object.
     */
    public function getMarshallerFactory()
    {
        if ($this->marshallerFactory === null) {
            $this->setMarshallerFactory(new Qti21MarshallerFactory());
        }

        return $this->marshallerFactory;
    }

    /**
     * Set the version on which the Marshaller operates.
     *
     * @param string $version A QTI version number e.g. '2.1'.
     */
    protected function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get the version on which the Marshaller operates.
     *
     * @return string A QTI version number e.g. '2.1'.
     */
    public function getVersion()
    {
        return $this->version;
    }

    public function __call($method, $args)
    {
        if ($method == 'marshall' || $method == 'unmarshall') {
            if (count($args) >= 1) {
                if ($method == 'marshall') {
                    $component = $args[0];
                    if ($component instanceof QtiComponent && ($this->getExpectedQtiClassName() === '' || ($component->getQtiClassName() == $this->getExpectedQtiClassName()))) {
                        return $this->marshall($component);
                    } else {
                        $componentName = $this->getComponentName($component);
                        throw new RuntimeException("No marshaller implementation found while marshalling component '${componentName}'.");
                    }
                } else {
                    $element = $args[0];
                    if ($element instanceof DOMElement && ($this->getExpectedQtiClassName() === '' || ($element->localName == $this->getExpectedQtiClassName()))) {
                        return call_user_func_array([$this, 'unmarshall'], $args);
                    } else {
                        $nodeName = $this->getElementName($element);
                        throw new RuntimeException("No Marshaller implementation found while unmarshalling element '${nodeName}'.");
                    }
                }
            } else {
                throw new RuntimeException("Method '${method}' only accepts a single argument.");
            }
        }

        throw new RuntimeException("Unknown method Marshaller::'${method}'.");
    }

    /**
     * Get Attribute Name to Use for Marshalling
     *
     * This method provides the attribute name to be used to retrieve an element attribute value
     * by considering whether or not the Marshaller implementation is running in Web Component
     * Friendly mode.
     *
     * Examples:
     *
     * In case of the Marshaller implementation IS NOT running in Web Component Friendly mode,
     * calling this method on an $element "choiceInteraction" and a "responseIdentifier" $attribute, the
     * "responseIdentifier" value is returned.
     *
     * On the other hand, in case of the Marshaller implementation IS running in Web Component Friendly mode,
     * calling this method on an $element "choiceInteraction" and a "responseIdentifier" $attribute, the
     * "response-identifier" value is returned.
     *
     * @param DOMElement $element
     * @param $attribute
     * @return string
     */
    protected function getAttributeName(DOMElement $element, $attribute)
    {
        if ($this->isWebComponentFriendly() === true && preg_match('/^qti-/', $element->localName) === 1) {
            $qtiFriendlyClassName = XmlUtils::qtiFriendlyName($element->localName);

            if (in_array($qtiFriendlyClassName, self::$webComponentFriendlyClasses) === true) {
                return XmlUtils::webComponentFriendlyAttributeName($attribute);
            }
        }

        return $attribute;
    }

    /**
     * Get the attribute value of a given DOMElement object, cast in a given datatype.
     *
     * @param DOMElement $element The element the attribute you want to retrieve the value is bound to.
     * @param string $attribute The attribute name.
     * @param string $datatype The returned datatype. Accepted values are 'string', 'integer', 'float', 'double' and 'boolean'.
     * @return mixed The attribute value with the provided $datatype, or null if the attribute does not exist in $element.
     * @throws InvalidArgumentException If $datatype is not in the range of possible values.
     */
    public function getDOMElementAttributeAs(DOMElement $element, $attribute, $datatype = 'string')
    {
        return XmlUtils::getDOMElementAttributeAs($element, $this->getAttributeName($element, $attribute), $datatype);
    }

    /**
     * Set the attribute value of a given DOMElement object. Boolean values will be transformed
     *
     * @param DOMElement $element A DOMElement object.
     * @param string $attribute An XML attribute name.
     * @param mixed $value A given value.
     */
    public function setDOMElementAttribute(DOMElement $element, $attribute, $value)
    {
        XmlUtils::setDOMElementAttribute($element, $this->getAttributeName($element, $attribute), $value);
    }

    /**
     * Set the node value of a given DOMElement object. Boolean values will be transformed as 'true'|'false'.
     *
     * @param DOMElement $element A DOMElement object.
     * @param mixed $value A given value.
     */
    public static function setDOMElementValue(DOMElement $element, $value)
    {
        switch (gettype($value)) {
            case 'boolean':
                $element->nodeValue = ($value === true) ? 'true' : 'false';
                break;

            default:
                $element->nodeValue = $value;
                break;
        }
    }

    /**
     * Get the first child DOM Node with nodeType attribute equals to XML_ELEMENT_NODE.
     * This is very useful to get a sub-node without having to exclude text nodes, cdata,
     * ... manually.
     *
     * @param DOMElement $element A DOMElement object
     * @return DOMElement|boolean A DOMElement If a child node with nodeType = XML_ELEMENT_NODE or false if nothing found.
     */
    public static function getFirstChildElement($element)
    {
        $children = $element->childNodes;
        for ($i = 0; $i < $children->length; $i++) {
            $child = $children->item($i);
            if ($child->nodeType === XML_ELEMENT_NODE) {
                return $child;
            }
        }

        return false;
    }

    /**
     * Get the children DOM Nodes with nodeType attribute equals to XML_ELEMENT_NODE.
     *
     * @param DOMElement $element A DOMElement object.
     * @param boolean $withText Wether text nodes must be returned or not.
     * @return array An array of DOMNode objects.
     */
    public static function getChildElements($element, $withText = false)
    {
        return XmlUtils::getChildElements($element, $withText);
    }

    /**
     * Get the child elements of a given element by tag name. This method does
     * not behave like DOMElement::getElementsByTagName. It only returns the direct
     * child elements that matches $tagName but does not go recursive.
     *
     * @param DOMElement $element A DOMElement object.
     * @param mixed $tagName The name of the tags you would like to retrieve or an array of tags to match.
     * @param boolean $exclude (optional) Whether the $tagName parameter must be considered as a blacklist.
     * @param boolean $withText (optional) Whether text nodes must be returned or not.
     * @return array An array of DOMElement objects.
     */
    public function getChildElementsByTagName($element, $tagName, $exclude = false, $withText = false)
    {
        if (is_array($tagName) === false) {
            $tagName = [$tagName];
        }

        if ($this->isWebComponentFriendly() === true) {
            foreach ($tagName as $key => $name) {
                if (in_array($name, self::$webComponentFriendlyClasses) === true) {
                    $tagName[$key] = XmlUtils::webComponentFriendlyClassName($name);
                }
            }
        }

        return XmlUtils::getChildElementsByTagName($element, $tagName, $exclude, $withText);
    }

    /**
     * Get the string value of the xml:base attribute of a given $element. The method
     * will return false if no xml:base attribute is defined for the $element or its value
     * is empty.
     *
     * @param DOMElement $element A DOMElement object you want to get the xml:base attribute value.
     * @return false|string The value of the xml:base attribute or false if it could not be retrieved.
     */
    public static function getXmlBase(DOMElement $element)
    {
        $returnValue = false;
        if (($xmlBase = $element->getAttributeNS('http://www.w3.org/XML/1998/namespace', 'base')) !== '') {
            $returnValue = $xmlBase;
        }

        return $returnValue;
    }

    /**
     * Set the value of the xml:base attribute of a given $element. If a value is already
     * defined for the xml:base attribute of the $element, the current value will be
     * overriden by $xmlBase.
     *
     * @param DOMElement $element The $element you want to set a value for xml:base.
     * @param string $xmlBase The value to be set to the xml:base attribute of $element.
     */
    public static function setXmlBase(DOMElement $element, $xmlBase)
    {
        $element->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'base', $xmlBase);
    }

    /**
     * @param BodyElement $bodyElement
     * @param DOMElement $element
     */
    protected function fillBodyElementFlowTo(BodyElement $bodyElement, DOMElement $element)
    {
        $scan = ['aria-flowto'];

        if (in_array($bodyElement->getQtiClassName(), self::$flowsToClasses, true)) {
            array_unshift($scan, 'aria-flowsto');
        }

        foreach ($scan as $s) {
            if (($ariaFlowTo = $this->getDOMElementAttributeAs($element, $s)) !== null) {
                $bodyElement->setAriaFlowTo($ariaFlowTo);

                break;
            }
        }
    }

    /**
     * Fill $bodyElement with the following bodyElement attributes:
     *
     * * id
     * * class
     * * lang
     * * label
     * * dir (QTI 2.2)
     *
     * @param BodyElement $bodyElement The bodyElement to fill.
     * @param DOMElement $element The DOMElement object from where the attribute values must be retrieved.
     * @throws UnmarshallingException If one of the attributes of $element is not valid.
     */
    protected function fillBodyElement(BodyElement $bodyElement, DOMElement $element)
    {
        try {
            $bodyElement->setId($element->getAttribute('id'));
            $bodyElement->setClass($element->getAttribute('class'));
            $bodyElement->setLang($element->getAttributeNS('http://www.w3.org/XML/1998/namespace', 'lang'));
            $bodyElement->setLabel($element->getAttribute('label'));

            $version = $this->getVersion();
            if (Version::compare($version, '2.2.0', '>=') === true) {
                // dir attribute
                if (($dir = $this->getDOMElementAttributeAs($element, 'dir')) !== null && in_array($element->localName, self::$dirClasses) === true) {
                    $bodyElement->setDir(Direction::getConstantByName($dir));
                }

                // aria-* attributes
                if ($element->localName !== 'printedVariable') {
                    // All QTI classes deal with aria-* except printedVariable.
                    if (($ariaControls = $this->getDOMElementAttributeAs($element, 'aria-controls')) !== null) {
                        $bodyElement->setAriaControls($ariaControls);
                    }

                    if (($ariaDescribedBy = $this->getDOMElementAttributeAs($element, 'aria-describedby')) !== null) {
                        $bodyElement->setAriaDescribedBy($ariaDescribedBy);
                    }

                    /*
                     * There is a little glitch in the QTI 2.2.X XSDs. Indeed, the following elements do not
                     * consider aria-flowto (the official one) but aria-flowsto which is an error: associateInteraction,
                     * choiceInteraction, drawingInteraction, extendedTextInteraction, gapMatchInteraction,
                     * graphicAssociateInteraction, hotspotInteraction, matchInteraction, mediaInteraction,
                     * orderInteraction, selectPointInteraction, sliderInteraction, uploadInteraction, associableHotspot,
                     * br, col, endAttemptInteraction, gap, hotspotChoice, hr, img, textEntryInteraction.
                     *
                     * In such a context, at unmarshalling time, for the elements described above, we prefer
                     * aria-flowsto (as described in the XSDs) as a first choice and then aria-flowto as a backup.
                     */
                    $this->fillBodyElementFlowTo($bodyElement, $element);

                    if (($ariaLabelledBy = $this->getDOMElementAttributeAs($element, 'aria-labelledby')) !== null) {
                        $bodyElement->setAriaLabelledBy($ariaLabelledBy);
                    }

                    if (($ariaOwns = $this->getDOMElementAttributeAs($element, 'aria-owns')) !== null) {
                        $bodyElement->setAriaOwns($ariaOwns);
                    }

                    if (($ariaLevel = $this->getDOMElementAttributeAs($element, 'aria-level')) !== null) {
                        $bodyElement->setAriaLevel($ariaLevel);
                    }

                    if (($ariaLive = $this->getDOMElementAttributeAs($element, 'aria-live')) !== null) {
                        $bodyElement->setAriaLive(AriaLive::getConstantByName($ariaLive));
                    }

                    if (($ariaOrientation = $this->getDOMElementAttributeAs($element, 'aria-orientation')) !== null) {
                        $bodyElement->setAriaOrientation(AriaOrientation::getConstantByName($ariaOrientation));
                    }

                    if (($ariaLabel = $this->getDOMElementAttributeAs($element, 'aria-label')) !== null) {
                        $bodyElement->setAriaLabel($ariaLabel);
                    }
                }
            }
        } catch (InvalidArgumentException $e) {
            $msg = "An error occurred while filling the bodyElement attributes (id, class, lang, label, dir, aria-*).";
            throw new UnmarshallingException($msg, $element, $e);
        }
    }

    /**
     * @param DOMElement $element
     * @param BodyElement $bodyElement
     */
    protected function fillElementFlowto(DOMElement $element, BodyElement $bodyElement)
    {
        if (($ariaFlowTo = $bodyElement->getAriaFlowTo()) !== '') {
            if (in_array($element->localName, self::$flowsToClasses, true)) {
                $element->setAttribute('aria-flowsto', $ariaFlowTo);
            } else {
                $element->setAttribute('aria-flowto', $ariaFlowTo);
            }
        }
    }

    /**
     * Fill $element with the attributes of $bodyElement.
     *
     * @param DOMElement $element The element from where the attribute values will be
     * @param BodyElement $bodyElement The bodyElement to be fill.
     */
    protected function fillElement(DOMElement $element, BodyElement $bodyElement)
    {
        if (($id = $bodyElement->getId()) !== '') {
            $element->setAttribute('id', $id);
        }

        if (($class = $bodyElement->getClass()) !== '') {
            $element->setAttribute('class', $class);
        }

        if (($lang = $bodyElement->getLang()) !== '') {
            $element->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:lang', $lang);
        }

        if (($label = $bodyElement->getLabel()) != '') {
            $element->setAttribute('label', $label);
        }

        $version = $this->getVersion();
        if (Version::compare($version, '2.2.0', '>=') === true) {
            // dir attribute
            if (($dir = $bodyElement->getDir()) !== Direction::AUTO && in_array($bodyElement->getQtiClassName(), self::$dirClasses) === true) {
                $element->setAttribute('dir', Direction::getNameByConstant($dir));
            }

            // aria-* attributes
            if ($bodyElement->getQtiClassName() !== 'printedVariable') {
                // All BodyElement objects deal with aria-* except PrintedVariable.

                /*
                 * There is a little glitch in the QTI 2.2.X XSDs. Indeed, the following elements do not
                 * consider aria-flowto (the official one) but aria-flowsto which is an error: associateInteraction,
                 * choiceInteraction, drawingInteraction, extendedTextInteraction, gapMatchInteraction,
                 * graphicAssociateInteraction, hotspotInteraction, matchInteraction, mediaInteraction,
                 * orderInteraction, selectPointInteraction, sliderInteraction, uploadInteraction, associableHotspot,
                 * br, col, endAttemptInteraction, gap, hotspotChoice, hr, img, textEntryInteraction.
                 *
                 * In such a context, at marshalling time, for the QTI classes described above, we populate data
                 * for the aria-flowsto attribute. Otherwise, we populate aria-flowto. This makes us able to honnor
                 * the XSD contract.
                 */
                $this->fillElementFlowto($element, $bodyElement);

                if (($ariaControls = $bodyElement->getAriaControls()) !== '') {
                    $element->setAttribute('aria-controls', $ariaControls);
                }

                if (($ariaDescribedBy = $bodyElement->getAriaDescribedBy()) !== '') {
                    $element->setAttribute('aria-describedby', $ariaDescribedBy);
                }

                if (($ariaLabelledBy = $bodyElement->getAriaLabelledBy()) !== '') {
                    $element->setAttribute('aria-labelledby', $ariaLabelledBy);
                }

                if (($ariaOwns = $bodyElement->getAriaOwns()) !== '') {
                    $element->setAttribute('aria-owns', $ariaOwns);
                }

                if (($ariaLevel = $bodyElement->getAriaLevel()) !== '') {
                    $element->setAttribute('aria-level', $ariaLevel);
                }

                if (($ariaLive = $bodyElement->getAriaLive()) !== false) {
                    $element->setAttribute('aria-live', AriaLive::getNameByConstant($ariaLive));
                }

                if (($ariaOrientation = $bodyElement->getAriaOrientation()) !== false) {
                    $element->setAttribute('aria-orientation', AriaOrientation::getNameByConstant($ariaOrientation));
                }

                if (($ariaLabel = $bodyElement->getAriaLabel()) !== '') {
                    $element->setAttribute('aria-label', $ariaLabel);
                }
            }
        }
    }

    protected function createElement(QtiComponent $component)
    {
        $localName = $component->getQtiClassName();

        if ($this->isWebComponentFriendly() === true && in_array($localName, self::$webComponentFriendlyClasses) === true) {
            $localName = XmlUtils::webComponentFriendlyClassName($localName);
        }

        return self::getDOMCradle()->createElement($localName);
    }

    /**
     * Is Web Component Friendly
     *
     * Whether or not the Marshaller should work in Web Component Friendly mode.
     *
     * @return bool
     */
    protected function isWebComponentFriendly()
    {
        return $this->getMarshallerFactory()->isWebComponentFriendly();
    }

    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     * @throws MarshallingException If an error occurs during the marshalling process.
     */
    abstract protected function marshall(QtiComponent $component);

    /**
     * Unmarshall a DOMElement object into its QTI Data Model equivalent.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A QtiComponent object.
     */
    abstract protected function unmarshall(DOMElement $element);

    /**
     * Get the class name/tag name of the QtiComponent/DOMElement which can be handled
     * by the Marshaller's implementation.
     *
     * Return an empty string if the marshaller implementation does not expect a particular
     * QTI class name.
     *
     * @return string A QTI class name or an empty string.
     */
    abstract public function getExpectedQtiClassName();

    /**
     * @param QtiComponent|string $component
     * @return string
     */
    private function getComponentName($component)
    {
        if ($component instanceof QtiComponent) {
            return $component->getQtiClassName();
        }
        return $this->getElementName($component);
    }

    /**
     * @param DOMElement|string $element
     * @return string
     */
    private function getElementName($element)
    {
        if ($element instanceof DOMElement) {
            return $element->localName;
        }
        if (is_object($element)) {
            return get_class($element);
        }

        return $element;
    }
}
