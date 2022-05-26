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

use qtism\data\content\xhtml\html5\Figcaption;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;

class Html5FigcaptionMarshallerTest extends Html5ElementMarshallerTest
{
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5MarshallingOnlyInQti22AndAbove(new Figcaption(), Figcaption::QTI_CLASS_NAME_FIGCAPTION);
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
            '<%1$s id="%2$s " class="%3$s">text content</%1$s>',
            $this->namespaceTag(Figcaption::QTI_CLASS_NAME_FIGCAPTION),
            $id,
            $class,
        );

        $object = new Figcaption(null, null, $id, $class);

        $this->assertMarshalling($expected, $object);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $expected = sprintf('<%1$s>text content</%1$s>', $this->namespaceTag(Figcaption::QTI_CLASS_NAME_FIGCAPTION));

        $video = new Figcaption();

        $this->assertMarshalling($expected, $video);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5UnmarshallingOnlyInQti22AndAbove(
            sprintf('<%1$s></%1$s>', $this->namespaceTag(Figcaption::QTI_CLASS_NAME_FIGCAPTION)),
            Figcaption::QTI_CLASS_NAME_FIGCAPTION
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
            '<%1$s id="%2$s " class="%3$s">text content</%1$s>',
            $this->namespaceTag(Figcaption::QTI_CLASS_NAME_FIGCAPTION),
            $id,
            $class,
        );

        $expected = new Figcaption(null, null, $id, $class);

        $this->assertUnmarshalling($expected, $xml);
    }

    public function testUnmarshall22WithDefaultValues(): void
    {
        $xml = sprintf('<%1$s>text content</%1$s>', $this->namespaceTag(Figcaption::QTI_CLASS_NAME_FIGCAPTION));

        $expected = new Figcaption();

        $this->assertUnmarshalling($expected, $xml);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshallWithWrongTypesOrValues(string $xml, string $exception, string $message): void
    {
        $this->assertUnmarshallingException($xml, $exception, $message);
    }
}
