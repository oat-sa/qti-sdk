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

use DOMElement;
use DOMNode;
use DOMText;
use qtism\data\content\AtomicBlock;
use qtism\data\content\FeedbackBlock;
use qtism\data\content\InfoControl;
use qtism\data\content\interactions\AssociateInteraction;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\GapImg;
use qtism\data\content\interactions\GapMatchInteraction;
use qtism\data\content\interactions\GapText;
use qtism\data\content\interactions\GraphicAssociateInteraction;
use qtism\data\content\interactions\GraphicOrderInteraction;
use qtism\data\content\interactions\HotspotInteraction;
use qtism\data\content\interactions\Hottext;
use qtism\data\content\interactions\HottextInteraction;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\interactions\InlineChoiceInteraction;
use qtism\data\content\interactions\MatchInteraction;
use qtism\data\content\interactions\OrderInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\SimpleAssociableChoice;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleMatchSet;
use qtism\data\content\ItemBody;
use qtism\data\content\ModalFeedback;
use qtism\data\content\SimpleInline;
use qtism\data\content\TemplateBlock;
use qtism\data\content\TemplateInline;
use qtism\data\content\xhtml\lists\Dl;
use qtism\data\content\xhtml\lists\DlElement;
use qtism\data\content\xhtml\lists\Li;
use qtism\data\content\xhtml\lists\Ol;
use qtism\data\content\xhtml\lists\Ul;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\content\xhtml\tables\Caption;
use qtism\data\content\xhtml\tables\Td;
use qtism\data\content\xhtml\tables\Th;
use qtism\data\content\xhtml\tables\Tr;
use qtism\data\content\xhtml\text\Blockquote;
use qtism\data\content\xhtml\text\Div;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * An abstract implementation of a marshaller/unmarshaller focusing
 * on QTI components that belong to the QTI content model.
 */
abstract class ContentMarshaller extends RecursiveMarshaller
{
    /**
     * Create a new ContentMarshaller object.
     *
     * @param string $version The QTI version on which the Marshaller operates e.g. '2.1.0'.
     */
    public function __construct($version)
    {
        parent::__construct($version);
        $this->setLookupClasses();
    }

    /**
     * Classes to lookup for.
     *
     * @var array
     */
    protected $lookupClasses;

    private static $finals = [
        'textRun',
        'br',
        'param',
        'hr',
        'col',
        'img',
        'math',
        'table',
        'colgroup',
        'tbody',
        'thead',
        'tfoot',
        'rubricBlock',
        'gap',
        'textEntryInteraction',
        'extendedTextInteraction',
        'selectPointInteraction',
        'associableHotspot',
        'hotspotChoice',
        'graphicGapMatchInteraction',
        'positionObjectInteraction',
        'positionObjectStage',
        'sliderInteraction',
        'mediaInteraction',
        'drawingInteraction',
        'uploadInteraction',
        'endAttemptInteraction',
        'customInteraction',
        'printedVariable',
        'include',
    ];

    private static $simpleComposites = [
        'a',
        'abbr',
        'acronym',
        'b',
        'big',
        'cite',
        'code',
        'dfn',
        'em',
        'feedbackInline',
        'templateInline',
        'i',
        'kbd',
        'q',
        'samp',
        'small',
        'span',
        'strong',
        'sub',
        'sup',
        'tt',
        'var',
        'td',
        'th',
        'object',
        'caption',
        'address',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'p',
        'pre',
        'li',
        'dd',
        'dt',
        'div',
        'templateBlock',
        'simpleChoice',
        'simpleAssociableChoice',
        'prompt',
        'gapText',
        'inlineChoice',
        'hottext',
        'modalFeedback',
        'feedbackBlock',
    ];

    /**
     * @param DOMNode $element
     * @return bool
     */
    protected function isElementFinal(DOMNode $element)
    {
        return $element instanceof DOMText || ($element instanceof DOMElement && in_array($element->localName, self::$finals));
    }

    /**
     * @param QtiComponent $component
     * @return bool
     */
    protected function isComponentFinal(QtiComponent $component)
    {
        return in_array($component->getQtiClassName(), self::$finals);
    }

