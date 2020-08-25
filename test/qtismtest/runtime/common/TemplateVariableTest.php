<?php

namespace qtismtest\runtime\common;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\TemplateVariable;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateVariableTest
 *
 * @package qtismtest\runtime\common
 */
class TemplateVariableTest extends QtiSmTestCase
{
    public function testCreateFromDataModel()
    {
        $decl = $this->createComponentFromXml('
            <templateDeclaration identifier="mytpl1" cardinality="single" baseType="identifier" paramVariable="true" mathVariable="false">
                <defaultValue>
                    <value>default</value>
                </defaultValue>
            </templateDeclaration>
        ');

        $var = TemplateVariable::createFromDataModel($decl);
        $this->assertInstanceOf(TemplateVariable::class, $var);
        $this->assertEquals('mytpl1', $var->getIdentifier());
        $this->assertEquals(Cardinality::SINGLE, $var->getCardinality());
        $this->assertEquals(BaseType::IDENTIFIER, $var->getCardinality());
        $this->assertEquals('default', $var->getDefaultValue()->getValue());
        $this->assertSame(null, $var->getValue());
        $this->assertTrue($var->isParamVariable());
        $this->assertFalse($var->isMathVariable());
    }
}
