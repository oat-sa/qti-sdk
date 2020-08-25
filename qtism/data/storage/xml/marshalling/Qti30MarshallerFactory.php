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

        $this->addMappingEntry('qti-associable-hotspot', HotspotMarshaller::class);
        $this->addMappingEntry('qti-gap', GapMarshaller::class);
        $this->addMappingEntry('qti-gap-img', GapChoiceMarshaller::class);
        $this->addMappingEntry('qti-gap-text', GapChoiceMarshaller::class);
        $this->addMappingEntry('qti-simple-associable-choice', SimpleAssociableChoiceMarshaller::class);
        $this->addMappingEntry('qti-hotspot-choice', HotspotMarshaller::class);
        $this->addMappingEntry('qti-hottext', HottextMarshaller::class);
        $this->addMappingEntry('qti-inline-choice', InlineChoiceMarshaller::class);
        $this->addMappingEntry('qti-simple-choice', SimpleChoiceMarshaller::class);
        $this->addMappingEntry('qti-associate-interaction', AssociateInteractionMarshaller::class);
        $this->addMappingEntry('qti-choice-interaction', ChoiceInteractionMarshaller::class);
        $this->addMappingEntry('qti-drawing-interaction', DrawingInteractionMarshaller::class);
        $this->addMappingEntry('qti-extended-text-interaction', TextInteractionMarshaller::class);
        $this->addMappingEntry('qti-gap-match-interaction', GapMatchInteractionMarshaller::class);
        $this->addMappingEntry('qti-graphic-associate-interaction', GraphicAssociateInteractionMarshaller::class);
        $this->addMappingEntry('qti-graphic-gap-match-interaction', GraphicGapMatchInteractionMarshaller::class);
        $this->addMappingEntry('qti-graphic-order-interaction', GraphicOrderInteractionMarshaller::class);
        $this->addMappingEntry('qti-hotspot-interaction', HotspotInteractionMarshaller::class);
        $this->addMappingEntry('qti-select-point-interaction', SelectPointInteractionMarshaller::class);
        $this->addMappingEntry('qti-hottext-interaction', HottextInteractionMarshaller::class);
        $this->addMappingEntry('qti-match-interaction', MatchInteractionMarshaller::class);
        $this->addMappingEntry('qti-media-interaction', MediaInteractionMarshaller::class);
        $this->addMappingEntry('qti-order-interaction', ChoiceInteractionMarshaller::class);
        $this->addMappingEntry('qti-slider-interaction', SliderInteractionMarshaller::class);
        $this->addMappingEntry('qti-upload-interaction', UploadInteractionMarshaller::class);
        $this->addMappingEntry('qti-custom-interaction', CustomInteractionMarshaller::class);
        $this->addMappingEntry('qti-end-attempt-interaction', EndAttemptInteractionMarshaller::class);
        $this->addMappingEntry('qti-inline-choice-interaction', InlineChoiceInteractionMarshaller::class);
        $this->addMappingEntry('qti-text-entry-interaction', TextInteractionMarshaller::class);
        $this->addMappingEntry('qti-position-object-interaction', PositionObjectInteractionMarshaller::class);
        $this->addMappingEntry('qti-printed-variable', PrintedVariableMarshaller::class);
        $this->addMappingEntry('qti-prompt', PromptMarshaller::class);
        $this->addMappingEntry('qti-feedback-block', FeedbackElementMarshaller::class);
        $this->addMappingEntry('qti-template-inline', TemplateElementMarshaller::class);
        $this->addMappingEntry('qti-rubric-block', RubricBlockMarshaller::class);
        $this->addMappingEntry('qti-template-block', TemplateElementMarshaller::class);
        $this->addMappingEntry('qti-template-inline', TemplateElementMarshaller::class);
        $this->addMappingEntry('qti-info-control', InfoControlMarshaller::class);

        // The class qtism\data\storage\xml\marshalling\SsmlSubMarshaller does not exist. Is that normal?
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
     */
    protected function instantiateMarshaller(ReflectionClass $class, array $args)
    {
        array_unshift($args, '3.0.0');
        return Reflection::newInstance($class, $args);
    }
}
