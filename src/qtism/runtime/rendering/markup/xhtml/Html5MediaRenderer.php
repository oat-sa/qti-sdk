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
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\markup\xhtml;

use DOMDocumentFragment;
use qtism\data\content\enums\CrossOrigin;
use qtism\data\content\enums\Preload;
use qtism\data\content\xhtml\html5\Html5Media;
use qtism\data\QtiComponent;

/**
 * Html5Media renderer.
 */
class Html5MediaRenderer extends Html5ElementRenderer
{
    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        parent::appendAttributes($fragment, $component, $base);

        /** @var Html5Media $component */
        if ($component->hasSrc()) {
            $fragment->firstChild->setAttribute('src', $this->transformUri($component->getSrc(), $base));
        }
        if ($component->hasAutoPlay()) {
            $fragment->firstChild->setAttribute('autoplay', 'true');
        }
        if ($component->getControls()) {
            $fragment->firstChild->setAttribute('controls', 'true');
        }
        if ($component->hasCrossOrigin()) {
            $fragment->firstChild->setAttribute('crossorigin', CrossOrigin::getNameByConstant($component->getCrossOrigin()));
        }
        if ($component->getLoop()) {
            $fragment->firstChild->setAttribute('loop', 'true');
        }
        if ($component->hasMediaGroup()) {
            $fragment->firstChild->setAttribute('mediagroup', $component->getMediaGroup());
        }
        if ($component->hasMuted()) {
            $fragment->firstChild->setAttribute('muted', 'true');
        }
        if ($component->hasPreload()) {
            $fragment->firstChild->setAttribute('preload', Preload::getNameByConstant($component->getPreload()));
        }
    }
}
