@if ($qrDataUri)
    <img src="{{ $qrDataUri }}" alt="" style="width: 62pt; height: 62pt;">
@endif
<div class="serial">{{ $certificate->certificate_number }}</div>
<div class="verify">Sahkan di {{ parse_url(config('app.url'), PHP_URL_HOST) }}/sijil/semak</div>
