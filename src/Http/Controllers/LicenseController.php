<?php

namespace ImTaxu\LaravelLicense\Http\Controllers;

use Illuminate\Routing\Controller;

class LicenseController extends Controller
{
    /**
     * Lisans hatası sayfasını göster
     *
     * @return \Illuminate\View\View
     */
    public function showError()
    {
        return view('license::error');
    }
}
