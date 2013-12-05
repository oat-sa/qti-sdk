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

use qtism\data\ShowHide;
use qtism\data\QtiComponent;
use \DOMDocumentFragment;

/**
 * The base class for FeedbackElement renderers.
 * 
 * It takes care of producing the following x-data attributes.
 * 
 * * data-outcomeIdentifier = qti:feedbackElement->outcomeIdentifier
 * * data-showHide = qti:feedbackElement->showHide
 * * data-identifier = qti:feedbackElement->identifier
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class FeedbackElementRenderer extends BodyElementRenderer {
    
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component) {
        parent::appendAttributes($fragment, $component);
        
        $fragment->setAttribute('data-outcomeIdentifier', $component->getOutcomeIdentifier());
        $fragment->setAttribute('data-showHide', ShowHide::getNameByConstant($component->getShowHide()));
        $fragment->setAttribute('data-identifier', $component->getIdentifier());
        
        $this->additionalClass(($component->getShowHide() === ShowHide::SHOW) ? 'qti-hide' : 'qti-show');
    }
}