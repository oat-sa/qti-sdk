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

use qtism\common\utils\Reflection;
use ReflectionClass;
use ReflectionException;

/**
 * A QTI 3.0.0 (aQTI) MarshallerFactory
 *
 * It is focusing on instantiating and configuring Marshallers for QTI 3.0.0 (aQTI).
 */
class Qti30MarshallerFactory extends Qti221MarshallerFactory
{
    public function __construct()
    {
        parent::__construct();

        $this->setWebComponentFriendly(true);
        $this->removeMappingEntry('associableHotspot');
        $this->removeMappingEntry('gap');
        $this->removeMappingEntry('gapImg');
        $this->removeMappingEntry('gapText');
        $this->removeMappingEntry('simpleAssociableChoice');
        $this->removeMappingEntry('hotspotChoice');
        $this->removeMappingEntry('hottext');
        $this->removeMappingEntry('inlineChoice');
        $this->removeMappingEntry('simpleChoice');
        $this->removeMappingEntry('associateInteraction');
        $this->removeMappingEntry('choiceInteraction');
        $this->removeMappingEntry('drawingInteraction');
        $this->removeMappingEntry('extendedTextInteraction');
        $this->removeMappingEntry('gapMatchInteraction');
        $this->removeMappingEntry('graphicAssociateInteraction');
        $this->removeMappingEntry('graphicGapMatchInteraction');
        $this->removeMappingEntry('graphicOrderInteraction');
        $this->removeMappingEntry('hotspotInteraction');
        $this->removeMappingEntry('selectPointInteraction');
        $this->removeMappingEntry('hottextInteraction');
        $this->removeMappingEntry('matchInteraction');
        $this->removeMappingEntry('mediaInteraction');
        $this->removeMappingEntry('orderInteraction');
        $this->removeMappingEntry('sliderInteraction');
        $this->removeMappingEntry('uploadInteraction');
        $this->removeMappingEntry('customInteraction');
        $this->removeMappingEntry('endAttemptInteraction');
        $this->removeMappingEntry('inlineChoiceInteraction');
        $this->removeMappingEntry('textEntryInteraction');
        $this->removeMappingEntry('positionObjectInteraction');
        $this->removeMappingEntry('printedVariable');
        $this->removeMappingEntry('prompt');
        $this->removeMappingEntry('feedbackBlock');
        $this->removeMappingEntry('feedackInline');
        $this->removeMappingEntry('rubricBlock');
        $this->removeMappingEntry('templateBlock');
        $this->removeMappingEntry('templateInline');
        $this->removeMappingEntry('infoControl');

        $this->addMappingEntry('qti-associable-hotspot', 'qtism\\data\\storage\\xml\\marshalling\\HotspotMarshaller');
        $this->addMappingEntry('qti-gap', 'qtism\\data\\storage\\xml\\marshalling\\GapMarshaller');
        $this->addMappingEntry('qti-gap-img', 'qtism\\data\\storage\\xml\\marshalling\\GapChoiceMarshaller');
        $this->addMappingEntry('qti-gap-text', 'qtism\\data\\storage\\xml\\marshalling\\GapChoiceMarshaller');
        $this->addMappingEntry('qti-simple-associable-choice', 'qtism\\data\\storage\\xml\\marshalling\\SimpleAssociableChoiceMarshaller');
        $this->addMappingEntry('qti-hotspot-choice', 'qtism\\data\\storage\\xml\\marshalling\\HotspotMarshaller');
        $this->addMappingEntry('qti-hottext', 'qtism\\data\\storage\\xml\\marshalling\\HottextMarshaller');
        $this->addMappingEntry('qti-inline-choice', 'qtism\\data\\storage\\xml\\marshalling\\InlineChoiceMarshaller');
        $this->addMappingEntry('qti-simple-choice', 'qtism\\data\\storage\\xml\\marshalling\\SimpleChoiceMarshaller');
        $this->addMappingEntry('qti-associate-interaction', 'qtism\\data\\storage\\xml\\marshalling\\AssociateInteractionMarshaller');
        $this->addMappingEntry('qti-choice-interaction', 'qtism\\data\\storage\\xml\\marshalling\\ChoiceInteractionMarshaller');
        $this->addMappingEntry('qti-drawing-interaction', 'qtism\\data\\storage\\xml\\marshalling\\DrawingInteractionMarshaller');
        $this->addMappingEntry('qti-extended-text-interaction', 'qtism\\data\\storage\\xml\\marshalling\\TextInteractionMarshaller');
        $this->addMappingEntry('qti-gap-match-interaction', 'qtism\\data\\storage\\xml\\marshalling\\GapMatchInteractionMarshaller');
        $this->addMappingEntry('qti-graphic-associate-interaction', 'qtism\\data\\storage\\xml\\marshalling\\GraphicAssociateInteractionMarshaller');
        $this->addMappingEntry('qti-graphic-gap-match-interaction', 'qtism\\data\\storage\\xml\\marshalling\\GraphicGapMatchInteractionMarshaller');
        $this->addMappingEntry('qti-graphic-order-interaction', 'qtism\\data\\storage\\xml\\marshalling\\GraphicOrderInteractionMarshaller');
        $this->addMappingEntry('qti-hotspot-interaction', 'qtism\\data\\storage\\xml\\marshalling\\HotspotInteractionMarshaller');
        $this->addMappingEntry('qti-select-point-interaction', 'qtism\\data\\storage\\xml\\marshalling\\SelectPointInteractionMarshaller');
        $this->addMappingEntry('qti-hottext-interaction', 'qtism\\data\\storage\\xml\\marshalling\\HottextInteractionMarshaller');
        $this->addMappingEntry('qti-match-interaction', 'qtism\\data\\storage\\xml\\marshalling\\MatchInteractionMarshaller');
        $this->addMappingEntry('qti-media-interaction', 'qtism\\data\\storage\\xml\\marshalling\\MediaInteractionMarshaller');
        $this->addMappingEntry('qti-order-interaction', 'qtism\\data\\storage\\xml\\marshalling\\ChoiceInteractionMarshaller');
        $this->addMappingEntry('qti-slider-interaction', 'qtism\\data\\storage\\xml\\marshalling\\SliderInteractionMarshaller');
        $this->addMappingEntry('qti-upload-interaction', 'qtism\\data\\storage\\xml\\marshalling\\UploadInteractionMarshaller');
        $this->addMappingEntry('qti-custom-interaction', 'qtism\\data\\storage\\xml\\marshalling\\CustomInteractionMarshaller');
        $this->addMappingEntry('qti-end-attempt-interaction', 'qtism\\data\\storage\\xml\\marshalling\\EndAttemptInteractionMarshaller');
        $this->addMappingEntry('qti-inline-choice-interaction', 'qtism\\data\\storage\\xml\\marshalling\\InlineChoiceInteractionMarshaller');
        $this->addMappingEntry('qti-text-entry-interaction', 'qtism\\data\\storage\\xml\\marshalling\\TextInteractionMarshaller');
        $this->addMappingEntry('qti-position-object-interaction', 'qtism\\data\\storage\\xml\\marshalling\\PositionObjectInteractionMarshaller');
        $this->addMappingEntry('qti-printed-variable', 'qtism\\data\\storage\\xml\\marshalling\\PrintedVariableMarshaller');
        $this->addMappingEntry('qti-prompt', 'qtism\\data\\storage\\xml\\marshalling\\PromptMarshaller');
        $this->addMappingEntry('qti-feedback-block', 'qtism\\data\\storage\\xml\\marshalling\\FeedbackElementMarshaller');
        $this->addMappingEntry('qti-template-inline', 'qtism\\data\\storage\\xml\\marshalling\\TemplateElementMarshaller');
        $this->addMappingEntry('qti-rubric-block', 'qtism\\data\\storage\\xml\\marshalling\\RubricBlockMarshaller');
        $this->addMappingEntry('qti-template-block', 'qtism\\data\\storage\\xml\\marshalling\\TemplateElementMarshaller');
        $this->addMappingEntry('qti-template-inline', 'qtism\\data\\storage\\xml\\marshalling\\TemplateElementMarshaller');
        $this->addMappingEntry('qti-info-control', 'qtism\\data\\storage\\xml\\marshalling\\InfoControlMarshaller');

        $this->addMappingEntry('sub', 'qtism\\data\\storage\\xml\\marshalling\\SsmlSubMarshaller', 'http://www.w3.org/2010/10/synthesis');
    }

    /**
     * Instantiate a Marshaller
     *
     * Instantiate a Marshaller in this MarshallerFactory context.
     *
     * @param ReflectionClass $class
     * @param array $args
     * @return Marshaller
     * @throws ReflectionException
     * @see \qtism\data\storage\xml\marshalling\MarshallerFactory::instantiateMarshaller()
     */
    protected function instantiateMarshaller(ReflectionClass $class, array $args)
    {
        array_unshift($args, '3.0.0');
        return Reflection::newInstance($class, $args);
    }
}