    /**
     * @param DOMElement $currentNode
     * @return QtiComponentCollection
     */
    protected function createCollection(DOMElement $currentNode)
    {
        return new QtiComponentCollection();
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component)
    {
        if ($component instanceof SimpleInline) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof AtomicBlock) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Tr) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Td) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Th) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Caption) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Ul) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Ol) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Li) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Dl) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof DlElement) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof ObjectElement) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Div) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof ItemBody) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Blockquote) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof SimpleChoice) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof SimpleAssociableChoice) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof SimpleMatchSet) {
            return $component->getSimpleAssociableChoices()->getArrayCopy();
        } elseif ($component instanceof GapText) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof GapImg) {
            return [$component->getObject()];
        } elseif ($component instanceof ChoiceInteraction) {
            return $component->getSimpleChoices()->getArrayCopy();
        } elseif ($component instanceof OrderInteraction) {
            return $component->getSimpleChoices()->getArrayCopy();
        } elseif ($component instanceof AssociateInteraction) {
            return $component->getSimpleAssociableChoices()->getArrayCopy();
        } elseif ($component instanceof GapMatchInteraction) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof InlineChoiceInteraction) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof HotspotInteraction) {
            return $component->getHotspotChoices()->getArrayCopy();
        } elseif ($component instanceof GraphicAssociateInteraction) {
            return $component->getAssociableHotspots()->getArrayCopy();
        } elseif ($component instanceof InlineChoice) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof MatchInteraction) {
            return $component->getSimpleMatchSets()->getArrayCopy();
        } elseif ($component instanceof Prompt) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof FeedbackBlock) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof TemplateBlock) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof TemplateInline) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof Hottext) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof HottextInteraction) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof GraphicOrderInteraction) {
            return $component->getHotspotChoices()->getArrayCopy();
        } elseif ($component instanceof ModalFeedback) {
            return $component->getContent()->getArrayCopy();
        } elseif ($component instanceof InfoControl) {
            return $component->getContent()->getArrayCopy();
        }
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element)
    {
        $simpleComposites = self::$simpleComposites;
        $localName = $element->localName;

        if (in_array($localName, $simpleComposites)) {
            return self::getChildElements($element, true);
        } elseif ($localName === 'choiceInteraction') {
            return $this->getChildElementsByTagName($element, 'simpleChoice');
        } elseif ($localName === 'orderInteraction') {
            return $this->getChildElementsByTagName($element, 'simpleChoice');
        } elseif ($localName === 'associateInteraction') {
            return $this->getChildElementsByTagName($element, 'simpleAssociableChoice');
        } elseif ($localName === 'matchInteraction') {
            return $this->getChildElementsByTagName($element, 'simpleMatchSet');
        } elseif ($localName === 'gapMatchInteraction') {
            return $this->getChildElementsByTagName($element, ['gapText', 'gapImg', 'prompt'], true);
        } elseif ($localName === 'inlineChoiceInteraction') {
            return $this->getChildElementsByTagName($element, 'inlineChoice');
        } elseif ($localName === 'hottextInteraction') {
            return $this->getChildElementsByTagName($element, 'prompt', true);
        } elseif ($localName === 'hotspotInteraction') {
            return $this->getChildElementsByTagName($element, 'hotspotChoice');
        } elseif ($localName === 'graphicAssociateInteraction') {
            return $this->getChildElementsByTagName($element, 'associableHotspot');
        } elseif ($localName === 'graphicOrderInteraction') {
            return $this->getChildElementsByTagName($element, 'hotspotChoice');
        } elseif ($localName === 'tr') {
            return $this->getChildElementsByTagName($element, ['td', 'th']);
        } elseif ($localName === 'ul' || $element->localName === 'ol') {
            return $this->getChildElementsByTagName($element, 'li');
        } elseif ($localName === 'dl') {
            return $this->getChildElementsByTagName($element, ['dd', 'dt']);
        } elseif ($localName === 'itemBody') {
            return self::getChildElements($element);
        } elseif ($localName === 'blockquote') {
            return self::getChildElements($element);
        } elseif ($localName === 'simpleMatchSet') {
            return $this->getChildElementsByTagName($element, 'simpleAssociableChoice');
        } elseif ($localName === 'gapImg') {
            return $this->getChildElementsByTagName($element, 'object');
        } elseif ($element->localName === 'infoControl') {
            $elts = self::getChildElements($element, true);
            $finalElts = [];

            foreach ($elts as $elt) {
                if ($elt->nodeType === XML_ELEMENT_NODE && $elt->localName === 'portableInfoControl') {
                    continue;
                } else {
                    $finalElts[] = $elt;
                }
            }

            return $finalElts;
        } else {
            return [];
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return '';
    }

    /**
     * Set the classes to be looked up.
     */
    abstract protected function setLookupClasses();

    /**
     * Get the classes to be looked up.
     *
     * @return array
     */
    protected function getLookupClasses()
    {
        return $this->lookupClasses;
    }

    /**
     * Get the related PHP class name of a given $element.
     *
     * @param DOMElement $element The element you want to know the data model PHP class.
     * @return string A fully qualified class name.
     * @throws UnmarshallingException If no class can be found for $element.
     */
    protected function lookupClass(DOMElement $element)
    {
        $localName = $element->localName;
        $lookup = $this->getLookupClasses();
        $class = ucfirst($localName);

        foreach ($lookup as $l) {
            $fqClass = $l . "\\" . $class;

            if (class_exists($fqClass)) {
                return $fqClass;
            }

            $fqClass = $l . "\\Qti" . $class;

            if (class_exists($fqClass)) {
                return $fqClass;
            }
        }

        $msg = "No class could be found for tag with name '" . $localName . "'.";
        throw new UnmarshallingException($msg, $element);
    }
}
