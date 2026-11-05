<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CertificateStatus;
use App\Enums\CertificateType;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $certificates = Certificate::with(['event.state', 'registration', 'user'])
            ->when($request->filled('program'),
                fn ($q) => $q->whereHas('event', fn ($e) => $e->where('short_code', $request->string('program'))))
            ->when($request->filled('jenis'), fn ($q) => $q->where('type', $request->string('jenis')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('recipient_name', 'like', $term)
                    ->orWhere('certificate_number', 'like', $term));
            })
            ->latest()
            ->paginate(25)->withQueryString();

        return view('admin.certificates', [
            'certificates' => $certificates,
            'events' => Event::completed()->orderByDesc('starts_at')->limit(50)->get(),
            'types' => CertificateType::cases(),
            'statuses' => CertificateStatus::cases(),
        ]);
    }

    /** Pengesahan bayaran manual — diuruskan oleh komponen Livewire. */
    public function payments()
    {
        return view('admin.payments');
    }

    /** Pratonton PDF templat dengan data contoh (gaya/orientasi boleh diuji tanpa simpan). */
    public function preview(Request $request, CertificateTemplate $template, CertificateService $certificates)
    {
        $design = in_array($request->query('design'), array_keys(\App\Enums\CertificateDesign::options()), true)
            ? $request->query('design') : null;
        $orientation = in_array($request->query('orientation'), ['landscape', 'portrait'], true)
            ? $request->query('orientation') : null;

        return $certificates->previewPdf($template, $design, $orientation)
            ->stream("pratonton-{$template->slug}.pdf");
    }
}
