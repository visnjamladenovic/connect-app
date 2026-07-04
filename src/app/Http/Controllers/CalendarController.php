<?php

namespace App\Http\Controllers;

use App\Models\Event;

class CalendarController extends Controller
{
    public function download(Event $event)
    {
        $startDate = $event->starts_at->format('Ymd\THis\Z');
        $endDate = $event->starts_at->addHours(2)->format('Ymd\THis\Z');
        $now = now()->format('Ymd\THis\Z');

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Connect//Event Calendar//SR\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . $event->id . "@connect.app\r\n";
        $ics .= "DTSTAMP:" . $now . "\r\n";
        $ics .= "DTSTART:" . $startDate . "\r\n";
        $ics .= "DTEND:" . $endDate . "\r\n";
        $ics .= "SUMMARY:" . $this->escapeString($event->title) . "\r\n";
        $ics .= "DESCRIPTION:" . $this->escapeString($event->description ?? '') . "\r\n";
        $ics .= "LOCATION:" . $this->escapeString($event->location_name . ', ' . $event->city->name) . "\r\n";
        $ics .= "GEO:" . $event->latitude . ";" . $event->longitude . "\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        $filename = 'event-' . $event->id . '.ics';

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function escapeString(string $value): string
    {
        return str_replace(
            ['\\', "\n", ',', ';'],
            ['\\\\', '\\n', '\\,', '\\;'],
            $value
        );
    }
}
