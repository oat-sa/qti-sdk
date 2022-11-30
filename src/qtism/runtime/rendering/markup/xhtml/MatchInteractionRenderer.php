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
use qtism\data\ShufflableCollection;
use qtism\data\storage\xml\Utils as XmlUtils;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;

/**
 * MatchInteraction renderer. Rendered components will be transformed as
 * 'div' elements with the 'qti-blockInteraction' and 'qti-matchInteraction'
 * additional CSS class.
 *
 * The following data-X attributes will be rendered:
 *
 * * data-responseIdentifier = qti:interaction->responseIdentifier
 * * data-shuffle = qti:associateInteraction->shuffle
 * * data-max-associations = qti:associateInteraction->maxAssociations
 * * data-min-associations = qti:associateInteraction->minAssociations
 */
class MatchInteractionRenderer extends InteractionRenderer
{
    /**
     * Create a new MatchInteractionRenderer object.
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
        $this->additionalClass('qti-blockInteraction');
        $this->additionalClass('qti-matchInteraction');

        $fragment->firstChild->setAttribute('data-shuffle', ($component->mustShuffle() === true) ? 'true' : 'false');
        $fragment->firstChild->setAttribute('data-max-associations', (string)$component->getMaxAssociations());
        $fragment->firstChild->setAttribute('data-min-associations', (string)$component->getMinAssociations());
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        parent::appendChildren($fragment, $component, $base);

        // Retrieve the two rendered simpleMatchSets and shuffle if needed.
        $currentSet = 0;
        $choiceElts = [];
        $simpleMatchSetElts = [];

        for ($i = 0; $i < $fragment->firstChild->childNodes->length; $i++) {
            $n = $fragment->firstChild->childNodes->item($i);
            if (Utils::hasClass($n, 'qti-simpleMatchSet') === true) {
                $simpleMatchSetElts[] = $n;
                $sets = $component->getSimpleMatchSets();

                if ($this->getRenderingEngine()->getShufflingPolicy() === AbstractMarkupRenderingEngine::CONTEXT_AWARE && $component->mustShuffle()) {
                    Utils::shuffle($n, new ShufflableCollection($sets[$currentSet]->getSimpleAssociableChoices()->getArrayCopy()));
                }

                // Retrieve the two content of the two simpleMatchSets, separately.
                $choiceElts[] = XmlUtils::getChildElementsByTagName($n, 'li');
                $currentSet++;
            }
        }

        // simpleMatchSet class cannot be rendered into a table :/
        foreach ($simpleMatchSetElts as $sms) {
            $fragment->firstChild->removeChild($sms);
        }

        $table = $fragment->ownerDocument->createElement('table');
        $fragment->firstChild->appendChild($table);

        // Build the table header.
        $tr = $fragment->ownerDocument->createElement('tr');
        $table->appendChild($tr);
        // Empty upper left cell.
        $tr->appendChild($fragment->ownerDocument->createElement('th'));

        $ifVerticalStatementsStorage = [];

        for ($i = 0; $i < count($choiceElts[1]); $i++) {
            $ifStatements = Utils::extractStatements($choiceElts[1][$i], Utils::EXTRACT_IF);
            $incStatements = Utils::extractStatements($choiceElts[1][$i], Utils::EXTRACT_INCLUDE);

            if (empty($incStatements) === false) {
                $tr->appendChild($incStatements[0]);
            }

            if (empty($ifStatements) === false) {
                $ifVerticalStatementsStorage[$i] = $ifStatements;
                $tr->appendChild($ifStatements[0]);
            }

            $th = XmlUtils::changeElementName($choiceElts[1][$i], 'th');
            $tr->appendChild($th);

            if (empty($incStatements) === false) {
                $th->parentNode->insertBefore($incStatements[1], $th->nextSibling);
            }

            if (empty($ifStatements) === false) {
                $th->parentNode->insertBefore($ifStatements[1], $th->nextSibling);
            }
        }

        // Build all remaining rows.
        for ($i = 0; $i < count($choiceElts[0]); $i++) {
            $tr = $fragment->ownerDocument->createElement('tr');

            $ifStatements = Utils::extractStatements($choiceElts[0][$i], Utils::EXTRACT_IF);
            $incStatements = Utils::extractStatements($choiceElts[0][$i], Utils::EXTRACT_INCLUDE);

            $th = XmlUtils::changeElementName($choiceElts[0][$i], 'th');
            $tr->appendChild($th);

            $table->appendChild($tr);

            if (empty($incStatements) === false) {
                $tr->parentNode->insertBefore($incStatements[0], $tr);
            }

            if (empty($ifStatements) === false) {
                $tr->parentNode->insertBefore($ifStatements[0], $tr);
            }

            for ($j = 0; $j < count($choiceElts[1]); $j++) {
                $input = $fragment->ownerDocument->createElement('input');
                $input->setAttribute('type', 'checkbox');
                $td = $fragment->ownerDocument->createElement('td');
                $td->appendChild($input);
                $tr->appendChild($td);

                if (isset($ifVerticalStatementsStorage[$j])) {
                    $td->parentNode->insertBefore($ifVerticalStatementsStorage[$j][0]->cloneNode(), $td);
                    $td->parentNode->insertBefore($ifVerticalStatementsStorage[$j][1]->cloneNode(), $td->nextSibling);
                }
            }

            if (empty($incStatements) === false && isset($td)) {
                $tr->parentNode->insertBefore($incStatements[1], $tr->nextSibling);
            }

            if (empty($ifStatements) === false && isset($td)) {
                $tr->parentNode->insertBefore($ifStatements[1], $tr->nextSibling);
            }
        }
    }
}
