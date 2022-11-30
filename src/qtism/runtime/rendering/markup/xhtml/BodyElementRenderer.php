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
use qtism\data\content\Direction;
use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\QtiComponent;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;

/**
 * BodyElement renderer.
 *
 * This rendered will add the 'qti-bodyElement' class to the rendered
 * elements.
 */
class BodyElementRenderer extends AbstractXhtmlRenderer
{
    /**
     * Create a new BodyElementRenderer object.
     *
     * @param AbstractMarkupRenderingEngine $renderingEngine
     */
    public function __construct(AbstractMarkupRenderingEngine $renderingEngine = null)
    {
        parent::__construct($renderingEngine);
        $this->additionalClass('qti-bodyElement');
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-' . $component->getQtiClassName());

        if ($component->hasId() === true) {
            $fragment->firstChild->setAttribute('id', $component->getId());
        }

        if ($component->hasClass() === true) {
            $classes = explode("\x20", $component->getClass());
            foreach ($classes as $class) {
                $this->additionalUserClass($class);
            }
        }

        if ($component->hasLang() === true) {
            $fragment->firstChild->setAttribute('lang', $component->getLang());
        }

        if ($component->getDir() !== Direction::AUTO) {
            $fragment->firstChild->setAttribute('dir', Direction::getNameByConstant($component->getDir()));
        }

        if (($ariaControls = $component->getAriaControls()) !== '') {
            $fragment->firstChild->setAttribute('aria-controls', $ariaControls);
        }

        if (($ariaDescribedBy = $component->getAriaDescribedBy()) !== '') {
            $fragment->firstChild->setAttribute('aria-describedby', $ariaDescribedBy);
        }

        if (($ariaFlowTo = $component->getAriaFlowTo()) !== '') {
            $fragment->firstChild->setAttribute('aria-flowto', $ariaFlowTo);
        }

        if (($ariaLabelledBy = $component->getAriaLabelledBy()) !== '') {
            $fragment->firstChild->setAttribute('aria-labelledby', $ariaLabelledBy);
        }

        if (($ariaOwns = $component->getAriaOwns()) !== '') {
            $fragment->firstChild->setAttribute('aria-owns', $ariaOwns);
        }

        if (($ariaLevel = $component->getAriaLevel()) !== '') {
            $fragment->firstChild->setAttribute('aria-level', $ariaLevel);
        }

        if (($ariaLive = $component->getAriaLive()) !== false) {
            $fragment->firstChild->setAttribute('aria-live', AriaLive::getNameByConstant($ariaLive));
        }

        if (($ariaOrientation = $component->getAriaOrientation()) !== false) {
            $fragment->firstChild->setAttribute('aria-orientation', AriaOrientation::getNameByConstant($ariaOrientation));
        }

        if (($ariaLabel = $component->getAriaLabel()) !== '') {
            $fragment->firstChild->setAttribute('aria-label', $ariaLabel);
        }

        if (($ariaHidden = $component->getAriaHidden()) !== false) {
            $fragment->firstChild->setAttribute('aria-hidden', 'true');
        }
    }
}
