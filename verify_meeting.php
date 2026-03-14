<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $meetings = \App\Models\Meeting::where('date', '>=', now()->toDateString())
        ->orderBy('date')
        ->take(3)
        ->get()
        ->map(fn($m) => [
            'id' => 'mtg_'.$m->id,
            'title' => $m->topic ?: 'Buổi nhóm',
            'meeting_date' => $m->date . ' ' . $m->time,
            'location' => null,
            'type' => $m->type,
        ])
        ->toArray();

    echo "Meetings successfully fetched: " . count($meetings) . "\n";
    print_r($meetings);
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
