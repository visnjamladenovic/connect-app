<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DescriptionController extends Controller
{
    public function create()
    {
        return view('admin.events.generate-description');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'keywords' => 'required|string|max:500',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date' => 'required|string|max:100',
        ]);

        $prompt = "Napiši privlačan opis događaja na srpskom jeziku (latinica) za platformu Connect.
Ključne informacije:
- Ključne reči: {$validated['keywords']}
- Kategorija: {$validated['category']}
- Lokacija: {$validated['location']}
- Datum: {$validated['date']}

Opis treba da bude između 100 i 200 reči, informativan i privlačan za posetioce.
Nemoj koristiti naslov, samo sam tekst opisa.";

        try {
            $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
            ]);

            $description = $response->json('content.0.text');

            return back()
                ->with('generated_description', $description)
                ->withInput();

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Greška pri generisanju opisa. Pokušajte ponovo.')
                ->withInput();
        }
    }
}
