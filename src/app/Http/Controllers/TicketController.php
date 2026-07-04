<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = auth()->user()->tickets()
            ->with(['event.city', 'event.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tickets.index', compact('tickets'));
    }

    public function create(Event $event)
    {
        if ($event->available_seats === 0) {
            return back()->with('error', 'Nema slobodnih mesta za ovaj događaj.');
        }

        return view('tickets.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $event->available_seats,
            'card_number' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!$this->luhnCheck($value)) {
                    $fail('Broj kartice nije validan.');
                }
            }],
            'card_holder' => 'required|string|max:255',
            'expiry' => 'required|string|regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/',
            'cvv' => 'required|string|size:3',
        ]);

        DB::transaction(function () use ($validated, $event) {
            Ticket::create([
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'quantity' => $validated['quantity'],
                'total_price' => $validated['quantity'] * $event->ticket_price,
                'status' => 'paid',
            ]);

            $event->decrement('available_seats', $validated['quantity']);
        });

        return redirect()->route('tickets.index')
            ->with('success', 'Rezervacija je uspešno obavljena!');
    }

    public function cancel(Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        if ($ticket->status !== 'paid') {
            return back()->with('error', 'Ova karta ne može biti otkazana.');
        }

        DB::transaction(function () use ($ticket) {
            $ticket->event->increment('available_seats', $ticket->quantity);
            $ticket->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Rezervacija je otkazana, mesta su vraćena.');
    }

    private function luhnCheck(string $number): bool
    {
        $number = preg_replace('/\D/', '', $number);
        $length = strlen($number);

        if ($length < 13 || $length > 19) {
            return false;
        }

        $sum = 0;
        $alternate = false;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return $sum % 10 === 0;
    }
}
