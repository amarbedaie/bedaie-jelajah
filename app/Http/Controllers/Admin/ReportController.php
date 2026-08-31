<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Services\ImpactStatsService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private ImpactStatsService $stats) {}

    public function index()
    {
        return view('admin.reports', [
            'headline' => $this->stats->headline(),
            'states' => $this->stats->stateMap(),
            'events' => Event::withSeatCounts()->withRatingAverage()->where('status', EventStatus::Selesai)
                ->with(['venue', 'state', 'speaker', 'category'])
                ->orderByDesc('starts_at')
                ->paginate(15),
            'topics' => $this->stats->topRequestedTopics(8),
            'demand' => $this->stats->topDemandAreas(8),
        ]);
    }

    public function event(Event $event)
    {
        return view('admin.report', [
            'event' => $event->load(['venue', 'state', 'district', 'speaker', 'category', 'mobilizers']),
            'report' => $this->stats->eventReport($event),
        ]);
    }

    /** Eksport CSV — dilindungi oleh gate `export-participants`. */
    public function export(Event $event): StreamedResponse
    {
        $filename = 'peserta-'.$event->short_code.'-'.now()->format('Ymd-Hi').'.csv';

        return response()->streamDownload(function () use ($event) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8

            fputcsv($out, [
                'Rujukan', 'Nama', 'Telefon', 'E-mel', 'Negeri', 'Daerah',
                'Jantina', 'Bilangan Tempat', 'Status', 'Sumber', 'Didaftar Pada',
                'Hadir', 'Masa Check-in', 'Status Bayaran',
            ]);

            Registration::where('event_id', $event->id)
                ->with(['state', 'district', 'attendance', 'payment'])
                ->orderBy('name')
                ->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r->reference_no,
                            $r->name,
                            $r->phone,
                            $r->email,
                            $r->state?->name,
                            $r->district?->name,
                            $r->gender,
                            $r->seats(),
                            $r->status->label(),
                            $r->source,
                            $r->registered_at?->format('Y-m-d H:i'),
                            $r->hasAttended() ? 'Ya' : 'Tidak',
                            $r->attendance?->checked_in_at?->format('Y-m-d H:i'),
                            $r->paymentStatus()->label(),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
