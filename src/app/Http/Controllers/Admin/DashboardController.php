<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::where('role', 'registered')->count(),
            'total_events' => Event::count(),
            'upcoming_events' => Event::where('starts_at', '>', now())->count(),
            'total_tickets' => Ticket::where('status', 'paid')->count(),
            'total_revenue' => Ticket::where('status', 'paid')->sum('total_price'),
        ];

        $recentEvents = Event::with(['city', 'category'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentUsers = User::where('role', 'registered')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentEvents', 'recentUsers'));
    }
}
