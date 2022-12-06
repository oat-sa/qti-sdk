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

namespace qtism\runtime\rendering\markup\goldilocks;

use qtism\runtime\rendering\markup\xhtml\MathRenderer;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

/**
 * The aQTI Rendering Engine.
 */
class GoldilocksRenderingEngine extends XhtmlRenderingEngine
{
    /**
     * Create a new AqtiRenderingEngine object.
     */
    public function __construct()
    {
        parent::__construct();

        // Register the MathRenderer to make it render
        // MathML without namespacing.
        $this->registerRenderer('math', new MathRenderer(null, false));
    }
}
