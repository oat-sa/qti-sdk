<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\interactions;

use InvalidArgumentException;

/**
 * From IMS QTI:
 *
 * A simple run of text to be displayed to the user, may be subject to
 * variable value substitution with printedVariable.
 */
class InlineChoice extends SimpleChoice
{
    /**
     * Create a new InlineChoice object.
     *
     * @param string $identifier The identifier of the InlineChoice.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If any of the arguments is invalid.
     */
    public function __construct($identifier, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($identifier, $id, $class, $lang, $label);
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'inlineChoice';
    }
}
