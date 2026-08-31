<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Registration;
use App\Services\ImpactStatsService;

class DashboardController extends Controller
{
    public function __construct(private ImpactStatsService $stats) {}

    public function index()
    {
        $today = now()->startOfDay();

        // Enam kiraan status permohonan dalam satu pertanyaan, bukan enam.
        // Dashboard ialah skrin yang paling kerap dibuka pasukan BeDaie.
        $this->statusCounts = $byStatus = Application::query()
            ->whereNotNull('submitted_at')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (ApplicationStatus ...$s) => collect($s)
            ->sum(fn ($x) => (int) ($byStatus[$x->value] ?? 0));

        return view('admin.dashboard', [
            'headline' => $this->stats->headline(),
            'newApplications' => $count(ApplicationStatus::Diterima),
            'openApplications' => $count(...ApplicationStatus::openStatuses()),
            'awaitingConfirmation' => $count(ApplicationStatus::Diluluskan),
            'upcomingEvents' => Event::upcoming()->count(),
            'todayEvents' => Event::whereBetween('starts_at', [$today, $today->clone()->endOfDay()])
                ->with(['venue', 'state', 'speaker'])->orderBy('starts_at')->get(),
            'registrationsThisMonth' => Registration::where('registered_at', '>=', now()->startOfMonth())->count(),
            'attendedTotal' => (int) Event::public()->sum('attended_count'),
            'certificatesIssued' => Certificate::valid()->count(),
            'recentApplications' => Application::submitted()
                ->with(['state', 'district', 'category'])
                ->latest('submitted_at')->limit(6)->get(),
            'demand' => $this->stats->topDemandAreas(6),
            'actions' => $this->actionQueue(),
        ]);
    }

    /** Kiraan status yang telah diagregat, dikongsi dengan actionQueue(). */
    private $statusCounts = [];

    /** "Tindakan yang perlu dibuat" — senarai kerja sebenar, bukan graf. */
    private function actionQueue(): array
    {
        $actions = [];

        $needsReview = $this->statusCounts[ApplicationStatus::Diterima->value] ?? 0;
        if ($needsReview > 0) {
            $actions[] = [
                'label' => "{$needsReview} permohonan baharu menunggu semakan",
                'url' => route('admin.permohonan', ['status' => ApplicationStatus::Diterima->value]),
                'tone' => 'warning',
            ];
        }

        $awaitingInfo = $this->statusCounts[ApplicationStatus::PerluMaklumat->value] ?? 0;
        if ($awaitingInfo > 0) {
            $actions[] = [
                'label' => "{$awaitingInfo} permohonan menunggu maklumat pemohon",
                'url' => route('admin.permohonan', ['status' => ApplicationStatus::PerluMaklumat->value]),
                'tone' => 'neutral',
            ];
        }

        $approved = $this->statusCounts[ApplicationStatus::Diluluskan->value] ?? 0;
        if ($approved > 0) {
            $actions[] = [
                'label' => "{$approved} permohonan diluluskan — sedia untuk dicipta program",
                'url' => route('admin.permohonan', ['status' => ApplicationStatus::Diluluskan->value]),
                'tone' => 'success',
            ];
        }

        $ended = Event::where('status', EventStatus::Diterbitkan)
            ->where('starts_at', '<', now()->subHours(6))->count();
        if ($ended > 0) {
            $actions[] = [
                'label' => "{$ended} program telah tamat tetapi belum ditutup",
                'url' => route('admin.program', ['status' => 'perlu_ditutup']),
                'tone' => 'danger',
            ];
        }

        return $actions;
    }
}
