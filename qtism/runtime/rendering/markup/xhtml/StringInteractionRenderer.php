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

use qtism\data\QtiComponent;
use \DOMDocumentFragment;

/**
 * StringInteraction renderer. Rendered elements will be applied
 * the 'qti-stringInteraction' additional CSS class.
 * 
 * The following data-X attributes will be rendered:
 * 
 * * data-responseIdentifier = qti:interaction->responseIdentifier
 * * data-base = qti:stringInteraction->base
 * * data-stringIdentifier = qti:stringInteraction->stringIdentifier (only if set in QTI-XML counter-part).
 * * data-expectedLength = qti:stringInteraction->expectedLength (only if set in QTI-XML counter-part).
 * * data-patternMask = qti:stringInteraction->patternMask (only if set in QTI-XML counter-part).
 * * data-placeholderText = qti:stringInteraction->placeholderText (only if set in QTI-XML counter-part).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class StringInteractionRenderer extends InteractionRenderer {
    
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = '') {  
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-stringInteraction');
        
        $fragment->firstChild->setAttribute('data-base', $component->getBase());
        
        if ($component->hasStringIdentifier() === true) {
            $fragment->firstChild->setAttribute('data-stringIdentifier', $component->getStringIdentifier());
        }
        
        if ($component->hasExpectedLength() === true) {
            $fragment->firstChild->setAttribute('data-expectedLength', $component->getExpectedLength());
        }
        
        if ($component->hasPatternMask() === true) {
            $fragment->firstChild->setAttribute('data-patternMask', $component->getPatternMask());
        }
        
        if ($component->hasPlaceholderText() === true) {
            $fragment->firstChild->setAttribute('data-placeholderText', $component->getPlaceholderText());
        }
    }
}