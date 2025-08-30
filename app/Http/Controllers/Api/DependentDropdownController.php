<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Regency;
use App\Models\District;

class DependentDropdownController extends Controller
{
    public function getRegencies(Request $request)
    {
        $provinceId = $request->input('province_id');
        $regencies = Regency::where('province_id', $provinceId)->pluck('name', 'id');
        return response()->json($regencies);
    }

    public function getDistricts(Request $request)
    {
        $regencyId = $request->input('regency_id');
        $districts = District::where('regency_id', $regencyId)->pluck('name', 'id');
        return response()->json($districts);
    }
}