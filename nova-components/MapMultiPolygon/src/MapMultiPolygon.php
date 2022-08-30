<?php

namespace Wm\MapMultiPolygon;

use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class MapMultiPolygon extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'map-multi-polygon';
    public $zone = [];

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        parent::resolve($resource, $attribute = null);
        $this->zone = $this->geometryToArea($this->value);
        $this->withMeta(['area' => $this->zone['area']]);
        $this->withMeta(['center' => $this->zone['center']]);
    }
    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        if ($request->exists($requestAttribute)) {
            $lonLat = explode(',', $request[$requestAttribute]);
            $model->{$attribute} = $this->latLonToGeometry($lonLat);
        }
    }

    public function geometryToArea($geometry)
    {
        $coords = [];
        if (!is_null($geometry)) {
            $g = json_decode(DB::select("SELECT st_asgeojson('$geometry') as g")[0]->g);
            $c = json_decode(DB::select("SELECT st_asgeojson(ST_Centroid('$geometry')) as g")[0]->g);
            $coords_latlon = $g->coordinates[0][0];
            $coords_latlon = array_map(function($coord){
                return [$coord[1], $coord[0]];
            },$coords_latlon);
            $coords['area'] = $coords_latlon;
            // g->coordinates == [lon,lat] we needs inverted order
            $coords['center'] = [$c->coordinates[1], $c->coordinates[0]];
        }
        return $coords;
    }

    public function latLonToGeometry($latlon)
    {
        $lat = $latlon[0];
        $lon = $latlon[1];
        return DB::select("SELECT ST_GeomFromText('POINT($lon $lat)') as g")[0]->g;
    }
}
