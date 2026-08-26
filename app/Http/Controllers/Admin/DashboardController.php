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

        return view('admin.dashboard', [
            'headline' => $this->stats->headline(),
            'newApplications' => Application::submitted()
                ->where('status', ApplicationStatus::Diterima)->count(),
            'openApplications' => Application::open()->count(),
            'awaitingConfirmation' => Application::where('status', ApplicationStatus::Diluluskan)->count(),
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

    /** "Tindakan yang perlu dibuat" — senarai kerja sebenar, bukan graf. */
    private function actionQueue(): array
    {
        $actions = [];

        $needsReview = Application::where('status', ApplicationStatus::Diterima)->count();
        if ($needsReview > 0) {
            $actions[] = [
                'label' => "{$needsReview} permohonan baharu menunggu semakan",
                'url' => route('admin.permohonan', ['status' => ApplicationStatus::Diterima->value]),
                'tone' => 'warning',
            ];
        }

        $awaitingInfo = Application::where('status', ApplicationStatus::PerluMaklumat)->count();
        if ($awaitingInfo > 0) {
            $actions[] = [
                'label' => "{$awaitingInfo} permohonan menunggu maklumat pemohon",
                'url' => route('admin.permohonan', ['status' => ApplicationStatus::PerluMaklumat->value]),
                'tone' => 'neutral',
            ];
        }

        $approved = Application::where('status', ApplicationStatus::Diluluskan)->count();
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
