<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    public function myGiving(Request $request)
    {
        $userId = Auth::id();
        
        $query = Donation::with('fund')->where('user_id', $userId)->latest('donation_date')->latest('id');

        if ($request->has('year') && $request->year) {
            $query->whereYear('donation_date', $request->year);
        } else {
            // Default to current year if no filter applied
            $query->whereYear('donation_date', date('Y'));
        }

        $donations = $query->paginate(20)->withQueryString();

        // Tính tổng tiền theo năm
        $yearlyTotal = Donation::where('user_id', $userId)
            ->whereYear('donation_date', $request->has('year') ? $request->year : date('Y'))
            ->sum('amount');
            
        // Các năm có dữ liệu (để render filter)
        $availableYears = Donation::where('user_id', $userId)
            ->selectRaw('YEAR(donation_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
            
        // Nếu chửa có năm nào thì mặc định vẫn có năm hiện tại
        if(count($availableYears) === 0) {
            $availableYears = [date('Y')];
        }

        return Inertia::render('User/Donations/Index', [
            'donations' => $donations,
            'yearlyTotal' => $yearlyTotal,
            'availableYears' => $availableYears,
            'currentYear' => $request->has('year') ? $request->year : date('Y')
        ]);
    }
}
