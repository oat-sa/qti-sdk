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
 * OrderInteraction renderer. Rendered components will be transformed as 
 * 'div' elements with a 'qti-orderInteraction' additional CSS class.
 * 
 * The following data-X attributes will be rendered:
 * 
 * * data-shuffle = qti:orderInteraction->shuffle
 * * data-maxChoices = qti:orderInteraction->maxChoices (only if specified in QTI-XML representation)
 * * data-minChoices = qti:orderInteraction->minChoices (only if specified in QTI-XML representation)
 * * data-orientation = qti:orderInteraction->orientation
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OrderInteractionRenderer extends InteractionRenderer {
    
    public function __construct(AbstractRenderingContext $renderingContext = null) {
        parent::__construct($renderingContext);
        $this->transform('div');
    }
    
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component) {
        
        parent::appendAttributes($fragment, $component);
        $this->additionalClass('qti-orderInteraction');
        
        $fragment->firstChild->setAttribute('data-shuffle', ($component->mustShuffle() === true) ? 'true' : 'false');
        
        if ($component->hasMaxChoices() === true) {
            $fragment->firstChild->setAttribute('data-maxChoices', $component->getMaxChoices());
        }
        else {
            $fragment->firstChild->setAttribute('data-minChoices', $component->getMinChoices());
        }
        
        $fragment->firstChild->setAttribute('data-orientation', ($component->getOrientation() === Orientation::VERTICAL) ? 'vertical' : 'horizontal');
    }
}