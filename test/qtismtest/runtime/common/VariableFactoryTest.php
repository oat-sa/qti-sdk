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

namespace qtismtest\runtime\common;

use UnexpectedValueException;
use qtism\common\enums\BaseType;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\TemplateDeclaration;
use qtism\data\state\VariableDeclaration;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\TemplateVariable;
use qtism\runtime\common\Variable;
use qtism\runtime\common\VariableFactory;
use qtismtest\QtiSmTestCase;

class VariableFactoryTest extends QtiSmTestCase
{
    /** @var VariableFactory */
    private $sut;

    /**
     * @before
     */
    public function setUpSut(): void
    {
        $this->sut = new VariableFactory();
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string|Variable $expectedVariableClass
     * @param VariableDeclaration $variableDeclaration
     */
    public function testCreateFromDataModel(
        string $expectedVariableClass,
        VariableDeclaration $variableDeclaration
    ): void {
        $this->assertEquals(
            $expectedVariableClass::createFromDataModel($variableDeclaration),
            $this->sut->createFromDataModel($variableDeclaration)
        );
    }

    public function testCreateFromUnknownDataModel(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->sut->createFromDataModel(
            new class ('test', BaseType::INTEGER) extends VariableDeclaration {
            }
        );
    }

    public function dataProvider(): array
    {
        return [
            TemplateDeclaration::class => [TemplateVariable::class, new TemplateDeclaration('test', BaseType::INTEGER)],
            OutcomeDeclaration::class  => [OutcomeVariable::class, new OutcomeDeclaration('test', BaseType::INTEGER)],
            ResponseDeclaration::class => [ResponseVariable::class, new ResponseDeclaration('test', BaseType::INTEGER)],
        ];
    }
}
