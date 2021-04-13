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
use qtism\data\storage\xml\versions\QtiVersion;
use RuntimeException;

/**
 * Class Marshaller
 */
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
        'a',
        'abbr',
        'acronym',
        'address',
        'associateInteraction',
        'b',
        'bdo',
        'big',
        'br',
        'caption',
        'choiceInteraction',
        'cite',
        'code',
        'col',
        'colgroup',
        'customInteraction',
        'dd',
        'dfn',
        'div',
        'dl',
        'drawingInteraction',
        'dt',
        'em',
        'extendedTextInteraction',
        'feedbackBlock',
        'feedbackInline',
        'gapImg',
        'gapMatchInteraction',
        'gapText',
        'graphicAssociateInteraction',
        'graphicGapMatchInteraction',
        'graphicOrderInteraction',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hotspotInteraction',
        'hottext',
        'hottext',
        'hottextInteraction',
        'hr',
        'i',
        'img',
        'infoControl',
        'inlineChoice',
        'inlineChoiceInteraction',
        'kbd',
        'label',
        'li',
        'matchInteraction',
        'mediaInteraction',
        'object',
        'orderInteraction',
        'p',
        'positionObjecInteraction',
        'pre',
        'prompt',
        'q',
        'rubricBlock',
        'samp',
        'selectPointInteraction',
        'simpleAssociableChoice',
        'simpleChoice',
        'sliderInteraction',
        'small',
        'span',
        'stimulusBody',
        'strong',
        'sub',
        'sup',
        'table',
        'tbody',
        'td',
        'templateBlock',
        'templateInline',
        'tfoot',
        'th',
        'thead',
        'tr',
        'tt',
        'ul',
        'uploadInteraction',
        'var',
    ];

    /**
     * An array containing the QTI class names that are allowed to be Web Component friendly.
     *
     * @var string[]
     */
    public static $webComponentFriendlyClasses = [
        'associableHotspot',
        'associateInteraction',
        'choiceInteraction',
        'customInteraction',
        'drawingInteraction',
        'endAttemptInteraction',
        'extendedTextInteraction',
        'feedbackBlock',
        'feedbackInline',
        'gap',
        'gapImg',
        'gapMatchInteraction',
        'gapText',
        'graphicAssociateInteraction',
        'graphicGapMatchInteraction',
        'graphicOrderInteraction',
        'hotspotChoice',
        'hotspotInteraction',
        'hottext',
        'hottextInteraction',
        'infoControl',
        'inlineChoice',
        'inlineChoiceInteraction',
        'matchInteraction',
        'mediaInteraction',
        'orderInteraction',
        'positionObjectInteraction',
        'positionObjectStage',
        'printedVariable',
        'prompt',
        'rubricBlock',
        'selectPointInteraction',
        'simpleAssociableChoice',
        'simpleChoice',
        'sliderInteraction',
        'templateBlock',
        'templateInline',
        'textEntryInteraction',
        'uploadInteraction',
    ];

    /**
     * An array containing QTI class names preferring aria-flowsto instead of aria-flowto.
     *
     * @var string[]
     */
    private static $flowsToClasses = [
        'associableHotspot',
        'associateInteraction',
        'br',
        'choiceInteraction',
        'col',
        'drawingInteraction',
        'endAttemptInteraction',
        'extendedTextInteraction',
        'gap',
        'gapMatchInteraction',
        'graphicAssociateInteraction',
        'hotspotChoice',
        'hotspotInteraction',
        'hottextInteraction',
        'hr',
        'img',
        'matchInteraction',
        'mediaInteraction',
        'orderInteraction',
        'selectPointInteraction',
        'sliderInteraction',
        'textEntryInteraction',
        'uploadInteraction',
    ];

    /**
     * Create a new Marshaller object.
     *
     * @param string $version The QTI version on which the Marshaller operates e.g. '2.1.0'.
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
    public function setMarshallerFactory(MarshallerFactory $marshallerFactory): void
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
    public function getMarshallerFactory(): MarshallerFactory
    {
        if ($this->marshallerFactory === null) {
            $version = QtiVersion::create($this->version);
            $this->marshallerFactory = $version->getMarshallerFactory();
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

    /**
     * @param $method
     * @param $args
     * @return DOMElement|mixed
     * @throws MarshallingException|UnmarshallingException
     */
    public function __call($method, $args)
    {
        if (count($args) < 1) {
            throw new RuntimeException("Method '${method}' only accepts a single argument.");
        }

        switch ($method) {
            case 'marshall':
                $component = $args[0];
                $this->checkMarshallerImplementation($component);
                return $this->marshall($component);

            case 'unmarshall':
                $this->checkUnmarshallerImplementation($args[0]);
                return $this->unmarshall(...$args);

            default:
                throw new RuntimeException("Unknown method Marshaller::'${method}'.");
        }
    }

    protected function checkMarshallerImplementation($component)
    {
        if (!$component instanceof QtiComponent || ($this->getExpectedQtiClassName() !== '' && $component->getQtiClassName() !== $this->getExpectedQtiClassName())) {
            $componentName = $this->getComponentName($component);
            throw new RuntimeException("No marshaller implementation found while marshalling component '${componentName}'.");
        }
    }

    protected function checkUnmarshallerImplementation($element)
    {
        if (!$element instanceof DOMElement || ($this->getExpectedQtiClassName() !== '' && $element->localName !== $this->getExpectedQtiClassName())) {
            $nodeName = $this->getElementName($element);
            throw new RuntimeException("No Marshaller implementation found while unmarshalling element '${nodeName}'.");
        }
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
        if ($this->isWebComponentFriendly() === true && strpos($element->localName, "qti-") === 0) {
            $qtiFriendlyClassName = XmlUtils::qtiFriendlyName($element->localName);

            if (in_array($qtiFriendlyClassName, self::$webComponentFriendlyClasses)) {
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
        XmlUtils::setDOMElementValue($element, $value);
    }

    /**
     * Get the first child DOM Node with nodeType attribute equals to XML_ELEMENT_NODE.
     * This is very useful to get a sub-node without having to exclude text nodes, cdata,
     * ... manually.
     *
     * @param DOMElement $element A DOMElement object
     * @return DOMElement|bool A DOMElement If a child node with nodeType = XML_ELEMENT_NODE or false if nothing found.
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
     * @param bool $withText Whether text nodes must be returned or not.
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
     * @param bool $exclude (optional) Whether the $tagName parameter must be considered as a blacklist.
     * @param bool $withText (optional) Whether text nodes must be returned or not.
     * @return array An array of DOMElement objects.
     */
    public function getChildElementsByTagName($element, $tagName, $exclude = false, $withText = false)
    {
        if (!is_array($tagName)) {
            $tagName = [$tagName];
        }

        if ($this->isWebComponentFriendly() === true) {
            foreach ($tagName as $key => $name) {
                if (in_array($name, self::$webComponentFriendlyClasses)) {
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

        if ($this->needsFlowsToFix($bodyElement->getQtiClassName())) {
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
     * Do we need to apply the fix for aria-flowSto?
     *
     * @param string $className
     * @return bool
     */
    private function needsFlowsToFix(string $className): bool
    {
        return Version::compare($this->getVersion(), '2.2.3', '<')
            && in_array($className, self::$flowsToClasses, true);
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
                if (($dir = $this->getDOMElementAttributeAs($element, 'dir')) !== null && in_array($element->localName, self::$dirClasses)) {
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

                    if (($ariaHidden = $this->getDOMElementAttributeAs($element, 'aria-hidden', 'boolean')) !== null) {
                        $bodyElement->setAriaHidden($ariaHidden);
                    }
                }
            }
        } catch (InvalidArgumentException $e) {
            $msg = 'An error occurred while filling the bodyElement attributes (id, class, lang, label, dir, aria-*).';
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
            if ($this->needsFlowsToFix($element->localName)) {
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
            if (($dir = $bodyElement->getDir()) !== Direction::AUTO && in_array($bodyElement->getQtiClassName(), self::$dirClasses)) {
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

                if (($ariaHidden = $bodyElement->getAriaHidden()) !== false) {
                    $element->setAttribute('aria-hidden', 'true');
                }
            }
        }
    }

    /**
     * @param QtiComponent $component
     * @return DOMElement
     */
    protected function createElement(QtiComponent $component)
    {
        $localName = $component->getQtiClassName();

        if ($this->isWebComponentFriendly() === true && in_array($localName, self::$webComponentFriendlyClasses)) {
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
     * @throws MarshallingException|MarshallerNotFoundException If an error occurs during the marshalling process.
     */
    abstract protected function marshall(QtiComponent $component);

    /**
     * Unmarshall a DOMElement object into its QTI Data Model equivalent.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A QtiComponent object.
     * @throws UnmarshallingException|MarshallerNotFoundException If an error occurs during the unmarshalling process.
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
