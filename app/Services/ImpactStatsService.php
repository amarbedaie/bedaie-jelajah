<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Models\AreaInterestRequest;
use App\Models\Event;
use App\Models\Partner;
use App\Models\State;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ImpactStatsService
{
    private const TTL = 300; // 5 minit

    /** Kaunter impak pada homepage — semuanya daripada database. */
    public function headline(): array
    {
        return Cache::remember('impact.headline', self::TTL, function () {
            $visitedStates = Event::public()->distinct()->count('state_id');
            $visitedDistricts = Event::public()->whereNotNull('district_id')->distinct()->count('district_id');

            return [
                'negeri' => $visitedStates,
                'daerah' => $visitedDistricts,
                'program' => Event::completed()->count(),
                'peserta' => (int) Event::public()->sum('attended_count'),
                'rakan' => Partner::active()->count() + Event::public()->whereNotNull('venue_id')->distinct()->count('venue_id'),
            ];
        });
    }

    /**
     * Status setiap negeri untuk peta jelajah.
     * dijelajahi | akan_datang | berlangsung | belum
     */
    public function stateMap(): Collection
    {
        $rows = Cache::remember('impact.state_map', self::TTL, function (): array {
            $states = State::orderBy('sort_order')->get();

            $aggregates = Event::public()
                ->select(
                    'state_id',
                    DB::raw('COUNT(*) as event_count'),
                    DB::raw('SUM(attended_count) as participant_count'),
                    DB::raw("SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as completed_count"),
                    DB::raw("SUM(CASE WHEN status = 'diterbitkan' AND starts_at >= NOW() THEN 1 ELSE 0 END) as upcoming_count"),
                    DB::raw("SUM(CASE WHEN status = 'berlangsung' THEN 1 ELSE 0 END) as ongoing_count"),
                )
                ->groupBy('state_id')
                ->get()
                ->keyBy('state_id');

            $districtCounts = Event::public()
                ->whereNotNull('district_id')
                ->select('state_id', DB::raw('COUNT(DISTINCT district_id) as districts'))
                ->groupBy('state_id')
                ->pluck('districts', 'state_id');

            $interest = AreaInterestRequest::select('state_id', DB::raw('COUNT(*) as total'))
                ->groupBy('state_id')
                ->pluck('total', 'state_id');

            $highDemandThreshold = max(5, (int) ceil(((float) $interest->avg()) * 1.5));

            return $states->map(function (State $state) use ($aggregates, $districtCounts, $interest, $highDemandThreshold) {
                $row = $aggregates->get($state->id);
                $interestCount = (int) ($interest[$state->id] ?? 0);

                $status = match (true) {
                    (int) ($row->ongoing_count ?? 0) > 0 => 'berlangsung',
                    (int) ($row->upcoming_count ?? 0) > 0 => 'akan_datang',
                    (int) ($row->completed_count ?? 0) > 0 => 'dijelajahi',
                    default => 'belum',
                };

                return [
                    'id' => $state->id,
                    'name' => $state->name,
                    'slug' => $state->slug,
                    'code' => $state->code,
                    'region' => $state->region,
                    'path' => $state->map_path,
                    'label_x' => $state->label_x,
                    'label_y' => $state->label_y,
                    'status' => $status,
                    'events' => (int) ($row->event_count ?? 0),
                    'completed' => (int) ($row->completed_count ?? 0),
                    'upcoming' => (int) ($row->upcoming_count ?? 0),
                    'participants' => (int) ($row->participant_count ?? 0),
                    'districts' => (int) ($districtCounts[$state->id] ?? 0),
                    'interest' => $interestCount,
                    'high_demand' => $interestCount >= $highDemandThreshold && $status === 'belum',
                ];
            })->all();
        });

        return collect($rows);
    }

    public function stateDetail(State $state): array
    {
        $events = Event::public()->where('state_id', $state->id)->with('district')->get();

        return [
            'state' => $state,
            'total_events' => $events->count(),
            'total_participants' => (int) $events->sum('attended_count'),
            'districts_visited' => $events->pluck('district.name')->filter()->unique()->sort()->values(),
            'upcoming' => Event::upcoming()->where('state_id', $state->id)
                ->with(['venue', 'district', 'category'])->limit(6)->get(),
            'completed' => Event::completed()->where('state_id', $state->id)
                ->with(['venue', 'district'])->limit(6)->get(),
            'interest_count' => AreaInterestRequest::where('state_id', $state->id)->count(),
        ];
    }

    /** Kawasan dengan permintaan tertinggi — untuk admin merancang jelajah. */
    public function topDemandAreas(int $limit = 10): Collection
    {
        return AreaInterestRequest::select(
            'state_id',
            'district_id',
            DB::raw('COUNT(*) as total'),
            DB::raw('COUNT(DISTINCT phone) as individuals'),
        )
            ->with(['state', 'district'])
            ->groupBy('state_id', 'district_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /** Topik paling diminta merentas semua permintaan kawasan. */
    public function topRequestedTopics(int $limit = 6): Collection
    {
        return AreaInterestRequest::select('event_category_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('event_category_id')
            ->with('category')
            ->groupBy('event_category_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /** Laporan impak penuh untuk satu program. */
    public function eventReport(Event $event): array
    {
        $registrations = $event->registrations()->with(['attendance', 'state', 'payment'])->get();
        $attended = $registrations->filter->hasAttended();
        $feedback = $event->feedback()->get();

        return [
            'registered' => $registrations->whereIn('status', [
                \App\Enums\RegistrationStatus::Disahkan,
                \App\Enums\RegistrationStatus::MenungguPengesahan,
            ])->count(),
            'waitlist' => $registrations->where('status', \App\Enums\RegistrationStatus::SenaraiMenunggu)->count(),
            'attended' => $attended->count(),
            'attendance_rate' => $registrations->count() > 0
                ? round($attended->count() / max(1, $registrations->whereIn('status', [
                    \App\Enums\RegistrationStatus::Disahkan,
                    \App\Enums\RegistrationStatus::MenungguPengesahan,
                ])->count()) * 100, 1)
                : 0.0,
            'walk_in' => $registrations->where('source', 'walk_in')->count(),
            'guests' => (int) $registrations->sum('guests_count'),
            'gender' => $registrations->groupBy('gender')->map->count(),
            'by_state' => $registrations->groupBy(fn ($r) => $r->state?->name ?? 'Tidak dinyatakan')->map->count()->sortDesc(),
            'revenue' => (float) $event->payments()
                ->whereIn('status', ['berjaya'])
                ->sum('amount'),
            'pending_payments' => $event->payments()->where('status', 'menunggu_pengesahan')->count(),
            'rating' => $feedback->whereNotNull('rating')->avg('rating'),
            'rating_count' => $feedback->whereNotNull('rating')->count(),
            'rating_breakdown' => collect(range(5, 1))->mapWithKeys(
                fn ($star) => [$star => $feedback->where('rating', $star)->count()]
            ),
            'wants_advanced' => $feedback->where('wants_advanced', true)->count(),
            'next_topics' => $feedback->pluck('next_topic')->filter()->take(20),
            'certificates' => $event->certificates()->valid()->count(),
            'gallery_count' => $event->gallery()->approved()->count(),
        ];
    }

    /** Ringkasan yang dipaparkan kepada Penggerak — sengaja ringkas. */
    public function mobilizerSummary(Event $event): array
    {
        $full = $this->eventReport($event);

        return [
            'registered' => $full['registered'],
            'attended' => $full['attended'],
            'attendance_rate' => $full['attendance_rate'],
            'rating' => $full['rating'] ? round((float) $full['rating'], 1) : null,
            'certificates' => $full['certificates'],
            'wants_advanced' => $full['wants_advanced'],
        ];
    }

    public static function flush(): void
    {
        Cache::forget('impact.headline');
        Cache::forget('impact.state_map');
        Cache::forget('filter.state_ids');
    }
}
