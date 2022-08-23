<?php

namespace Wm\MapPoi;

use Laravel\Nova\Fields\Field;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;

class MapPoi extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'map-poi';

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request,
                                                $requestAttribute,
                                                $model,
                                                $attribute)
    {
        if ($request->exists($requestAttribute)) {
            $model->{$attribute} = $request[$requestAttribute];
        }
    }
}
