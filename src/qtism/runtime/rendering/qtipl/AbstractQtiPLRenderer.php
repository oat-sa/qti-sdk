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
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\qtipl;

use qtism\runtime\rendering\Renderable;

/**
 *
 * An abstract representation of the QtiPLRenderer.
 */
abstract class AbstractQtiPLRenderer implements Renderable
{
    /**
     * @TODO
     * @var ConditionRenderingOptions
     */
    private $cro;

    /**
     * Gets the ConditionRenderingOptions of this QtiPLRenderer.
     *
     * @return ConditionRenderingOptions
     */
    public function getCRO()
    {
        return $this->cro;
    }

    /**
     * Sets the ConditionRenderingOptions to this QtiPLRenderer.
     *
     * @param $cro ConditionRenderingOptions
     */
    public function setCRO($cro)
    {
        $this->cro = $cro;
    }

    /**
     * Creates a new instance of a QtiPLRenderer.
     *
     * @param $cro ConditionRenderingOptions The ConditionRenderingOptions assigned to this QtiPLRenderer.
     */
    public function __construct($cro)
    {
        $this->setCRO($cro);
    }
}
