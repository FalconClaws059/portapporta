<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Zone;
use App\Models\Waste;
use App\Models\Calendar;
use App\Models\UserType;
use App\Models\TrashType;
use App\Models\CalendarItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\WasteCollectionCenter;
use App\Providers\CurlServiceProvider;

class ReaInstanceJsonSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pap:reasync {company_id : e.g. 3 (for rea)} {endpoint : e.g. http://apirea.webmapp.it/}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync a json file to its specific table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $company_id = $this->argument('company_id');
        $endpoint = $this->argument('endpoint');


        $this->syncTipiRifiuto($company_id, $endpoint);
        $this->syncUtenzeMeta($company_id, $endpoint);
        $this->syncCentriRaccolta($company_id, $endpoint);
        $this->syncRifiutario($company_id, $endpoint);
        $this->syncZoneMeta($company_id, $endpoint);
    }

    protected function syncTipiRifiuto($company_id, $endpoint)
    {

        // Curl request to get the feature information from external source
        $curl = app(CurlServiceProvider::class);
        $url = $endpoint . '/data/tipi_rifiuto.json';
        $track_obj = $curl->exec($url);
        $response = json_decode($track_obj, true);


        try {
            foreach ($response as $trash) {
                if (array_key_exists('name', $trash)) {
                    $params['name']['it'] = $trash['name'];
                }
                if (array_key_exists('description', $trash)) {
                    $params['description']['it'] = $trash['description'];
                }
                if (array_key_exists('howto', $trash)) {
                    $params['howto']['it'] = $trash['howto'];
                }
                if (array_key_exists('where', $trash)) {
                    $params['where']['it'] = $trash['where'];
                }
                if (array_key_exists('color', $trash)) {
                    $params['color']['it'] = $trash['color'];
                }
                if (array_key_exists('allowed', $trash)) {
                    $params['allowed']['it'] = $trash['allowed'];
                }
                if (array_key_exists('notallowed', $trash)) {
                    $params['notallowed']['it'] = $trash['notallowed'];
                }
                if (!empty($trash['translations'])) {
                    if (array_key_exists('name', $trash['translations'])) {
                        $params['name']['en'] = $trash['translations']['en']['name'];
                    }
                    if (array_key_exists('description', $trash['translations'])) {
                        $params['description']['en'] = $trash['translations']['en']['description'];
                    }
                    if (array_key_exists('howto', $trash['translations'])) {
                        $params['howto']['en'] = $trash['translations']['en']['howto'];
                    }
                    if (array_key_exists('where', $trash['translations'])) {
                        $params['where']['en'] = $trash['translations']['en']['where'];
                    }
                    if (array_key_exists('color', $trash['translations'])) {
                        $params['color']['en'] = $trash['translations']['en']['color'];
                    }
                    if (array_key_exists('allowed', $trash['translations'])) {
                        $params['allowed']['en'] = $trash['translations']['en']['allowed'];
                    }
                    if (array_key_exists('notallowed', $trash['translations'])) {
                        $params['notallowed']['en'] = $trash['translations']['en']['notallowed'];
                    }
                }
                TrashType::updateOrCreate(
                    [
                        'slug' => $trash['id'],
                        'company_id' => $company_id
                    ],
                    $params
                );
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    protected function syncUtenzeMeta($company_id, $endpoint)
    {

        // Curl request to get the feature information from external source
        $curl = app(CurlServiceProvider::class);
        $url = $endpoint . '/data/utenze_meta.json';
        $track_obj = $curl->exec($url);
        $response = json_decode($track_obj, true);

        try {
            foreach ($response as $usertype => $array) {
                if (array_key_exists('label', $array)) {
                    $params['label']['it'] = $array['label'];
                }
                if (!empty($array['translations'])) {
                    if (array_key_exists('en', $array['translations'])) {
                        $params['label']['en'] = $array['translations']['en']['label'];
                    }
                }
                UserType::updateOrCreate(
                    [
                        'slug' => $usertype,
                        'company_id' => $company_id
                    ],
                    $params
                );
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    protected function syncCentriRaccolta($company_id, $endpoint)
    {

        // Curl request to get the feature information from external source
        $curl = app(CurlServiceProvider::class);
        $url = $endpoint . '/data/centri_raccolta.geojson';
        $track_obj = $curl->exec($url);
        $response = json_decode($track_obj, true);

        try {
            foreach ($response['features'] as $feature) {
                if (array_key_exists('name', $feature['properties'])) {
                    $params['name']['it'] = $feature['properties']['name'];
                }
                if (array_key_exists('marker-color', $feature['properties'])) {
                    $params['marker-color'] = $feature['properties']['marker-color'];
                }
                if (array_key_exists('marker-size', $feature['properties'])) {
                    $params['marker-size'] = $feature['properties']['marker-size'];
                }
                if (array_key_exists('marker-symbol', $feature['properties'])) {
                    $params['marker-symbol'] = $feature['properties']['marker-symbol'];
                }
                if (array_key_exists('website', $feature['properties'])) {
                    $params['website'] = $feature['properties']['website'];
                }
                if (array_key_exists('picture_url', $feature['properties'])) {
                    $params['picture_url'] = $feature['properties']['picture_url'];
                }
                if (array_key_exists('orario', $feature['properties'])) {
                    $params['orario']['it'] = $feature['properties']['orario'];
                }
                if (array_key_exists('description', $feature['properties'])) {
                    $params['description']['it'] = $feature['properties']['description'];
                }

                if (!empty($feature['properties']['translations'])) {
                    if (array_key_exists('en', $feature['properties']['translations'])) {
                        $params['name']['en'] = $feature['properties']['translations']['en']['name'];
                        $params['orario']['en'] = $feature['properties']['translations']['en']['orario'];
                        $params['description']['en'] = $feature['properties']['translations']['en']['description'];
                    }
                }

                $lat = $feature['geometry']['coordinates'][0];
                $lng = $feature['geometry']['coordinates'][1];

                $waste_center = WasteCollectionCenter::updateOrCreate(
                    [
                        'geometry' => DB::select("SELECT ST_GeomFromText('POINT($lat $lng)') as g")[0]->g,
                        'company_id' => $company_id
                    ],
                    $params
                );

                // Relational Table: user_type_waste_collection_center 
                if (array_key_exists('userTypes', $feature['properties'])) {
                    $user_types = [];
                    foreach ($feature['properties']['userTypes'] as $value) {
                        $userType = UserType::where('company_id', $company_id)
                            ->where('slug', $value)->get();
                        array_push($user_types, $userType[0]->id);
                    };
                    $waste_center->userTypes()->sync($user_types, false);
                }

                // Relational Table: trash_type_waste_collection_center 
                if (array_key_exists('trashTypes', $feature['properties'])) {
                    $trash_types = [];
                    foreach ($feature['properties']['trashTypes'] as $value) {
                        $trashType = TrashType::where('company_id', $company_id)
                            ->where('slug', $value)->get();
                        array_push($trash_types, $trashType[0]->id);
                    };
                    $waste_center->trashTypes()->sync($trash_types, false);
                }
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    protected function syncRifiutario($company_id, $endpoint)
    {

        // Curl request to get the feature information from external source
        $curl = app(CurlServiceProvider::class);
        $url = $endpoint . '/data/rifiutario.json';
        $track_obj = $curl->exec($url);
        $response = json_decode($track_obj, true);


        try {
            foreach ($response as $waste) {
                if (array_key_exists('notes', $waste)) {
                    $params['notes']['it'] = $waste['notes'];
                }
                if (array_key_exists('where', $waste)) {
                    $params['where']['it'] = $waste['where'];
                }
                if (array_key_exists('pap', $waste)) {
                    $params['pap'] = $waste['pap'];
                }
                if (array_key_exists('collection_center', $waste)) {
                    $params['collection_center'] = $waste['collection_center'];
                }
                if (array_key_exists('delivery', $waste)) {
                    $params['delivery'] = $waste['delivery'];
                }
                if (!empty($waste['translations'])) {

                    if (array_key_exists('name', $waste['translations']['en'])) {
                        $params['name']['en'] = $waste['translations']['en']['name'];
                    }
                    if (array_key_exists('notes', $waste['translations']['en'])) {
                        $params['notes']['en'] = $waste['translations']['en']['notes'];
                    }
                    if (array_key_exists('where', $waste['translations']['en'])) {
                        $params['where']['en'] = $waste['translations']['en']['where'];
                    }
                }
                if (array_key_exists('category', $waste)) {
                    try {
                        $trashType = TrashType::where('company_id', $company_id)
                            ->where('slug', $waste['category'])->get();
                        if (count($trashType) > 0) {
                            $params['trash_type_id'] = $trashType[0]->id;
                        }
                    } catch (Exception $e) {
                        Log::error('TrashType relation not found: ' . json_encode($waste) . ' ' . $e->getMessage());
                        continue;
                    }
                }
                Waste::updateOrCreate(
                    [
                        'name' => [
                            'it' => $waste['name'],
                        ],
                        'company_id' => $company_id
                    ],
                    $params
                );
            }
        } catch (Exception $e) {
            Log::error('Caught exception syncRifiutario: ' . $waste['name'] . ' ' .  $e->getMessage());
        }
    }

    protected function syncZoneMeta($company_id, $endpoint)
    {

        // Curl request to get the feature geometry
        $curl = app(CurlServiceProvider::class);
        $url = $endpoint . '/data/zone_confini.geojson';
        $track_obj = $curl->exec($url);
        $response = json_decode($track_obj, true);

        $coordinate_array = [];
        try {
            foreach ($response['features'] as $zone) {
                $coordinate_array[$zone['properties']['id']] = array(
                    "type" => "MultiPolygon",
                    "coordinates" => $zone['geometry']['coordinates']
                );
            }
        } catch (Exception $e) {
            Log::error('Caught exception syncZoneConfini: ' . json_encode($zone) . ' ' .  $e->getMessage());
        }

        // Curl request to get the feature information from external source
        $curl = app(CurlServiceProvider::class);
        $url = $endpoint . '/data/zone_meta.json';
        $track_obj = $curl->exec($url);
        $response = json_decode($track_obj, true);

        try {
            foreach ($response as $zone) {
                if (array_key_exists('comune', $zone)) {
                    $params['comune'] = $zone['comune'];
                }
                if (array_key_exists('url', $zone)) {
                    $params['url'] = $zone['url'];
                }
                if (array_key_exists($zone['id'], $coordinate_array)) {
                    $params['geometry'] = DB::select("SELECT ST_AsText(ST_GeomFromGeoJSON('" . json_encode($coordinate_array[$zone['id']]) . ",4326')) As wkt")[0]->wkt;
                }
                $params['company_id'] = $company_id;

                $zone_obg = Zone::updateOrCreate(
                    [
                        'label' =>  $zone['label'],
                        'company_id' => $company_id
                    ],
                    $params
                );

                if (array_key_exists('types', $zone)) {
                    $zones = [];
                    foreach ($zone['types'] as $z) {
                        $userType = UserType::where('company_id', $company_id)
                            ->where('slug', $z)->get();
                        array_push($zones, $userType[0]->id);
                    };
                    $zone_obg->userTypes()->sync($zones, false);
                }

                if (count($zone_obg->userTypes) > 0) {
                    foreach ($zone_obg->userTypes as $userType) {
                        $this->syncCalendario($userType->slug, $endpoint, $userType, $zone_obg, $company_id);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Caught exception syncZoneMeta: ' . json_encode($zone) . ' ' .  $e->getMessage());
        }
    }

    protected function syncCalendario($slug, $endpoint, $userType, $zone_obg, $company_id)
    {
        // Curl request to get the feature information from external source
        $curl = app(CurlServiceProvider::class);
        $url = $endpoint . '/data/calendar_' . $slug . '_input.json';
        $obj = $curl->exec($url);
        $response = json_decode($obj, true);
        $calendarItems = [];

        $calendarName = $zone_obg->label . ' (' . $slug . ')';
        $params = [
            'zone_id' => $zone_obg->id,
            'user_type_id' => $userType->id,
            'company_id' => $company_id,

        ];

        $this->info('calendar: ' . $url . ' for zone: ' . $zone_obg->label . PHP_EOL);

        foreach ($response as $items) {
            foreach ($items as $calendar) {
                if (array_key_exists('start', $calendar)) {
                    //convert string to date
                    $params['start_date'] =  $this->setStartDate($calendar);
                };
                if (array_key_exists('end', $calendar)) {
                    //convert string to date
                    $params['stop_date'] =  $this->setStopDate($calendar);
                };
                if (array_key_exists('calendars', $calendar)) {
                    foreach ($calendar['calendars'] as $calendarItem) {
                        $calendarItems[] = $calendarItem;
                    }
                }
            }
            $syncedCalendar = Calendar::updateOrCreate([
                'name' => $calendarName,

            ], $params);
            foreach ($calendarItems as $calendarItem) {
                $this->syncCalendarioItem($calendarItem, $syncedCalendar);
            }
        }
    }
    protected function syncCalendarioItem($calendarItem, $syncedCalendar)
    {
        $item = [];
        $trashTypes = [];
        $frequency = 'weekly';
        $servicesByDay = [];

        if (array_key_exists('start', $calendarItem)) {
            $startTime = $calendarItem['start'];
        }
        if (array_key_exists('end', $calendarItem)) {
            $stopTime = $calendarItem['end'];
        }

        foreach ($calendarItem['services'] as $service => $serviceData) {

            //if the ['days'] key does not exist, and instead a ['baseDate'] and ['frequency'] keys are found, 
            if (array_key_exists('baseDate', $serviceData) && array_key_exists('frequency', $serviceData)) {

                $services[] = $service;
                $dateDay = $this->handleBaseDate($serviceData['baseDate']);

                //set the 'frequency' to biweekly if the ['frequency'] value is '14'
                if ($serviceData['frequency'] == 14) {
                    $frequency = 'biweekly';
                }

                foreach ($serviceData as $day) {
                    $item = [
                        'calendar_id' => $syncedCalendar->id,
                        'start_time' => $startTime,
                        'stop_time' => $stopTime,
                        'day_of_week' => $dateDay,
                        'frequency' => $frequency,
                    ];
                    array_push($item['services'], $service);

                    //get the trashtypes from the $item['services']
                    $trashTypes = $this->getTrashTypes($services, $syncedCalendar);

                    $newCalendarItem = CalendarItem::updateOrCreate(['day_of_week' => $dateDay], $item);
                    //attach every trashtype in the array to the newcalendaritem
                    $newCalendarItem->trashTypes()->sync(collect($trashTypes)->pluck('id')->toArray());
                }
            }
            if (array_key_exists('days', $serviceData)) {
                foreach ($serviceData['days'] as $day) {
                    if ($day == 7) {
                        $day = 0;
                    }
                    if (!isset($servicesByDay[$day])) {
                        $servicesByDay[$day] = [];
                    }
                    $servicesByDay[$day][] = $service;
                }
            }
        }
        foreach ($servicesByDay as $dayOfWeek => $services) {
            //create a new calendar item for each day of the week and assign the services to it

            $item = [
                'calendar_id' => $syncedCalendar->id,
                'start_time' => $startTime,
                'stop_time' => $stopTime,
                'day_of_week' => $dayOfWeek,
                'frequency' => $frequency,
            ];

            $newCalendarItem = CalendarItem::updateOrCreate(['day_of_week' => $dayOfWeek], $item);

            $trashTypes = $this->getTrashTypes($services, $syncedCalendar);

            $newCalendarItem->trashTypes()->sync(collect($trashTypes)->pluck('id')->toArray());
        }

        //reset the servicesByDay array
        $servicesByDay = [];
    }


    protected function setStartDate($calendar)
    {
        $dateString = $calendar['start'];
        $currentYear = Carbon::now()->year;
        $dateStringWithYear = $currentYear . '-' . $dateString;
        $date = Carbon::createFromFormat('Y-m-d', $dateStringWithYear)->format('Y-m-d');
        return $date;
    }

    protected function setStopDate($calendar)
    {
        $dateString = $calendar['end'];
        $currentYear = Carbon::now()->year;
        $dateStringWithYear = $currentYear . '-' . $dateString;
        $date = Carbon::createFromFormat('Y-m-d', $dateStringWithYear)->format('Y-m-d');
        return $date;
    }

    protected function getTrashTypes($services, $syncedCalendar)
    {
        $trashTypes = [];
        foreach ($services as $service) {
            $trashType = TrashType::where('company_id', $syncedCalendar->company_id)
                ->where('slug', $service)->get();
            $trashTypes[] = $trashType[0];
        }
        return $trashTypes;
    }

    protected function handleBaseDate($baseDate)
    {
        $dateDay = Carbon::createFromFormat('Y-m-d', $baseDate)->format('l');
        $dateDay = Carbon::parse($dateDay)->dayOfWeekIso;
        if ($dateDay == 7) {
            $dateDay = 0;
        }

        return $dateDay;
    }
}
