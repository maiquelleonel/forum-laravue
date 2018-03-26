<?php

namespace App\Core\Routing;
use Illuminate\Routing\Route;
use InvalidArgumentException;
use Illuminate\Routing\UrlGenerator as CoreUrlGenerator;
use Illuminate\Routing\RouteCollection;
use Illuminate\Http\Request;

class UrlGenerator extends CoreUrlGenerator
{
    /**
     * Get the URL to a named route.
     *
     * @param  string  $name
     * @param  mixed   $parameters
     * @param  bool  $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        if (! is_null($route = $this->routes->getByName($name))) {
            if(config("routing.version_prefix") && str_contains($route->getPrefix(), config("routing.version_prefix"))) {
                $parameters = $this->mergeParameters($parameters);
            }
            return $this->toRoute($route, $parameters, $absolute);
        }
        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }

    /**
     * Merge user parameters with subdomain parameter
     *
     * @param array|string $parameters
     * @return array array of parameters
     */
    private function mergeParameters($parameters = [])
    {
        if(!is_array($parameters)) {
            $parameters = [$parameters];
        }

        if ($version = $this->getVersionParameter()) {
            return array_merge([$version], $parameters);
        }

        return $parameters;
    }

    /**
     * Get the version parameter value
     *
     * @return string|null subdomain parameter value
     */
    private function getVersionParameter()
    {
        $version = $this->request->route('version');
        return ($version && (str_is("v*", $version) OR is_numeric($version)))
                ? $version
                : "/";
    }
}