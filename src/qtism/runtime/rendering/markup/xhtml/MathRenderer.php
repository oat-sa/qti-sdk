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
 * Math Renderer.
 */
class MathRenderer extends ExternalQtiComponentRenderer
{
    /**
     * Whether to embed the resulting output into the MathML namespace.
     */
    private $namespaceOutput = true;

    /**
     * Create a MathRenderer object.
     *
     * @param AbstractMarkupRenderingEngine $renderingEngine
     * @param bool $namespaceOutput Whether to embed the resulting output into the MathML namespace.
     */
    public function __construct(AbstractMarkupRenderingEngine $renderingEngine = null, $namespaceOutput = true)
    {
        parent::__construct($renderingEngine);
        $this->setNamespaceOutput($namespaceOutput);
    }

    /**
     * Set whether the resulting output must be embedded in the MathML namespace.
     *
     * @param bool $namespaceOutput
     */
    public function setNamespaceOutput($namespaceOutput)
    {
        $this->namespaceOutput = $namespaceOutput;
    }

    /**
     * Whether the resulting output must be embedded in the MathML namespace.
     *
     * @return bool
     */
    public function mustNamespaceOutput()
    {
        return $this->namespaceOutput;
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     * @throws RenderingException
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        try {
            $dom = $component->getXml();
            $node = $fragment->ownerDocument->importNode($dom->documentElement, true);
            $nodeNamespaceUri = $node->namespaceURI;
            $node = Utils::anonimizeElement($node);

            if ($this->mustNamespaceOutput() === true) {
                $node->setAttribute('xmlns', $nodeNamespaceUri);
            }

            $fragment->appendChild($node);
        } catch (RuntimeException $e) {
            $msg = "An error occurred while rendering the XML content of the 'MathML' external component.";
            throw new RenderingException($msg, RenderingException::UNKNOWN, $e);
        }
    }
}
