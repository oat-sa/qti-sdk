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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\common;

use UnexpectedValueException;
use qtism\data\state\VariableDeclaration;

interface VariableFactoryInterface
{
    /**
     * Create a runtime Variable object from its Data Model representation.
     *
     * @param VariableDeclaration $variableDeclaration A VariableDeclaration object from the QTI Data Model.
     * @return Variable A Variable object.
     * @throws UnexpectedValueException If $variableDeclaration is not consistent.
     */
    public function createFromDataModel(VariableDeclaration $variableDeclaration): Variable;
}
