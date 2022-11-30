<?php

declare(strict_types=1);

namespace qtismtest\runtime\common;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\TemplateVariable;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateVariableTest
 */
class TemplateVariableTest extends QtiSmTestCase
{
    public function testCreateFromDataModel(): void
    {
        $decl = $this->createComponentFromXml('
            <templateDeclaration identifier="mytpl1" cardinality="single" baseType="identifier" paramVariable="true" mathVariable="false">
                <defaultValue>
                    <value>default</value>
                </defaultValue>
            </templateDeclaration>
        ');

        $var = TemplateVariable::createFromDataModel($decl);
        $this::assertInstanceOf(TemplateVariable::class, $var);
        $this::assertEquals('mytpl1', $var->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $var->getCardinality());
        $this::assertEquals(BaseType::IDENTIFIER, $var->getCardinality());
        $this::assertEquals('default', $var->getDefaultValue()->getValue());
        $this::assertNull($var->getValue());
        $this::assertTrue($var->isParamVariable());
        $this::assertFalse($var->isMathVariable());
    }
}
