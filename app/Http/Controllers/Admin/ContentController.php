<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class ContentController extends Controller
{
    /** Galeri & testimoni diuruskan sepenuhnya melalui komponen Livewire. */
    public function gallery()
    {
        return view('admin.gallery');
    }

    /** Rakan & penaja diuruskan sepenuhnya melalui komponen Livewire. */
    public function partners()
    {
        return view('admin.partners');
    }

    public function pages()
    {
        return view('admin.content', [
            'legal' => [
                'privacy' => Setting::get('legal.privacy'),
                'terms' => Setting::get('legal.terms'),
            ],
        ]);
    }
}
