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
    public function testInsertAt()
    {
        $routeA = new SelectableRoute();
        $routeB = new SelectableRoute();
        $routeC = new SelectableRoute();

        $routes = new SelectableRouteCollection([$routeA, $routeB, $routeC]);

        $this::assertTrue($routes[0] === $routeA);
        $this::assertTrue($routes[1] === $routeB);
        $this::assertTrue($routes[2] === $routeC);

        $routeAlpha = new SelectableRoute();
        $routes->insertAt($routeAlpha, 0);

        $this::assertTrue($routes[0] === $routeAlpha);
        $this::assertTrue($routes[1] === $routeA);
        $this::assertTrue($routes[2] === $routeB);
        $this::assertTrue($routes[3] === $routeC);

        $routeOmega = new SelectableRoute();
        $routes->insertAt($routeOmega, 4);

        $this::assertTrue($routes[0] === $routeAlpha);
        $this::assertTrue($routes[1] === $routeA);
        $this::assertTrue($routes[2] === $routeB);
        $this::assertTrue($routes[3] === $routeC);
        $this::asserttrue($routes[4] === $routeOmega);

        $routeGamma = new SelectableRoute();
        $routes->insertAt($routeGamma, 2);
        $this::assertTrue($routes[0] === $routeAlpha);
        $this::assertTrue($routes[1] === $routeA);
        $this::assertTrue($routes[2] === $routeGamma);
        $this::assertTrue($routes[3] === $routeB);
        $this::asserttrue($routes[4] === $routeC);
        $this::asserttrue($routes[5] === $routeOmega);
    }
}
