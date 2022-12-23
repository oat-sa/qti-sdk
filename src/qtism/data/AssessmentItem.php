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

namespace qtism\data;

use InvalidArgumentException;
use qtism\common\collections\IdentifierCollection;
use qtism\common\utils\Format;
use qtism\data\content\ItemBody;
use qtism\data\content\ModalFeedbackCollection;
use qtism\data\content\ModalFeedbackRule;
use qtism\data\content\ModalFeedbackRuleCollection;
use qtism\data\content\StylesheetCollection;
use qtism\data\processing\ResponseProcessing;
use qtism\data\processing\TemplateProcessing;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\ResponseValidityConstraintCollection;
use qtism\data\state\ShufflingCollection;
use qtism\data\state\TemplateDeclarationCollection;
use qtism\data\state\Utils as StateUtils;
use SplObjectStorage;

/**
 * The AssessmentItem QTI class implementation. It contains all the information
 * needed by a QTI Item Delivery engine to be run.
 */
class AssessmentItem extends QtiComponent implements QtiIdentifiable, IAssessmentItem
{
    use QtiIdentifiableTrait;

    /**
     * From IMS QTI:
     *
     * The principle identifier of the item. This identifier must have a
     * corresponding entry in the item's metadata.
     *
     * @var string
     * @qtism-bean-property
     */
    private $identifier;

    /**
     * From IMS QTI:
     *
     * The title of an assessmentItem is intended to enable the item to be selected
     * in situations where the full text of the itemBody is not available, for example
     * when a candidate is browsing a set of items to determine the order in which to
     * attempt them. Therefore, delivery engines may reveal the title to candidates at
     * any time but are not required to do so.
     *
     * @var string
     * @qtism-bean-property
     */
    private $title;

    /**
     * The label of the item.
     *
     * @var string
     * @qtism-bean-property
     */
    private $label = '';

    /**
     * The language used in the Item.
     *
     * @var string
     * @qtism-bean-property
     */
    private $lang = '';

    /**
     * From IMS QTI:
     *
     * Items are classified into Adaptive Items and Non-adaptive Items.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $adaptive = false;

    /**
     * Whether the item is time dependent or not.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $timeDependent;

    /**
     * From IMS QTI:
     *
     * The tool name attribute allows the tool creating the item to identify
     * itself. Other processing systems may use this information to interpret
     * the content of application specific data, such as labels on the elements
     * of the item's itemBody.
     *
     * @var string
     * @qtism-bean-property
     */
    private $toolName = '';

    /**
     * From IMS QTI:
     *
     * The tool name attribute allows the tool creating the item to identify itself.
     * Other processing systems may use this information to interpret the content of
     * application specific data, such as labels on the elements of the item's itemBody.
     *
     * @var string
     * @qtism-bean-property
     */
    private $toolVersion = '';

    /**
     * The response declarations.
     *
     * @var ResponseDeclarationCollection
     * @qtism-bean-property
     */
    private $responseDeclarations;

    /**
     * The outcome declarations.
     *
     * @var OutcomeDeclarationCollection
     * @qtism-bean-property
     */
    private $outcomeDeclarations;

    /**
     * The template declarations.
     *
     * @var TemplateDeclarationCollection
     * @qtism-bean-property
     */
    private $templateDeclarations;

    /**
     * The stylesheets of the item.
     *
     * @var StylesheetCollection
     * @qtism-bean-property
     */
    private $stylesheets;

    /**
     * The content body of the item.
     *
     * @var ItemBody
     * @qtism-bean-property
     */
    private $itemBody = null;

    /**
     * The templateProcessing.
     *
     * @var TemplateProcessing
     * @qtism-bean-property
     */
    private $templateProcessing = null;

    /**
     * The responseProcessing.
     *
     * @var ResponseProcessing
     * @qtism-bean-property
     */
    private $responseProcessing = null;

    /**
     * The modalFeedbacks.
     *
     * @var ModalFeedbackCollection
     */
    private $modalFeedbacks;

    /**
     * Create a new AssessmentItem object.
     *
     * @param string $identifier A QTI Identifier.
     * @param string $title The title of the item.
     * @param bool $timeDependent Whether the item is time dependent.
     * @param string $lang The language (code) of the item.
     * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier, if $title is not a string value, if $timeDependent is not a boolean value, or if $lang is not a string value.
     */
    public function __construct($identifier, $title, $timeDependent, $lang = '')
    {
        $this->setObservers(new SplObjectStorage());

        $this->setIdentifier($identifier);
        $this->setTitle($title);
        $this->setTimeDependent($timeDependent);
        $this->setLang($lang);
        $this->setResponseDeclarations(new ResponseDeclarationCollection());
        $this->setOutcomeDeclarations(new OutcomeDeclarationCollection());
        $this->setTemplateDeclarations(new TemplateDeclarationCollection());
        $this->setStylesheets(new StylesheetCollection());
        $this->setModalFeedbacks(new ModalFeedbackCollection());
    }

