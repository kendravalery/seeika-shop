<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;

class CustomerController extends Controller
{
    public function showAccount()
    {
        return view('customer.account.my_account');
    }

    public function showAddress()
    {
        $user = Auth::user();
        $provinces = Province::all();
        $addresses = Address::where('user_id', $user->id)->get();

        $provinceNames = Province::whereIn('code', $addresses->pluck('province')->filter()->unique())->pluck('name', 'code');

        $cityNames = City::whereIn('code', $addresses->pluck('city')->filter()->unique())->pluck('name', 'code');

        $districtNames = District::whereIn('code', $addresses->pluck('district')->filter()->unique())->pluck('name', 'code');

        $villageNames = Village::whereIn('code', $addresses->pluck('village')->filter()->unique())->pluck('name', 'code');

        return view('customer.account.address', compact('addresses', 'provinces', 'provinceNames', 'cityNames', 'districtNames', 'villageNames'));
    }

    public function updateAddress()
    {
        return back();
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['profile_photo' => 'required|mimes:png,jpg,png,webp']);

        $user = Auth::user();

        // delete photo if present
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('profile_photo')->store('profile', 'public');
        $user->update([
            'profile_photo' => $path,
        ]);
        return back()->with('success', 'Foto berhasil diupdate');
    }
    public function manageAccount()
    {
        return view('customer.account.manage_account');
    }

    public function updateManageAccount(Request $request)
    {
        $request->validate(['name' => 'required|string|max:70', 'username' => 'nullable|string|max:40|unique:users,username,' . auth()->id(), 'phone' => 'nullable|string|max:20']);
        $user = Auth::user();

        $user->update(['name' => $request->name, 'username' => $request->username, 'phone' => $request->phone]);
        return back()->with('success', 'Profile updated successfully');
    }

    // provinsi
    public function getCities($provinceCode)
    {
        return City::where('province_code', $provinceCode)->get();
    }
    // kecematan
    public function getDistricts($cityCodes)
    {
        return District::where('city_code', $cityCodes)->get();
    }
    // Desa
    public function getVillages($districtCode)
    {
        return Village::where('district_code', $districtCode)->get();
    }

    public function updateManageAddress(Request $request, $id)
    {
        $validated = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'full_address' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'village' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
        ]);

        // Find the address belonging to the authenticated user.
        $address = Address::where('user_id', Auth::id())->findOrFail($id);

        $address->update($validated);

        return back()->with('success', 'Address updated successfully');
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'full_address' => 'required|string',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'village' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
        ]);
        $user = Auth::user();

        Address::create([
            'user_id' => $user->id,
            'label' => null,
            'receiver_name' => $request->receiver_name,
            'phone' => $request->phone,

            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'village' => $request->village,

            'postal_code' => $request->postal_code,
            'full_address' => $request->full_address,
            'is_default' => false,
        ]);
        return back();
    }

    public function deleteAddress($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);

        $address->delete();

        return redirect()->route('customer.address');
    }

    public function defaultAddress($id)
    {
        $userId = Auth::id();

        $address = Address::where('user_id', $userId)->findOrFail($id);

        Address::where('user_id', $userId)->update([
            'is_default' => false,
        ]);

        $address->update([
            'is_default' => true,
        ]);

        return back();
    }
}
