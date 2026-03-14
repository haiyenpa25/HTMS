<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DocsController extends Controller
{
    public function setup()
    {
        return Inertia::render('Docs/Setup', [
            // No specific props needed for now
        ]);
    }
}