    /**
     * Set the identifier.
     *
     * @param string $identifier A QTI Identifier.
     * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
     */
    public function setIdentifier($identifier): void
    {
        if (Format::isIdentifier($identifier, false)) {
            $this->identifier = $identifier;
            $this->notify();
        } else {
            $msg = "The identifier argument must be a valid QTI Identifier, '" . $identifier . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the identifier.
     *
     * @return string A QTI identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the title of the item.
     *
     * @param string $title A title.
     * @throws InvalidArgumentException If $title is not a string value.
     */
    public function setTitle($title): void
    {
        if (is_string($title)) {
            $this->title = $title;
        } else {
            $msg = "The title argument must be a string, '" . gettype($title) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the title of the item.
     *
     * @return string A title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the language code.
     *
     * @param string $lang A language code.
     * @throws InvalidArgumentException If $lang is not a string.
     */
    public function setLang($lang = ''): void
    {
        if (is_string($lang)) {
            $this->lang = $lang;
        } else {
            $msg = "The lang argument must be a string, '" . gettype($lang) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Set the label of the item.
     *
     * @param string $label A string with at most 256 characters.
     * @throws InvalidArgumentException If $label is not a string with at most 256 characters.
     */
    public function setLabel($label): void
    {
        if (Format::isString256($label) === true) {
            $this->label = $label;
        } else {
            $msg = 'The label argument must be a string with at most 256 characters.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether or not a value is defined for the label attribute.
     *
     * @return bool
     */
    public function hasLabel(): bool
    {
        return $this->getLabel() !== '';
    }

    /**
     * Get the label of the item.
     *
     * @return string A string with at most 256 characters.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the language code.
     *
     * @return string A language code.
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * Whether the AssessmentItem has a language.
     *
     * @return bool
     */
    public function hasLang(): bool
    {
        $lang = $this->getLang();

        return !empty($lang);
    }

    /**
     * Set whether the item is adaptive.
     *
     * @param bool $adaptive Adaptive or not.
     * @throws InvalidArgumentException If $adaptive is not a boolean value.
     */
    public function setAdaptive($adaptive): void
    {
        if (is_bool($adaptive)) {
            $this->adaptive = $adaptive;
        } else {
            $msg = "The adaptive argument must be a boolean, '" . gettype($adaptive) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether the item is adaptive.
     *
     * @return bool
     */
    public function isAdaptive(): bool
    {
        return $this->adaptive;
    }

    /**
     * Set whether the item is time dependent or not.
     *
     * @param bool $timeDependent Time dependent or not.
     * @throws InvalidArgumentException If $timeDependent is not a boolean value.
     */
    public function setTimeDependent($timeDependent): void
    {
        if (is_bool($timeDependent)) {
            $this->timeDependent = $timeDependent;
        } else {
            $msg = "The timeDependent argument must be a boolean, '" . gettype($timeDependent) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether the item is time dependent.
     *
     * @return bool
     */
    public function isTimeDependent(): bool
    {
        return $this->timeDependent;
    }

    /**
     * Set the name of the tool with which the item was created.
     *
     * @param string $toolName A tool name with at most 256 characters.
     * @throws InvalidArgumentException If $toolName is not a string value with at most 256 characters.
     */
    public function setToolName($toolName): void
    {
        if (Format::isString256($toolName) === true) {
            $this->toolName = $toolName;
        } else {
            $msg = 'The toolName argument must be a string with at most 256 characters.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the name of the tool with which the item was created.
     *
     * @return string
     */
    public function getToolName(): string
    {
        return $this->toolName;
    }

    /**
     * Whether or not a value is defined for the toolName attribute.
     *
     * @return bool
     */
    public function hasToolName(): bool
    {
        return $this->getToolName() !== '';
    }

    /**
     * Set the version of the tool with which the item was created.
     *
     * @param string $toolVersion A tool version with at most 256 characters.
     * @throws InvalidArgumentException If $toolVersion is not a string value with at most 256 characters.
     */
    public function setToolVersion($toolVersion): void
    {
        if (Format::isString256($toolVersion) === true) {
            $this->toolVersion = $toolVersion;
        } else {
            $msg = 'The toolVersion argument must be a string with at most 256 characters.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the version of the tool with which the item was created.
     *
     * @return string A tool version with at most 256 characters.
     */
    public function getToolVersion(): string
    {
        return $this->toolVersion;
    }

    /**
     * Whether or not a value is defined for the toolVersion attribute.
     *
     * @return bool
     */
    public function hasToolVersion(): bool
    {
        return $this->getToolVersion() !== '';
    }

    /**
     * Set the response declarations.
     *
     * @param ResponseDeclarationCollection $responseDeclarations A collection of ResponseDeclaration objects
     */
    public function setResponseDeclarations(ResponseDeclarationCollection $responseDeclarations): void
    {
        $this->responseDeclarations = $responseDeclarations;
    }

    /**
     * Get the response declarations.
     *
     * @return ResponseDeclarationCollection A collection of ResponseDeclaration objects.
     */
    public function getResponseDeclarations(): ResponseDeclarationCollection
    {
        return $this->responseDeclarations;
    }

    /**
     * Set the outcome declarations.
     *
     * @param OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations): void
    {
        $this->outcomeDeclarations = $outcomeDeclarations;
    }

    /**
     * Get the outcome declarations.
     *
     * @return OutcomeDeclarationCollection A collection of OutcomeDeclaration objects.
     */
    public function getOutcomeDeclarations(): OutcomeDeclarationCollection
    {
        return $this->outcomeDeclarations;
    }

    /**
     * Set the template declarations.
     *
     * @param TemplateDeclarationCollection $templateDeclarations A collection of TemplateDeclaration objects.
     */
    public function setTemplateDeclarations(TemplateDeclarationCollection $templateDeclarations): void
    {
        $this->templateDeclarations = $templateDeclarations;
    }

    /**
     * Get the template declarations.
     *
     * @return TemplateDeclarationCollection A collection of TemplateDeclaration objects.
     */
    public function getTemplateDeclarations(): TemplateDeclarationCollection
    {
        return $this->templateDeclarations;
    }

    /**
     * Set the TemplateProcessing object composing this item. Give the null value
     * to $templateProcessing to express that there is not TemplateProcessing object
     * bound to the item.
     *
     * @param TemplateProcessing $templateProcessing A TemplateProcessing object or null.
     */
    public function setTemplateProcessing(TemplateProcessing $templateProcessing = null): void
    {
        $this->templateProcessing = $templateProcessing;
    }

    /**
     * Get the TemplateProcessing object composing the item. If no TemplateProcessing
     * object is bound to the item, the null value is returned.
     *
     * @return TemplateProcessing A TemplateProcessing object or null.
     */
    public function getTemplateProcessing(): ?TemplateProcessing
    {
        return $this->templateProcessing;
    }

    /**
     * Whether or not a TemplateProcessing object is bound to the item.
     *
     * @return bool
     */
    public function hasTemplateProcessing(): bool
    {
        return $this->getTemplateProcessing() !== null;
    }

    /**
     * Set the stylesheets.
     *
     * @param StylesheetCollection $stylesheets A collection of Stylesheet objects.
     */
    public function setStylesheets(StylesheetCollection $stylesheets): void
    {
        $this->stylesheets = $stylesheets;
    }

    /**
     * Get the stylesheets.
     *
     * @return StylesheetCollection A collection of Stylesheet objects.
     */
    public function getStylesheets(): StylesheetCollection
    {
        return $this->stylesheets;
    }

    /**
     * Set the ItemBody object representing the content body of the item.
     *
     * @param ItemBody $itemBody An ItemBody object or the NULL value to state that the item has no content.
     */
    public function setItemBody(ItemBody $itemBody = null): void
    {
        $this->itemBody = $itemBody;
    }

    /**
     * Get the ItemBody object representing the content body of the item.
     *
     * @return ItemBody|null An ItemBody object or the NULL value if the item has no content.
     */
    public function getItemBody(): ?ItemBody
    {
        return $this->itemBody;
    }

    /**
     * Whether or not the object has an ItemBody object representing its content.
     *
     * @return bool
     */
    public function hasItemBody(): bool
    {
        return $this->getItemBody() !== null;
    }

    /**
     * Get the associated ResponseProcessing object.
     *
     * @return ResponseProcessing|null A ResponseProcessing object or null if no associated response processing.
     */
    public function getResponseProcessing(): ?ResponseProcessing
    {
        return $this->responseProcessing;
    }

    /**
     * Set the associated ResponseProcessing object.
     *
     * @param ResponseProcessing $responseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function setResponseProcessing(ResponseProcessing $responseProcessing = null): void
    {
        $this->responseProcessing = $responseProcessing;
    }

    /**
     * Whether the AssessmentItem has a response processing.
     *
     * @return bool
     */
    public function hasResponseProcessing(): bool
    {
        return $this->getResponseProcessing() !== null;
    }

    /**
     * Set the associated ModalFeedback objects.
     *
     * @param ModalFeedbackCollection $modalFeedbacks A collection of ModalFeedback objects.
     */
    public function setModalFeedbacks(ModalFeedbackCollection $modalFeedbacks): void
    {
        $this->modalFeedbacks = $modalFeedbacks;
    }

    /**
     * Get the associated ModalFeedback objects.
     *
     * @return ModalFeedbackCollection A collection of ModalFeedback objects.
     */
    public function getModalFeedbacks(): ModalFeedbackCollection
    {
        return $this->modalFeedbacks;
    }

    /**
     * @return array|ModalFeedbackRuleCollection
     */
    public function getModalFeedbackRules(): ModalFeedbackRuleCollection
    {
        $modalFeedbackRules = new ModalFeedbackRuleCollection();

        foreach ($this->getModalFeedbacks() as $modalFeedback) {
            $identifier = $modalFeedback->getIdentifier();
            $outcomeIdentifier = $modalFeedback->getOutcomeIdentifier();
            $showHide = $modalFeedback->getShowHide();
            $title = $modalFeedback->getTitle();

            $modalFeedbackRules[] = new ModalFeedbackRule($outcomeIdentifier, $showHide, $identifier, $title);
        }

        return $modalFeedbackRules;
    }

    /**
     * Get the response variable identifiers related to the endAttemptInteraction in the item content.
     *
     * @return IdentifierCollection
     */
    public function getEndAttemptIdentifiers(): IdentifierCollection
    {
        $endAttemptIdentifiers = new IdentifierCollection();

        foreach ($this->getComponentsByClassName('endAttemptInteraction') as $endAttemptInteraction) {
            $endAttemptIdentifiers[] = $endAttemptInteraction->getResponseIdentifier();
        }

        return $endAttemptIdentifiers;
    }

    /**
     * @return array|ShufflingCollection
     */
    public function getShufflings(): ShufflingCollection
    {
        $classNames = [
            'choiceInteraction',
            'orderInteraction',
            'associateInteraction',
            'matchInteraction',
            'gapMatchInteraction',
            'inlineChoiceInteraction',
        ];

        $shufflings = new ShufflingCollection();
        foreach ($this->getComponentsByClassName($classNames) as $interaction) {
            $shuffling = StateUtils::createShufflingFromInteraction($interaction);

            if ($shuffling !== false) {
                $shufflings[] = $shuffling;
            }
        }

        return $shufflings;
    }

    /**
     * @return array|ResponseValidityConstraintCollection
     */
    public function getResponseValidityConstraints(): ResponseValidityConstraintCollection
    {
        $classNames = [
            'choiceInteraction',
            'orderInteraction',
            'associateInteraction',
            'matchInteraction',
            'inlineChoiceInteraction',
            'textEntryInteraction',
            'extendedTextInteraction',
            'hottextInteraction',
            'hotspotInteraction',
            'selectPointInteraction',
            'graphicOrderInteraction',
            'graphicAssociateInteraction',
            'positionObjectInteraction',
            'gapMatchInteraction',
            'graphicGapMatchInteraction',
        ];

        $responseValidityConstraints = new ResponseValidityConstraintCollection();
        foreach ($this->getComponentsByClassName($classNames) as $component) {
            $responseValidityConstraints[] = $component->getResponseValidityConstraint();
        }

        return $responseValidityConstraints;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'assessmentItem';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        $comp = array_merge(
            $this->getResponseDeclarations()->getArrayCopy(),
            $this->getOutcomeDeclarations()->getArrayCopy(),
            $this->getTemplateDeclarations()->getArrayCopy()
        );

        if ($this->hasTemplateProcessing() === true) {
            $comp[] = $this->getTemplateProcessing();
        }

        $comp = array_merge($comp, $this->getStylesheets()->getArrayCopy());

        if ($this->hasItemBody() === true) {
            $comp[] = $this->getItemBody();
        }

        if ($this->hasResponseProcessing() === true) {
            $comp[] = $this->getResponseProcessing();
        }

        $comp = array_merge($comp, $this->getModalFeedbacks()->getArrayCopy());

        return new QtiComponentCollection($comp);
    }

    public function __clone()
    {
        $this->setObservers(new SplObjectStorage());
    }
}
