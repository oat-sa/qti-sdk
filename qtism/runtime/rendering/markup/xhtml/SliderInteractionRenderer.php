<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */

namespace qtism\runtime\rendering\markup\xhtml;

use qtism\data\content\interactions\Orientation;
use qtism\runtime\rendering\AbstractRenderingContext;
use qtism\data\QtiComponent;
use \DOMDocumentFragment;

/**
 * SliderInteraction renderer. Rendered components will be transformed as 
 * 'div' elements with a 'qti-sliderInteraction' additional CSS class.
 * 
 * An additional 'qti-horizontal' or 'qti-vertical' CSS class is also
 * added depending on the value of qti:sliderInteraction->orientation.
 * 
 * The following data-X attributes will be rendered:
 * 
 * * data-responseIdentifier = qti:interaction->responseIdentifier
 * * data-lowerBound = qti:sliderInteraction->lowerBound
 * * data-upperBound = qti:sliderInteraction->upperBound
 * * data-step = qti:sliderInteraction->step (Only if a value is present in QTI-XML)
 * * data-stepLabel = qti:sliderInteraction->stepLabel
 * * data-orientation = qti:sliderInteraction->orientation 
 * * data-reverse = qti:sliderInteraction->reverse
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SliderInteractionRenderer extends InteractionRenderer {
    
    public function __construct(AbstractRenderingContext $renderingContext = null) {
        parent::__construct($renderingContext);
        $this->transform('div');
    }
    
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component) {
        
        parent::appendAttributes($fragment, $component);
        $this->additionalClass('qti-sliderInteraction');
        $this->additionalClass(($component->getOrientation() === Orientation::HORIZONTAL) ? 'qti-horizontal' : 'qti-vertical');
        
        $fragment->firstChild->setAttribute('data-lowerBound', $component->getLowerBound());
        $fragment->firstChild->setAttribute('data-upperBound', $component->getUpperBound());
        $fragment->firstChild->setAttribute('data-stepLabel', ($component->mustStepLabel() === true) ? 'true' : 'false');
        $fragment->firstChild->setAttribute('data-orientation', ($component->getOrientation() === Orientation::VERTICAL) ? 'vertical' : 'horizontal');
        $fragment->firstChild->setAttribute('data-reverse', ($component->mustReverse() === true) ? 'true' : 'false');
        
        if ($component->hasStep() === true) {
            $fragment->firstChild->setAttribute('data-step', $component->getStep());
        }
    }
}