<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportMapController extends Controller
{
    public function index(Request $req)
    {
        $q = Report::query()->withCoords();

        if ($req->filled('status')) $q->where('status', $req->string('status'));
        if ($req->filled('category_id')) $q->where('category_id', $req->integer('category_id'));

        if ($req->filled('nelat')) {
            $ne = ['lat'=>$req->float('nelat'), 'lng'=>$req->float('nelng')];
            $sw = ['lat'=>$req->float('swlat'), 'lng'=>$req->float('swlng')];
            $q->inBounds($ne, $sw);
        }

        $points = $q->latest()->limit(5000)->get(['id','latitude','longitude','status','category_id','created_at']);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $points->map(fn($r) => [
                'type' => 'Feature',
                'geometry' => ['type'=>'Point','coordinates'=>[(float)$r->longitude,(float)$r->latitude]],
                'properties' => [
                    'id'=>$r->id,
                    'status'=>$r->status,
                    'category_id'=>$r->category_id,
                    'created_at'=>$r->created_at->toIso8601String(),
                ]
            ])
        ]);
    }
}
