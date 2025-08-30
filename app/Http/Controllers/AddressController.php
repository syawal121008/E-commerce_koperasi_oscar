<?php
namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Province;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses()->latest()->get();
        return view('profile.addresses.index', compact('addresses'));
    }

    public function create()
    {
        $provinces = Province::pluck('name', 'id');
        return view('profile.addresses.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'address_label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'province_id' => 'required|exists:provinces,id',
            'regency_id' => 'required|exists:regencies,id',
            'district_id' => 'required|exists:districts,id', // <-- PERBAIKAN 1
            'full_address' => 'required|string',
            'postal_code' => 'nullable|string|max:5',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        Auth::user()->addresses()->create($request->all());

        return redirect()->route('addresses.index')->with('paid', 'Alamat berhasil ditambahkan.');
    }
}