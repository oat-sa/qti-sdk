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

/**
 * Allows to specify options to choose the rendering
 * of the if/else loops.
 */
class ConditionRenderingOptions
{
    /**
     * @var int The number spaces of indentation in an if/else loop
     */
    private $indentation;

    /**
     * @var int The number of spaces in the indentation by default.
     */

    private static $defaultIdentation = 4;

    /**
     * @return ConditionRenderingOptions The format by default for the
     * ConditionRenderingOptions.
     */
    public static function getDefault()
    {
        return new ConditionRenderingOptions(ConditionRenderingOptions::$defaultIdentation);
    }

    /**
     * Creates a new instance of a ConditionRenderingOptions.
     *
     * @param $indentation int The number spaces of indentation in an if/else loop
     */

    public function __construct($indentation)
    {
        if ($indentation > 0) {
            $this->indentation = $indentation;
        } else {
            $this->indentation = 0;
        }
    }

    /**
     * Gets the indentation of this ConditionRenderingOptions.
     *
     * @return int
     */

    public function getIndentation()
    {
        return $this->indentation;
    }
}
