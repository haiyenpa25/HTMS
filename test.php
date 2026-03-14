<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'tb.thanhtrang@httlthanhmyloi.com')->first();
Illuminate\Support\Facades\Auth::login($user);

$request = Illuminate\Http\Request::create('/calendar/events', 'GET');
$controller = new App\Http\Controllers\CalendarController();
$response = $controller->fetchEvents($request);

echo $response->getContent();
