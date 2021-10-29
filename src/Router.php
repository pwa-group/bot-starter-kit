<?php

namespace App;

use App\Controllers\Index;
use ArrayIterator;

final class Router
{
    /**
     * @var ArrayIterator<Route>
     */
    private ArrayIterator $routes;

    /**
     * Router constructor.
     * @param $routes array<Route>
     */
    public function __construct(array $routes = [])
    {
        $this->routes = new ArrayIterator();
        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    public function add(Route $route): self
    {
        $this->routes->offsetSet($route->getPath(), $route);
        return $this;
    }

    public function match(string $data): Route
    {
        return $this->matchFromPath($data);
    }

    public function matchFromPath(string $path): Route
    {
        foreach ($this->routes as $route) {
            if ($route->match($path) === false) {
                continue;
            }
            return $route;
        }

        return new Route('/', [Index::class]);
    }
}
