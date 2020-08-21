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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\markup\xhtml;

use DOMDocumentFragment;
use qtism\data\QtiComponent;
use qtism\data\ShowHide;

/**
 * The base class for FeedbackElement renderers. Rendered
 * elements will get the 'qti-feedbackElement' additional CSS class.
 *
 * It takes care of producing the following x-data attributes.
 *
 * * data-outcome-identifier = qti:feedbackElement->outcomeIdentifier
 * * data-show-hide = qti:feedbackElement->showHide
 * * data-identifier = qti:feedbackElement->identifier
 */
abstract class FeedbackElementRenderer extends BodyElementRenderer
{
    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     * @see \qtism\runtime\rendering\markup\xhtml\BodyElementRenderer::appendAttributes()
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-feedbackElement');

        $fragment->firstChild->setAttribute('data-outcome-identifier', $component->getOutcomeIdentifier());
        $fragment->firstChild->setAttribute('data-show-hide', ShowHide::getNameByConstant($component->getShowHide()));
        $fragment->firstChild->setAttribute('data-identifier', $component->getIdentifier());

        if ($this->getRenderingEngine()->getFeedbackShowHidePolicy() === XhtmlRenderingEngine::CONTEXT_STATIC) {
            $this->additionalClass(($component->getShowHide() === ShowHide::SHOW) ? 'qti-hide' : 'qti-show');
        }
    }
}
