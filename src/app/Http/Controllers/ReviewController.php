<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Event $event)
    {
        // Korisnik može ostaviti recenziju samo ako je kupio kartu
        $hasPaidTicket = auth()->user()->tickets()
            ->where('event_id', $event->id)
            ->where('status', 'paid')
            ->exists();

        if (!$hasPaidTicket) {
            return back()->with('error', 'Možete oceniti samo događaje za koje ste kupili kartu.');
        }

        // Jedan korisnik = jedna recenzija po događaju
        $alreadyReviewed = Review::where('user_id', auth()->id())
            ->where('event_id', $event->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'Već ste ocenili ovaj događaj.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'event_id' => $event->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Recenzija je uspešno dodata.');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Recenzija je obrisana.');
    }
}
