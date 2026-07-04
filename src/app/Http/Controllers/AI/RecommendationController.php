<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Event;

class RecommendationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Kategorije iz interesovanja korisnika
        $interestCategoryIds = $user->interests()->pluck('category_id');

        // Kategorije iz istorije kupljenih karata
        $ticketCategoryIds = $user->tickets()
            ->with('event')
            ->get()
            ->pluck('event.category_id');

        // Spajamo interesovanja i istoriju (union, bez duplikata)
        $allCategoryIds = $interestCategoryIds
            ->merge($ticketCategoryIds)
            ->unique()
            ->values();

        // ID događaja koje je korisnik već kupio
        $bookedEventIds = $user->tickets()->pluck('event_id');

        // Preporučeni događaji — iz relevantnih kategorija, nisu već kupljeni
        $recommended = Event::with(['city', 'category'])
            ->whereIn('category_id', $allCategoryIds)
            ->whereNotIn('id', $bookedEventIds)
            ->where('starts_at', '>', now())
            ->where('available_seats', '>', 0)
            ->orderBy('starts_at')
            ->get()
            ->sortByDesc(function ($event) use ($interestCategoryIds, $ticketCategoryIds) {
                // Bodujemo: +2 ako je u interesovanjima, +1 ako je u istoriji
                $score = 0;
                if ($interestCategoryIds->contains($event->category_id)) $score += 2;
                if ($ticketCategoryIds->contains($event->category_id)) $score += 1;
                return $score;
            })
            ->take(8)
            ->values();

        // Popularni događaji ako korisnik nema dovoljno istorije/interesovanja
        $popular = collect();
        if ($recommended->count() < 4) {
            $popular = Event::with(['city', 'category'])
                ->whereNotIn('id', $bookedEventIds)
                ->whereNotIn('id', $recommended->pluck('id'))
                ->where('starts_at', '>', now())
                ->where('available_seats', '>', 0)
                ->withCount('tickets')
                ->orderByDesc('tickets_count')
                ->take(4)
                ->get();
        }

        return view('recommendations.index', compact('recommended', 'popular'));
    }
}
