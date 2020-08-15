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
use qtism\data\storage\xml\Utils;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;
use qtism\runtime\rendering\RenderingException;
use RuntimeException;

/**
 * SSML Sub Renderer.
 */
class SsmlSubRenderer extends ExternalQtiComponentRenderer
{
    /**
     * Create a SsmlSubRenderer object.
     *
     * @param AbstractMarkupRenderingEngine $renderingEngine
     */
    public function __construct(AbstractMarkupRenderingEngine $renderingEngine = null)
    {
        parent::__construct($renderingEngine);
    }

    /**
     * @see \qtism\runtime\rendering\markup\xhtml\ExternalQtiComponentRenderer::appendChildren()
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        try {
            $dom = $component->getXml();
            $node = $fragment->ownerDocument->importNode($dom->documentElement, true);
            $node = Utils::anonimizeElement($node);

            foreach ($node->childNodes as $childNode) {
                $fragment->appendChild($childNode);
            }
        } catch (RuntimeException $e) {
            $msg = "An error occurred while rendering the XML content of the 'SSML Sub' external component.";
            throw new RenderingException($msg, RenderingException::UNKNOWN, $e);
        }
    }
}
