<?php

// Taken from https://github.com/knuckleswtf/scribe/issues/594#issuecomment-2024396761

namespace App\Utils;

use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;
use Illuminate\Routing\Route;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\Shared\UrlParamsNormalizer;
use Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromFormRequest as BaseGetFromFormRequest;
use Knuckles\Scribe\Tools\Globals;

class GetFromFormRequest extends BaseGetFromFormRequest
{
    public function __invoke(ExtractedEndpointData $endpointData, array $routeRules = []): ?array
    {
        return $this->getParametersFromFormRequestWithBinding($endpointData, $endpointData->route);
    }

    /**
     * Overriding Knuckles\Scribe\Extracting\Strategies\GetFromFormRequestBase::getParametersFromFormRequest
     * to bind the request to the route and set the route parameters properly.
     */
    public function getParametersFromFormRequestWithBinding(ExtractedEndpointData $endpointData, Route $route): array
    {
        if (! $formRequestReflectionClass = $this->getFormRequestReflectionClass($endpointData->method)) {
            return [];
        }

        if (! $this->isFormRequestMeantForThisStrategy($formRequestReflectionClass)) {
            return [];
        }

        $className = $formRequestReflectionClass->getName();

        if (Globals::$__instantiateFormRequestUsing) {
            $formRequest = call_user_func_array(Globals::$__instantiateFormRequestUsing, [$className, $route, $endpointData->method]);
        } else {
            $formRequest = new $className;
        }

        // Set the route properly, so it works for users who have code that checks for the route.
        /** @var LaravelFormRequest $formRequest */
        $formRequest->setRouteResolver(function () use ($formRequest, $route, $endpointData) {
            // Also need to bind the request to the route in case their code tries to inspect current request
            $route = $route->bind($formRequest);

            // ADDING THIS LINE TO SET ROUTE PARAMETERS
            return $this->setRouteParametersAfterBinding($endpointData, $route);
        });

        $formRequest->server->set('REQUEST_METHOD', $route->methods()[0]);

        $parametersFromFormRequest = $this->getParametersFromValidationRules(
            $this->getRouteValidationRules($formRequest),
            $this->getCustomParameterData($formRequest)
        );

        return $this->normaliseArrayAndObjectParameters($parametersFromFormRequest);
    }

    /**
     * Dynamically sets route parameters for a given route based on URL parameters and method signatures.
     * It assigns parameters using the first instance of type-hinted Eloquent models, the first case of enums,
     * or a default example value.
     *
     * Note: Assumes model instances can be fetched with first() and enums are instantiated from their first case.
     */
    protected function setRouteParametersAfterBinding(ExtractedEndpointData $endpointData, Route $route): Route
    {
        $typeHintedEloquentModels = UrlParamsNormalizer::getTypeHintedEloquentModels($endpointData->method);
        $typeHintedEnums = UrlParamsNormalizer::getTypeHintedEnums($endpointData->method);

        foreach ($endpointData->urlParameters as $urlParameter) {
            $paramName = $urlParameter->name;

            $routeParameter = match (true) {
                array_key_exists($paramName, $typeHintedEloquentModels) => $typeHintedEloquentModels[$paramName]::first(),
                array_key_exists($paramName, $typeHintedEnums) => $typeHintedEnums[$paramName]->getCases()[0]?->getValue(),
                default => $urlParameter->example,
            };

            $route->setParameter($paramName, $routeParameter);
        }

        return $route;
    }
}
