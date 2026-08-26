<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Services\Payments\PaymentManager;

class SettingController extends Controller
{
    public function templates()
    {
        return view('admin.templates', [
            // Satu baris per (key, channel) — dikumpulkan mengikut pencetus.
            'templates' => NotificationTemplate::orderBy('key')->orderBy('channel')->get()->groupBy('key'),
            'channels' => ['in_app' => 'In-App', 'mail' => 'E-mel', 'whatsapp' => 'WhatsApp'],
            // Saluran WhatsApp hanya benar-benar menghantar apabila dikonfigurasi.
            'whatsappReady' => (bool) config('jelajah.whatsapp.enabled')
                && filled(config('jelajah.whatsapp.base_url'))
                && filled(config('jelajah.whatsapp.api_key')),
        ]);
    }

    public function index(PaymentManager $payments)
    {
        return view('admin.settings', [
            'gateways' => $payments->available(),
            'activeGateway' => $payments->gateway()->key(),
        ]);
    }

    /** Log notifikasi — bukti sama ada mesej benar-benar keluar. */
    public function notificationLog(\Illuminate\Http\Request $request)
    {
        $whatsappReady = (bool) config('jelajah.whatsapp.enabled')
            && filled(config('jelajah.whatsapp.base_url'))
            && filled(config('jelajah.whatsapp.api_key'));

        return view('admin.notification-log', [
            'whatsappReady' => $whatsappReady,
            'mailReady' => ! in_array(config('mail.default'), ['log', 'array'], true),
            'queuePending' => config('queue.default') === 'database'
                ? \Illuminate\Support\Facades\DB::table('jobs')->count()
                : null,
            'logs' => \App\Models\NotificationLog::query()
                ->when($request->filled('saluran'), fn ($q) => $q->where('channel', $request->string('saluran')))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->filled('q'), function ($q) use ($request) {
                    $term = '%'.$request->string('q').'%';
                    $q->where(fn ($w) => $w->where('recipient_name', 'like', $term)
                        ->orWhere('recipient_address', 'like', $term));
                })
                ->latest()
                ->paginate(30)
                ->withQueryString(),
        ]);
    }
}
