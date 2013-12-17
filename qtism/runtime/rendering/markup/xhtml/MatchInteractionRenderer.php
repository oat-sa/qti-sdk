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

use qtism\data\ShufflableCollection;
use qtism\runtime\rendering\AbstractRenderingEngine;
use qtism\data\QtiComponent;
use \DOMDocumentFragment;

/**
 * MatchInteraction renderer. Rendered components will be transformed as 
 * 'div' elements with a 'qti-matchInteraction' additional CSS class.
 * 
 * The following data-X attributes will be rendered:
 * 
 * * data-responseIdentifier = qti:interaction->responseIdentifier
 * * data-shuffle = qti:associateInteraction->shuffle
 * * data-maxAssociations = qti:associateInteraction->maxAssociations
 * * data-minAssociations = qti:associateInteraction->minAssociations
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MatchInteractionRenderer extends InteractionRenderer {
    
    public function __construct(AbstractRenderingEngine $renderingEngine = null) {
        parent::__construct($renderingEngine);
        $this->transform('div');
    }
    
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component) {
        
        parent::appendAttributes($fragment, $component);
        $this->additionalClass('qti-matchInteraction');
        
        $fragment->firstChild->setAttribute('data-shuffle', ($component->mustShuffle() === true) ? 'true' : 'false');
        $fragment->firstChild->setAttribute('data-maxAssociations', $component->getMaxAssociations());
        $fragment->firstChild->setAttribute('data-minAssociations', $component->getMinAssociations());
    }
    
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component) {
        parent::appendChildren($fragment, $component);
        
        // Retrieve the two rendered simpleMatchSets and shuffle if needed.
        if ($this->getRenderingEngine()->mustShuffle() === true) {
            
            $currentSet = 0;
            for ($i = 0; $i < $fragment->firstChild->childNodes->length; $i++) {
                $n = $fragment->firstChild->childNodes->item($i);
                if (Utils::hasClass($n, 'qti-simpleMatchSet') === true) {
                    $sets = $component->getSimpleMatchSets();
                    Utils::shuffle($n, new ShufflableCollection($sets[$currentSet]->getSimpleAssociableChoices()->getArrayCopy()));
                    $currentSet++;
                }
            }
        }
    }
}