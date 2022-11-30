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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\markup\xhtml;

use DOMDocumentFragment;
use qtism\data\QtiComponent;
use qtism\data\storage\php\Utils as PhpUtils;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;
use qtism\runtime\rendering\markup\Utils;

/**
 * PrintedVariable Renderer.
 *
 * The following data-X attributes will be rendered:
 *
 * * data-identifier = qti:printedVariable->identifier
 * * data-format = qti:printedVariable->format
 * * data-power-form = qti:printedVariable->powerForm
 * * data-base = qti:printedVariable->base
 * * data-index = qti:printedVariable->index
 * * data-delimiter = qti:printedVariable->delimiter
 * * data-field = qti:printedVariable->field
 * * data-mapping-indicator = qti:printedVariable->mappingIndicator
 */
class PrintedVariableRenderer extends BodyElementRenderer
{
    /**
     * Create a new PrintedVariableRenderer object.
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
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-printedVariable');

        $fragment->firstChild->setAttribute('data-identifier', $component->getIdentifier());

        if ($component->hasFormat() === true) {
            $fragment->firstChild->setAttribute('data-format', $component->getFormat());
        }

        $fragment->firstChild->setAttribute(
            'data-power-form',
            (string)($component->mustPowerForm() === true) ? 'true' : 'false'
        );
        $fragment->firstChild->setAttribute('data-base', (string)$component->getBase());

        if ($component->hasIndex() === true) {
            $fragment->firstChild->setAttribute('data-index', (string)$component->getIndex());
        }

        $fragment->firstChild->setAttribute('data-delimiter', (string)$component->getDelimiter());

        if ($component->hasField() === true) {
            $fragment->firstChild->setAttribute('data-field', (string)$component->getField());
        }

        $fragment->firstChild->setAttribute('data-mapping-indicator', (string)$component->getMappingIndicator());
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        $renderingEngine = $this->getRenderingEngine();

        if ($renderingEngine !== null) {
            switch ($renderingEngine->getPrintedVariablePolicy()) {
                case AbstractMarkupRenderingEngine::CONTEXT_AWARE:
                    $value = Utils::printVariable(
                        $this->getRenderingEngine()->getState(),
                        $component->getIdentifier(),
                        $component->getFormat(),
                        $component->mustPowerForm(),
                        $component->getBase(),
                        $component->getIndex(),
                        $component->getDelimiter(),
                        $component->getField(),
                        $component->getMappingIndicator()
                    );
                    $fragment->firstChild->appendChild($fragment->ownerDocument->createTextNode($value));
                    break;

                case AbstractMarkupRenderingEngine::TEMPLATE_ORIENTED:
                    $base = $component->getBase();
                    $index = $component->getIndex();

                    $params = [
                        '$' . $renderingEngine->getStateName(),
                        PhpUtils::doubleQuotedPhpString($component->getIdentifier()),
                        PhpUtils::doubleQuotedPhpString($component->getFormat()),
                        ($component->mustPowerForm() === true) ? 'true' : 'false',
                        (is_int($base)) ? $base : PhpUtils::doubleQuotedPhpString($base),
                        (is_int($index)) ? $index : PhpUtils::doubleQuotedPhpString($index),
                        PhpUtils::doubleQuotedPhpString($component->getDelimiter()),
                        PhpUtils::doubleQuotedPhpString($component->getField()),
                        PhpUtils::doubleQuotedPhpString($component->getMappingIndicator()),
                    ];

                    $value = ' qtism-printVariable(' . implode(', ', $params) . ') ';
                    $fragment->firstChild->appendChild($fragment->ownerDocument->createComment($value));
                    break;
            }
        }
    }
}
