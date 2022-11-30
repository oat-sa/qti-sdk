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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtism\runtime\rendering\markup\xhtml;

use qtism\data\content\xhtml\html5\Figcaption;
use qtism\data\QtiComponent;
use DOMDocumentFragment;

class FigcaptionRenderer extends Html5ElementRenderer
{
    /**
     * @param QtiComponent&Figcaption $component
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        parent::appendAttributes($fragment, $component, $base);

        /** @var Figcaption $component */
        if ($component->hasId()) {
            $fragment->firstChild->setAttribute('id', $component->getId());
        }
        if ($component->hasClass()) {
            $fragment->firstChild->setAttribute('class', $component->getClass());
        }
    }
}
