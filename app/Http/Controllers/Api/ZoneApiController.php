<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\Zone;
use App\Models\Request\Request as RequestRequest;
use App\Models\Request\Request;
use stdClass;

class ZoneApiController extends Controller
{
    public function zoneWiseDriver(Request $request)
    {
        $request->validate([
            'zone_id' => 'required'
        ]);

        $zones = Zone::select('id', 'name', 'service_location_id')->with('driver')
            ->find($request->zone_id);
        $driver_count = 0;
        $driver_details = [];
        foreach ($zones->driver as $driver) {
            $data = [];
            if ($driver->active == 1) {
                $driver_count += 1;
                $driver_detail = new stdClass();
                $driver_detail->driver_zone_name = $zones->name;
                $driver_detail->driver_name = $driver->name;
                $driver_detail->driver_car_name = $driver->car_model_name;
                $driver_details[] = $driver_detail;
            }
        }
        return response()->json(['success' => true, 'data' => ['driver_detail', $driver_details], 'driver_count' => $driver_count]);
    }

    public function getZoneName()
    {
        $zone_name = Zone::select('id', 'name')->get();
        return response()->json(['success' => true, 'data' => ['zone_name', $zone_name], 200]);
    }

    public function tripAcceptedByDriver(Request $request)
    {
        $request->validate([]);
    }

    public function zoneMap(Request $request)
    {
        $request->validate([
            'zone_id' => 'required'
        ]);

        $zones = Zone::select('id', 'name', 'service_location_id', 'coordinates', 'lat', 'lng')->with('driver')->find($request->zone_id);
        $on_trips = [];
        $availables = [];
        foreach ($zones->driver as $driver) {
            if ($driver->active == 1 && $driver->available == 0) {
                $on_trip = new StdClass();
                $on_trip->driver_zone_name = $zones->name;
                // $on_trip->coordinates = $zones->coordinates;
                $on_trip->latitude = $zones->lat;
                $on_trip->longitude = $zones->lng;
                $on_trip->driver_name = $driver->name;
                $on_trip->driver_number = $driver->mobile;
                $on_trip->driver_car_number = $driver->car_number;
                $on_trips[] = $on_trip;
            } else {
                if ($driver->active == 1 && $driver->available == 1) {
                    $available = new StdClass();
                    $available->driver_zone_name = $zones->name;
                    // $available->coordinates = $zones->coordinates;
                    $available->latitude = $zones->lat;
                    $available->longitude = $zones->lng;
                    $available->driver_name = $driver->name;
                    $available->driver_number = $driver->mobile;
                    $available->driver_car_number = $driver->car_number;
                    $availables[] = $available;
                }
            }
        }
        return response()->json(['success' =>  true, 'data' => ['ontrip_drivers' => $on_trips, 'available_drivers' => $availables], 200]);
    }


    public function acceptedTripDriver()
    {
        $requests = Request::with('driverDetail')->with('zoneType.zone')->select('id', 'driver_id', 'zone_type_id', 'accepted_at', 'is_completed', 'is_cancelled')->whereNotNull('accepted_at')->where([['is_completed', 0], ['is_cancelled', 0]])->get();
        $request_datas = [];
        foreach ($requests as $request) {

            $request_data = new StdClass();
            $request_data->driver_name = $request->driverDetail->name;
            $request_data->zone_name  = $request->zone_name;
            array_push($request_datas,$request_data);
        }
        return response()->json(['success' => true, 'data' => ['request' => $request_datas]], 200);
    }
}
