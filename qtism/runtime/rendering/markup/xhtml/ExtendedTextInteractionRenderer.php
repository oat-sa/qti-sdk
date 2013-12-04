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

use qtism\data\content\interactions\TextFormat;

use qtism\runtime\rendering\AbstractRenderingContext;
use qtism\data\QtiComponent;
use \DOMDocumentFragment;

/**
 * ExtendedTextInteraction renderer. Will render components
 * as 'div' elements with type 'text' and an additional class
 * of 'qti-extendedTextInteraction'.
 * 
 * The generated 'div' element will be composed of:
 * * A 'div' element with the additional 'qti-prompt' CSS class if a prompt is present in the interaction.
 * * A 'textarea' element representing the text input.
 * 
 * The following data-X attributes will be rendered:
 * 
 * * data-responseIdentifier = qti:interaction->responseIdentifier
 * * data-base = qti:stringInteraction->base
 * * data-stringIdentifier = qti:stringInteraction->stringIdentifier (only if set in QTI-XML counter-part).
 * * data-expectedLength = qti:stringInteraction->expectedLength (only if set in QTI-XML counter-part).
 * * data-patternMask = qti:stringInteraction->patternMask (only if set in QTI-XML counter-part).
 * * data-placeholderText = qti:stringInteraction->placeholderText (only if set in QTI-XML counter-part).
 * * data-maxStrings = qti:extendedTextInteraction->maxStrings (only if set in QTI-XML counter-part).
 * * data-minStrings = qti:extendedTextInteraction->minStrings.
 * * data-expectedLines = qti:extendedTextInteraction->expectedLines (only if set in QTI-XML counter-part).
 * * data-format = qti:extendedTextInteraction->format.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExtendedTextInteractionRenderer extends StringInteractionRenderer {
    
    public function __construct(AbstractRenderingContext $renderingContext = null) {
        parent::__construct($renderingContext);
        $this->transform('div');
    }
    
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component) {
        parent::appendAttributes($fragment, $component);
        $this->additionalClass('qti-extendedTextInteraction');
        
        $fragment->firstChild->setAttribute('data-minStrings', $component->getMinStrings());
        $fragment->firstChild->setAttribute('data-format', TextFormat::getNameByConstant($component->getFormat()));
        
        if ($component->hasMaxStrings() === true) {
            $fragment->firstChild->setAttribute('data-maxStrings', $component->getMaxStrings());
        }
        
        if ($component->hasExpectedLines() === true) {
            $fragment->firstChild->setAttribute('data-expectedLines', $component->getExpectedLines());
        }
    }
    
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component) {
        parent::appendChildren($fragment, $component);
        
        // Append a textarea...
        $textarea = $fragment->ownerDocument->createElement('textarea');
        // Makes the textarea non self-closing...
        $textarea->appendChild($fragment->ownerDocument->createTextNode(''));
        $fragment->firstChild->appendChild($textarea);
    }
}