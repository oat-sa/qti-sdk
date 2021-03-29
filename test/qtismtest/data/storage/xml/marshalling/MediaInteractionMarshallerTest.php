<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\Media;
use qtism\data\content\interactions\MediaInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\html5\Audio;
use qtism\data\content\xhtml\html5\Video;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtismtest\QtiSmTestCase;

/**
 * Class MediaInteractionMarshallerTest
 */
class MediaInteractionMarshallerTest extends QtiSmTestCase
{
    /**
     * @dataProvider validMediaProvider
     * @param string $version
     * @param Media $media
     * @param string $mediaAsXml
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall(
        string $version,
        Media $media,
        string $mediaAsXml
    ): void {
        $mediaInteraction = new MediaInteraction('RESPONSE', false, $media, 'my-media');
        $mediaInteraction->setMinPlays(1);
        $mediaInteraction->setMaxPlays(2);
        $mediaInteraction->setLoop(true);
        $mediaInteraction->setXmlBase('/home/jerome');

        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $mediaInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory($version)->createMarshaller($mediaInteraction)->marshall($mediaInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        self::assertEquals(
            sprintf(
                '<mediaInteraction id="my-media" responseIdentifier="RESPONSE" autostart="false" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt>%s</mediaInteraction>',
                $mediaAsXml
            ),
            $dom->saveXML($element)
        );
    }

    /**
     * @dataProvider validMediaProvider
     * @param string $version
     * @param Media $media
     * @param string $mediaAsXml
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall(
        string $version,
        Media $media,
        string $mediaAsXml
    ): void {
        $element = $this->createDOMElement(
            sprintf(
                '<mediaInteraction id="my-media" responseIdentifier="RESPONSE" autostart="false" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt>%s</mediaInteraction>',
                $mediaAsXml
            )
        );

        $component = $this->getMarshallerFactory($version)->createMarshaller($element)->unmarshall($element);
        self::assertInstanceOf(MediaInteraction::class, $component);
        self::assertEquals('RESPONSE', $component->getResponseIdentifier());
        self::assertEquals('my-media', $component->getId());
        self::assertFalse($component->mustAutostart());
        self::assertEquals(1, $component->getMinPlays());
        self::assertTrue($component->mustLoop());
        self::assertEquals('/home/jerome', $component->getXmlBase());

        self::assertEquals($media, $component->getMedia());

        self::assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        self::assertEquals('Prompt...', $promptContent[0]->getContent());
    }

    public function validMediaProvider(): array
    {
        $src = 'my-video.mp4';
        $type = 'video/mp4';
        $width = 400;
        $height = 300;

        $object = new ObjectElement($src, $type);
        $object->setWidth($width);
        $object->setHeight($height);
        $objectAsXml = sprintf(
            '<object data="%s" type="%s" width="%s" height="%s"/>',
            $src,
            $type,
            $width,
            $height
        );

        $video = new Video();
        $video->setSrc($src);
        $video->setWidth($width);
        $video->setHeight($height);
        $videoAsXml = sprintf(
            '<video src="%s" width="%s" height="%s"/>',
            $src,
            $width,
            $height
        );

        $audio = new Audio();
        $audio->setSrc($src);
        $audioAsXml = sprintf(
            '<audio src="%s"/>',
            $src
        );

        return [
            ['2.1.0', $object, $objectAsXml],
            ['2.2.0', $object, $objectAsXml],
            ['2.2.0', $video, $videoAsXml],
            ['2.2.0', $audio, $audioAsXml],
        ];
    }

    /**
     * @dataProvider invalidMarshallProvider
     * @param Media $media
     * @param string $exceptionMessage
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshallWithHtml5MediaInQti21Fails(
        Media $media,
        string $exceptionMessage
    ): void {
        $mediaInteraction = new MediaInteraction('RESPONSE', false, $media, 'my-media');

        $this->expectException(MarshallerNotFoundException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->getMarshallerFactory('2.1.0')->createMarshaller($mediaInteraction)->marshall($mediaInteraction);
    }

    /**
     * @dataProvider invalidUnmarshallProvider
     * @param string $mediaAsXml
     * @param string $exceptionMessage
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshallWithHtml5MediaInQti21Fails(
        string $mediaAsXml,
        string $exceptionMessage
    ): void {
        $element = $this->createDOMElement(
            sprintf(
                '<mediaInteraction id="my-media" responseIdentifier="RESPONSE" autostart="false" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt>%s</mediaInteraction>',
                $mediaAsXml
            )
        );

        $this->expectException(MarshallerNotFoundException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function invalidMarshallProvider(): array
    {
        return [
            [new Video(), "No mapping entry found for QTI class name 'video'."],
            [new Audio(), "No mapping entry found for QTI class name 'audio'."],
        ];
    }

    public function invalidUnmarshallProvider(): array
    {
        return [
            ['<video/>', "No mapping entry found for QTI class name 'video'."],
            ['<audio/>', "No mapping entry found for QTI class name 'audio'."],
        ];
    }

    public function testUnmarshallNoMedia()
    {
        $element = $this->createDOMElement('
            <mediaInteraction id="my-media" responseIdentifier="RESPONSE" autostart="false" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt></mediaInteraction>        
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'mediaInteraction' element must contain exactly one media element ('object', 'video' or 'audio'), 0 given.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingAutoStart()
    {
        $element = $this->createDOMElement('
            <mediaInteraction id="my-media" responseIdentifier="RESPONSE" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="my-video.mp4" type="video/mp4" width="400" height="300"/></mediaInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'autostart' attribute is missing from the 'mediaInteraction' element.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <mediaInteraction id="my-media" autostart="true" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="my-video.mp4" type="video/mp4" width="400" height="300"/></mediaInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'mediaInteraction' element.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
