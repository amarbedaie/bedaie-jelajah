<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificates) {}

    /** Halaman semakan — pengguna memasukkan nombor sijil. */
    public function search(Request $request)
    {
        $number = trim((string) $request->query('no'));

        if ($number !== '') {
            return redirect()->route('sijil.semak.show', $number);
        }

        return view('public.certificate-search');
    }

    /** Pengesahan awam: /sijil/semak/{nombor} */
    public function verify(string $number, QrCodeService $qr)
    {
        $certificate = Certificate::with(['event.state', 'event.venue', 'event.speaker', 'template'])
            ->where('certificate_number', $number)
            ->first();

        return view('public.certificate-verify', [
            'number' => $number,
            'certificate' => $certificate,
            'qrSvg' => $certificate ? $qr->svg($certificate->verificationUrl(), 180) : null,
        ]);
    }

    /** Muat turun PDF melalui public_id yang tidak boleh diteka. */
    public function download(Certificate $certificate): Response
    {
        abort_if($certificate->isValid() === false, 410,
            'Sijil ini telah ditarik balik atau digantikan dengan versi baharu.');

        // pdfPath() menjana PDF hanya jika ia belum wujud.
        $path = $this->certificates->pdfPath($certificate);

        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$certificate->certificate_number.'.pdf"',
        ]);
    }
}
