<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Sistem ini ditulis dalam Bahasa Melayu, bukan diterjemahkan. Mesej
 * pengesahan Laravel ialah satu-satunya teks yang boleh terlepas ke
 * bahasa Inggeris tanpa disedari, kerana ia datang daripada rangka kerja.
 */
class LocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_mesej_pengesahan_dalam_bahasa_melayu(): void
    {
        $this->assertSame('ms', config('app.locale'));

        $this->assertSame(
            'Ruangan nama penuh wajib diisi.',
            trans('validation.required', ['attribute' => 'nama penuh']),
        );
    }

    public function test_borang_pendaftaran_memaparkan_ralat_bahasa_melayu(): void
    {
        $validator = Validator::make(
            [],
            ['name' => 'required', 'email' => 'required|email', 'phone' => 'required'],
            [],
            ['name' => 'nama penuh', 'email' => 'e-mel', 'phone' => 'nombor WhatsApp'],
        );

        $errors = collect($validator->errors()->messages())->flatten()->all();

        $this->assertNotEmpty($errors);

        foreach ($errors as $message) {
            $this->assertStringNotContainsString('field is required', $message,
                "Mesej pengesahan masih dalam bahasa Inggeris: {$message}");
        }

        // Dan borang sebenar memaparkannya kepada pengguna.
        $this->from(route('register'))
            ->post(route('register'), [])
            ->assertSessionHasErrors('name');

        $this->followingRedirects()
            ->from(route('register'))
            ->post(route('register'), [])
            ->assertDontSee('field is required');
    }

    public function test_mesej_log_masuk_gagal_dalam_bahasa_melayu(): void
    {
        $this->assertStringNotContainsString('credentials', trans('auth.failed'));
        $this->assertStringNotContainsString('password', trans('auth.password'));
    }

    /**
     * Ralat mesti dipautkan kepada medannya, bukan sekadar diwarnakan merah.
     * Warna sahaja tidak sampai kepada pembaca skrin.
     */
    public function test_ralat_borang_dipautkan_kepada_medannya(): void
    {
        $html = $this->followingRedirects()
            ->from(route('register'))
            ->post(route('register'), ['email' => 'bukan-emel'])
            ->getContent();

        $this->assertStringContainsString('aria-invalid="true"', $html,
            'Medan yang gagal mesti ditandakan aria-invalid.');
        $this->assertStringContainsString('aria-describedby="name-error"', $html,
            'Medan mesti menunjuk kepada mesej ralatnya.');
        $this->assertStringContainsString('id="name-error"', $html,
            'Mesej ralat mesti mempunyai id yang sepadan.');
        $this->assertStringContainsString('role="alert"', $html,
            'Ralat mesti diumumkan apabila dipaparkan.');
    }
}
