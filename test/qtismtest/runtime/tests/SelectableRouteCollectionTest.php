<?php

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\SelectableRoute;
use qtism\runtime\tests\SelectableRouteCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class SelectableRouteCollectionTest
 */
class SelectableRouteCollectionTest extends QtiSmTestCase
{
    public function testInsertAt(): void
    {
        $routeA = new SelectableRoute();
        $routeB = new SelectableRoute();
        $routeC = new SelectableRoute();

        $routes = new SelectableRouteCollection([$routeA, $routeB, $routeC]);

        $this::assertSame($routeA, $routes[0]);
        $this::assertSame($routeB, $routes[1]);
        $this::assertSame($routeC, $routes[2]);

        $routeAlpha = new SelectableRoute();
        $routes->insertAt($routeAlpha, 0);

        $this::assertSame($routeAlpha, $routes[0]);
        $this::assertSame($routeA, $routes[1]);
        $this::assertSame($routeB, $routes[2]);
        $this::assertSame($routeC, $routes[3]);

        $routeOmega = new SelectableRoute();
        $routes->insertAt($routeOmega, 4);

        $this::assertSame($routeAlpha, $routes[0]);
        $this::assertSame($routeA, $routes[1]);
        $this::assertSame($routeB, $routes[2]);
        $this::assertSame($routeC, $routes[3]);
        $this::assertSame($routeOmega, $routes[4]);

        $routeGamma = new SelectableRoute();
        $routes->insertAt($routeGamma, 2);
        $this::assertSame($routeAlpha, $routes[0]);
        $this::assertSame($routeA, $routes[1]);
        $this::assertSame($routeGamma, $routes[2]);
        $this::assertSame($routeB, $routes[3]);
        $this::assertSame($routeC, $routes[4]);
        $this::assertSame($routeOmega, $routes[5]);
    }
}
