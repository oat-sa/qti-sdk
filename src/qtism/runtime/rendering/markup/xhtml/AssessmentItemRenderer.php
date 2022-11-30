<?php

declare(strict_types=1);

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
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;

/**
 * The renderer for AssessmentItem elements. Rendered elements
 * will get a 'qti-assessmentItem' additional CSS class and will be
 * transformed as 'div' elements.
 *
 * It also takes care of producing the following x-data attributes.
 *
 * * data-identifier = qti:assessmentItem->identifier
 * * data-title = qti:assessmentItem->title
 * * data-label = qti:assessmentItem->label (Only if present in QTI-XML)
 * * data-lang = qti:assessmentItem->lang (Only if present in QTI-XML)
 * * data-adaptive = qti:assessmentItem->adaptive
 * * data-time-dependent = qti:assessmentItem->timeDependent
 */
class AssessmentItemRenderer extends AbstractXhtmlRenderer
{
    /**
     * Create a new AssessmentItemRenderer object.
     *
     * @param AbstractMarkupRenderingEngine $renderingEngine
     */
    public function __construct(AbstractMarkupRenderingEngine $renderingEngine = null)
    {
        parent::__construct($renderingEngine);
        $this->transform('div');
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-assessmentItem');

        $fragment->firstChild->setAttribute('data-identifier', $component->getIdentifier());
        $fragment->firstChild->setAttribute('data-title', $component->getTitle());

        if ($component->hasLabel() === true) {
            $fragment->firstChild->setAttribute('data-label', $component->getLabel());
        }

        if ($component->hasLang() === true) {
            $fragment->firstChild->setAttribute('data-lang', $component->getLang());
        }

        $fragment->firstChild->setAttribute('data-adaptive', ($component->isAdaptive() === true) ? 'true' : 'false');
        $fragment->firstChild->setAttribute('data-time-dependent', ($component->isTimeDependent() === true) ? 'true' : 'false');
    }
}
