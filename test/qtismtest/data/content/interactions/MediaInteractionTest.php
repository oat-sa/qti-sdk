<?php
namespace qtismtest\data\content\interactions;

use qtismtest\QtiSmTestCase;
use qtism\data\content\interactions\MediaInteraction;
use qtism\data\content\xhtml\Object;

class MediaInteractionTest extends QtiSmTestCase
{
    public function testCreateWrongAutostartType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'autostart' argument must be a boolean value, 'integer' given."
        );
        
        $mediaInteraction = new MediaInteraction('RESPONSE', 999, new Object('http://myobject.com/video.mpg', 'video/mpeg'));
    }
    
    public function testSetMinPlaysWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'minPlays' argument must be a positive (>= 0) integer, 'boolean' given."
        );
        
        $mediaInteraction = new MediaInteraction('RESPONSE', true, new Object('http://myobject.com/video.mpg', 'video/mpeg'));
        $mediaInteraction->setMinPlays(true);
    }
    
    public function testSetMaxPlaysWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'maxPlays' argument must be a positive (>= 0) integer, 'boolean' given."
        );
        
        $mediaInteraction = new MediaInteraction('RESPONSE', true, new Object('http://myobject.com/video.mpg', 'video/mpeg'));
        $mediaInteraction->setMaxPlays(true);
    }
    
    public function testSetLoopWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'loop' argument must be a boolean value, 'integer' given."
        );
        
        $mediaInteraction = new MediaInteraction('RESPONSE', true, new Object('http://myobject.com/video.mpg', 'video/mpeg'));
        $mediaInteraction->setLoop(999);
    }
    
    public function testHasMinMAxPlays()
    {
        $mediaInteraction = new MediaInteraction('RESPONSE', true, new Object('http://myobject.com/video.mpg', 'video/mpeg'));
        $mediaInteraction->setMinPlays(1);
        $mediaInteraction->setMaxPlays(1);
        
        $this->assertTrue($mediaInteraction->hasMinPlays());
        $this->assertTrue($mediaInteraction->hasMaxPlays());
    }
}
