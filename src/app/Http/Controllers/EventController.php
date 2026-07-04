<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\City;
use App\Models\Category;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['city', 'category'])
            ->where('starts_at', '>', now());

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('city')) {
            $query->where('city_id', $request->city);
        }

        if ($request->filled('date')) {
            $query->whereDate('starts_at', $request->date);
        }

        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        $events = $query->orderBy('starts_at')->paginate(12);
        $cities = City::all();
        $categories = Category::all();

        return view('events.index', compact('events', 'cities', 'categories'));
    }

    public function show(Event $event)
    {
        $event->load(['city', 'category', 'reviews.user']);
        $avgRating = $event->reviews()->avg('rating');

        return view('events.show', compact('event', 'avgRating'));
    }

    public function create()
    {
        $cities = City::all();
        $categories = Category::all();

        return view('admin.events.create', compact('cities', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'starts_at' => 'required|date|after:now',
            'ticket_price' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1',
        ]);

        $validated['available_seats'] = $validated['total_seats'];
        $validated['created_by'] = auth()->id();

        Event::create($validated);

        return redirect()->route('admin.events.create')
            ->with('success', 'Događaj je uspešno dodat.');
    }

    public function edit(Event $event)
    {
        $cities = City::all();
        $categories = Category::all();

        return view('admin.events.edit', compact('event', 'cities', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'starts_at' => 'required|date',
            'ticket_price' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1',
        ]);

        $event->update($validated);

        return redirect()->route('admin.events.edit', $event)
            ->with('success', 'Događaj je uspešno izmenjen.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Događaj je obrisan.');
    }
}
