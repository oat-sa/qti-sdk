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

declare(strict_types=1);

namespace qtism\runtime\common;

use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\TemplateDeclaration;
use qtism\data\state\VariableDeclaration;
use UnexpectedValueException;

final class VariableFactory implements VariableFactoryInterface
{
    private const VARIABLE_DECLARATION_MAP = [
        TemplateDeclaration::class => TemplateVariable::class,
        OutcomeDeclaration::class  => OutcomeVariable::class,
        ResponseDeclaration::class => ResponseVariable::class,
    ];

    /**
     * @inheritDoc
     */
    public function createFromDataModel(VariableDeclaration $variableDeclaration): Variable
    {
        return [$this->createVariableClass($variableDeclaration), 'createFromDataModel']($variableDeclaration);
    }

    /**
     * @param VariableDeclaration $variableDeclaration A VariableDeclaration object from the QTI Data Model.
     * @return string|Variable A matching Variable class
     * @throws UnexpectedValueException If $variableDeclaration is not consistent.
     */
    private function createVariableClass(VariableDeclaration $variableDeclaration): string
    {
        if (!isset(self::VARIABLE_DECLARATION_MAP[$variableDeclaration::class])) {
            throw new UnexpectedValueException(
                sprintf(
                    '`%s` is an unexpected `%s` implementation.',
                    $variableDeclaration::class,
                    VariableDeclaration::class
                )
            );
        }

        return self::VARIABLE_DECLARATION_MAP[$variableDeclaration::class];
    }
}
