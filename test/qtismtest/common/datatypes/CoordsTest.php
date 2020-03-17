<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiShape;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtismtest\QtiSmTestCase;

class CoordsTest extends QtiSmTestCase
{
    public function testInstantiate()
    {
        $coords = new QtiCoords(QtiShape::POLY, [0, 0, 0, 3, 3, 0]);
        $this->assertEquals(BaseType::COORDS, $coords->getBaseType());
        $this->assertEquals(Cardinality::SINGLE, $coords->getCardinality());
    }

    public function testInsideCircle()
    {
        $coords = new QtiCoords(QtiShape::CIRCLE, [5, 5, 5]);

        $point = new QtiPoint(1, 1); // 1,1 is outside
        $this->assertFalse($coords->inside($point));

        $point = new QtiPoint(3, 3); // 3,3 is inside
        $this->assertTrue($coords->inside($point));

        $point = new QtiPoint(5, 5); // 5,5 is inside
        $this->assertTrue($coords->inside($point));

        $point = new QtiPoint(10, 10); // 10,10 is outside
        $this->assertFalse($coords->inside($point));
    }

    public function testInsideRectangle()
    {
        // Do not forget (x1, y1) -> left top corner, (x2, y2) -> right bottom corner.
        $coords = new QtiCoords(QtiShape::RECT, [0, 0, 5, 3]);

        $point = new QtiPoint(0, 0); // 0, 0 is inside.
        $this->assertTrue($coords->inside($point));

        $point = new QtiPoint(-1, -1); // -1, -1 is outside.
        $this->assertFalse($coords->inside($point));

        $point = new QtiPoint(2, 1); // 2, 1 is inside.
        $this->assertTrue($coords->inside($point));

        $point = new QtiPoint(5, 3); // 5, 3 is inside.
        $this->assertTrue($coords->inside($point));

        $point = new QtiPoint(5, 4); // 5, 4 is outside.
        $this->assertFalse($coords->inside($point));
    }

    public function testInsidePolygon()
    {
        $coords = new QtiCoords(QtiShape::POLY, [0, 8, 7, 4, 2, 2, 8, -4, -2, 1]);

        $point = new QtiPoint(0, 8); // 0, 8 is inside.
        $this->assertTrue($coords->inside($point));

        $point = new QtiPoint(10, 9); // 10, 9 is outside.
        $this->assertFalse($coords->inside($point));

        $point = new QtiPoint(3, 2); // 3, 2 is outside.
        $this->assertFalse($coords->inside($point));

        $point = new QtiPoint(1, 2); // 1, 2 is inside;
        $this->assertTrue($coords->inside($point));

        $point = new QtiPoint(-1, -1); // -1, -1 is outside.
        $this->assertFalse($coords->inside($point));

        $point = new QtiPoint(6, 4); // 6, 4 is inside.
        $this->assertTrue($coords->inside($point));

        $point = new QtiPoint(-3, 6); // -3, 6 is outside, and the horizontal line intersects the edge between the first and the last vertices.
        $this->assertFalse($coords->inside($point));
    }

    public function testOnEdgePolygon()
    {
        $coords = new QtiCoords(QtiShape::POLY, [0, 0, 0, 3, 3, 0]);
        $point = new QtiPoint(0, 2);
        $this->assertTrue($coords->inside($point));
    }

    public function testInsideDefault()
    {
        // always true.
        $coords = new QtiCoords(QtiShape::DEF);
        $this->assertTrue($coords->inside(new QtiPoint(0, 0)));
        $this->assertTrue($coords->inside(new QtiPoint(100, 200)));
        $this->assertTrue($coords->inside(new QtiPoint(-200, -100)));
    }
}
