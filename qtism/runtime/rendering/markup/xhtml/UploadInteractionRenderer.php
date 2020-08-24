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
 * UploadInteraction renderer. Rendered components will be transformed as
 * 'div' elements with the 'qti-uploadInteraction' and 'qti-blockInteraction' additional CSS classes.
 *
 * * An additional <input type="file"> element is appended to the interaction container. The accept attribute will be set with the qti:uploadInteraction->type attribute value if specified.
 * * An addition <input type="submit"> element is appended to the interaction container.
 *
 * The following data-X attributes will be rendered:
 *
 * * data-response-identifier = qti:interaction->responseIdentifier
 * * data-type = qti:uploadInteraction->type (Only if present in QTI description)
 */
class UploadInteractionRenderer extends InteractionRenderer
{
    /**
     * Create a new UploadInteractionRenderer object.
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
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        parent::appendAttributes($fragment, $component);
        $this->additionalClass('qti-blockInteraction');
        $this->additionalClass('qti-uploadInteraction');

        if ($component->hasType() === true) {
            $fragment->firstChild->setAttribute('data-type', $component->getType());
        }
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        parent::appendChildren($fragment, $component);

        $inputFileElt = $fragment->ownerDocument->createElement('input');
        $inputFileElt->setAttribute('type', 'file');

        if ($component->hasType() === true) {
            $inputFileElt->setAttribute('accept', $component->getType());
        }

        $submitElt = $fragment->ownerDocument->createElement('input');
        $submitElt->setAttribute('type', 'submit');

        $fragment->firstChild->appendChild($inputFileElt);
        $fragment->firstChild->appendChild($submitElt);
    }
}
