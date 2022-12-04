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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\content\FlowCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\html5\Rb;
use qtism\data\content\xhtml\html5\Rt;
use qtism\data\content\xhtml\html5\Rp;
use qtism\data\content\xhtml\html5\Ruby;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;

class Html5RubyMarshallerTest extends Html5ElementMarshallerTest
{
    private const SUBJECT_QTI_CLASS_NAME = 'ruby';

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5MarshallingOnlyInQti22AndAbove(new Ruby(), self::SUBJECT_QTI_CLASS_NAME);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $id = 'id';
        $class = 'testclass';

        $expected = sprintf(
            '<%1$s id="%2$s" class="%3$s"><%4$s>真</%4$s><%5$s>まこと</%5$s><%6$s>真</%6$s></%7$s>',
            $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
            $id,
            $class,
            $this->prefixTag(Rt::QTI_CLASS_NAME),
            $this->prefixTag(Rb::QTI_CLASS_NAME),
            $this->prefixTag(Rp::QTI_CLASS_NAME),
            $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
        );

        $rb = new Rb();
        $rb->setContent(new InlineCollection([new TextRun('まこと')]));

        $rt = new Rt();
        $rt->setContent(new InlineCollection([new TextRun('真')]));

        $rp = new Rp();
        $rp->setContent(new InlineCollection([new TextRun('真')]));

        $object = new Ruby(null, null, $id, $class);
        $object->setContent(new FlowCollection([ $rt, $rb, $rp]));

        $this->assertMarshalling($expected, $object);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $expected = sprintf(
            '<%s/>',
            $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME)
        );

        $ruby = new Ruby();

        $this->assertMarshalling($expected, $ruby);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5UnmarshallingOnlyInQti22AndAbove(
            sprintf(
                '<%s></%s>',
                $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
                $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
            ),
            self::SUBJECT_QTI_CLASS_NAME
        );
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $id = 'id';
        $class = 'testclass';

        $xml = sprintf(
            '<%1$s id="%2$s" class="%3$s"></%4$s>',
            $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
            $id,
            $class,
            $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
        );

        $expected = new Ruby(null, null, $id, $class);

        $this->assertUnmarshalling($expected, $xml);
    }

    public function testUnmarshall22WithDefaultValues(): void
    {
        $xml = sprintf(
            '<%s></%s>',
            $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
            $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
        );

        $expected = new Ruby();

        $this->assertUnmarshalling($expected, $xml);
    }
}
