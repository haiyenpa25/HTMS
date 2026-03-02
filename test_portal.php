<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'superadmin@httlthanhmyloi.com')->first();
$response = $app->handle(
    Illuminate\Http\Request::create('/portal', 'GET')->setUserResolver(function() use ($user) { return $user; })
);

$content = $response->getContent();
if (preg_match('/&quot;page&quot;:(.*?)}">/s', $content, $matches) || preg_match('/data-page="(.*?)"/', $content, $matches)) {
    $page = json_decode(html_entity_decode($matches[1]), true);
    print_r($page['props']);
} else {
    echo "Could not find Inertia page data.\n";
    echo substr($content, 0, 1000);
}
