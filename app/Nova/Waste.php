<?php

namespace App\Nova;

use App\Nova\Filters\WasteBooleanFilter;
use App\Nova\Filters\WasteCollectionCenterFilter;
use App\Nova\Filters\WasteDeliveryFilter;
use App\Nova\Filters\WastePap;
use App\Nova\Filters\WasteTrashTypeFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Kongulov\NovaTabTranslatable\NovaTabTranslatable;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use Khalin\Nova4SearchableBelongsToFilter\NovaSearchableBelongsToFilter;
use Laravel\Nova\Query\Search\SearchableRelation;

class Waste extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Waste::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Get the searchable columns for the resource.
     *
     * @return array
     */
    public static function searchableColumns()
    {
        return [
            'id',
            'name',
            new SearchableRelation('trashType', 'name'),
        ];
    }
    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $allTrashTypeId = array();


        $selectedTrashTypeId = $this->model()->trash_type_id;
        return [
            ID::make()->sortable(),
            Boolean::make('pap'),
            Boolean::make('delivery'),
            Boolean::make('collection_center'),
            BelongsTo::make('trashType'),
            NovaTabTranslatable::make([
                Text::make('name')->sortable(),
                Textarea::make('where'),
                Textarea::make('notes')
            ]),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new WastePap,
            new WasteDeliveryFilter,
            new WasteCollectionCenterFilter,
            new WasteTrashTypeFilter,
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('company_id', $request->user()->company->id);
    }
}
