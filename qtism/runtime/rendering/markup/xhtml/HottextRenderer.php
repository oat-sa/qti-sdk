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
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;

/**
 *Hottext renderer. This renderer will transform the prompt into a 'span' element with an
 * additional 'qti-hottext' CSS class.
 *
 * Depending on the value of the qti:choice->showHide attribute and only if
 * a value for qti:choice->templateIdentifier is defined, an additional CSS class with
 * a value of 'qti-show' or 'qti-hide' will be set.
 *
 * Moreover, the following data will be set to the data set of the element
 * with the help of the data-X attributes:
 *
 * * data-identifier = qti:choice->identifier
 * * data-fixed = qti:choice->fixed
 * * data-template-identifier = qti:choice->templateIdentifier (only if qti:choice->templateIdentifier is set).
 * * data-show-hide = qti:choice->showHide (only if qti:choice->templateIdentifier is set).
 */
class HottextRenderer extends ChoiceRenderer
{
    /**
     * Create a new HottextRenderer object.
     *
     * @param AbstractMarkupRenderingEngine $renderingEngine
     */
    public function __construct(AbstractMarkupRenderingEngine $renderingEngine = null)
    {
        parent::__construct($renderingEngine);
        $this->transform('span');
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     * @see \qtism\runtime\rendering\markup\xhtml\ChoiceRenderer::appendAttributes()
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-hottext');
    }

    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        parent::appendChildren($fragment, $component);

        $inputElt = $fragment->ownerDocument->createElement('input');
        $inputElt->setAttribute('type', 'radio');

        $fragment->firstChild->insertBefore($inputElt, $fragment->firstChild->firstChild);
    }
}
