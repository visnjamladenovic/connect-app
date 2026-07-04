<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\City;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::with(['city', 'category'])
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->take(6)
            ->get();

        $cities = City::all();
        $categories = Category::all();

        return view('home', compact('events', 'cities', 'categories'));
    }

    public function dashboard()
    {
        $user = auth()->user();

        $upcomingTickets = $user->tickets()
            ->with(['event.city', 'event.category'])
            ->whereHas('event', fn($q) => $q->where('starts_at', '>', now()))
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recommendations = $user->interests()
            ->with('category')
            ->get()
            ->pluck('category_id');

        $recommendedEvents = Event::with(['city', 'category'])
            ->whereIn('category_id', $recommendations)
            ->where('starts_at', '>', now())
            ->whereNotIn('id', $user->tickets()->pluck('event_id'))
            ->take(4)
            ->get();

        return view('dashboard', compact('upcomingTickets', 'recommendedEvents'));
    }
}
