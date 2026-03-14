<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'tb.thanhtrang@httlthanhmyloi.com')->first();
auth()->login($user);

$userDepartments = $user->member ? $user->member->departments->pluck('id')->toArray() : [];
$query = App\Models\Event::with('department');

$query->where(function ($q) use ($userDepartments) {
    $q->whereIn('scope_type', ['global', 'internal'])
      ->orWhere(function ($subQ) use ($userDepartments) {
          $subQ->where('scope_type', 'department')
               ->whereIn('scope_id', $userDepartments);
      });
});

$events = $query->get();
echo "Found " . $events->count() . " events for tb.thanhtrang:\n";
foreach($events as $e) {
    echo "- " . $e->title . " (Date: " . $e->start_time . ", Scope: " . $e->scope_type . " ID: " . $e->scope_id . ")\n";
}
