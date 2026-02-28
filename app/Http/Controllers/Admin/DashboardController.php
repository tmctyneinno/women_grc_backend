<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\EventBooking;
use App\Models\Course;

class DashboardController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:admin');
    // }
 
    // public function index()
    // {
    //     return view('admin.dashboard');


    // }



    public function index()
    {
        $totalUsers = User::count();
        $pendingUsers = User::where('status', 'pending')->count();
        $blockedUsers = User::where('status', 'blocked')->count();

        $totalEvents = Event::count();
        $upcomingEvents = Event::where('start_date', '>=', now())->count();

        $totalBookings = EventBooking::count();
        $confirmedBookings = EventBooking::where('status', 'confirmed')->count();

        $totalCourses = Course::count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'pendingUsers',
            'blockedUsers',
            'totalEvents',
            'upcomingEvents',
            'totalBookings',
            'confirmedBookings',
            'totalCourses'
        ));
    }

}