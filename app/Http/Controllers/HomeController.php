<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Center\GridCenter;
use App\Model\Tracker;
use App\Transformer\UserTransformer;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['users','test']]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index(Request $request){
        return view('home', []);
    }

    public function home(){
        return redirect('/');
    }

    public function users()
    {
        $param = $_GET;
        $query = User::select();
        $user_data = new GridCenter($query, $param);
    
        echo json_encode($user_data->render(new UserTransformer()));
        exit;
    }

    public function test(){
        $list = Tracker::where('tracker_id', 7477)->orderBy('updated', 'ASC')->get();
        $list2 = [];
        $i = 0;
        $total_distance = 0;
        $total_duration = 0;
        foreach($list AS $v){
            if(!empty($v->latitude) && !empty($v->longitude)) {
                $distance = ($i==0) ? 0 : round($this->haversineGreatCircleDistance($list2[$i-1]->latitude, $list2[$i-1]->longitude, $v->latitude, $v->longitude, 6371000),2);
                $duration = ($i==0) ? 0 : round(abs(strtotime($v->updated) - strtotime($list2[$i-1]->updated)),2); 
                $list2[$i] = (object) [
                    'updated'   => $v->updated,
                    'status'    => $v->movement_status,
                    'latitude'  => $v->latitude,
                    'longitude' => $v->longitude,
                    'distance'  => $distance,
                    'duration'  => $duration,
                    'width'     => ($v->nozzle_kiri > 12.63 ? 18 : 0) + ($v->nozzle_kanan > 12.63 ? 18 : 0)
                ];
                $total_distance += $distance;
                $total_duration += $duration;
                echo "[".$v->updated_at."] Location: [".$v->latitude.",".$v->longitude."], Status: ".$v->movement_status.", Speed: ".$v->speed.", Duration: ".$duration." Detik, Distance: ".$distance." Meter, Width: ".$list2[$i]->width." Meter <br/>";
                $i++;
            }
        }
        echo "<br/>";
        echo "Total Duration: ".$total_duration." Detik = ".round($total_duration/60,2)." Menit = ".round($total_duration/3600,2)." Jam <br/>";
        echo "Total Distance: ".$total_distance." Meter = ".round($total_distance/1000,2)." KM <br/>";
        echo "Average Speed : ".round(($total_distance/1000)/($total_duration/3600),2)." KM/Jam <br/>";
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}
