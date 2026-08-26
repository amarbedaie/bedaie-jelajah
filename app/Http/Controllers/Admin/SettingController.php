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
}
