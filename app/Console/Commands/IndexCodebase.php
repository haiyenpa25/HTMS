<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * HTMS Code Indexer — Tương tự Grapuco Knowledge Graph
 *
 * Scan toàn bộ codebase và sinh các file index trong thư mục index/
 * Chạy: php artisan htms:index
 *
 * Output files:
 *   index/01_routes.md        — Route → Controller → Middleware map
 *   index/02_controllers.md   — Controller → Methods → Models used
 *   index/03_models.md        — Model → Table → Relationships
 *   index/04_vue_pages.md     — Vue Page → Props → API Calls
 *   index/05_feature_graph.md — Feature Slug → Full execution chain
 *   index/06_middleware.md    — Middleware chain per portal
 *   index/README.md           — Quick navigation guide
 */
class IndexCodebase extends Command
{
    protected $signature = 'htms:index {--force : Overwrite existing index}';
    protected $description = 'Generate HTMS code knowledge graph in index/ directory';

    private string $indexDir;
    private array $routeData = [];
    private array $controllerData = [];
    private array $modelData = [];
    private array $vueData = [];

    public function handle(): int
    {
        $this->indexDir = base_path('index');
        File::ensureDirectoryExists($this->indexDir);

        $this->info('🔍 HTMS Code Indexer — Building Knowledge Graph...');
        $this->newLine();

        $this->line('  📍 Parsing routes/web.php...'); $this->parseRoutes();
        $this->line('  🎮 Parsing Controllers...'); $this->parseControllers();
        $this->line('  📦 Parsing Models...'); $this->parseModels();
        $this->line('  🖼  Parsing Vue Pages...'); $this->parseVuePages();

        $this->newLine();
        $this->line('  📝 Writing index/01_routes.md...'); $this->writeRoutesIndex();
        $this->line('  📝 Writing index/02_controllers.md...'); $this->writeControllersIndex();
        $this->line('  📝 Writing index/03_models.md...'); $this->writeModelsIndex();
        $this->line('  📝 Writing index/04_vue_pages.md...'); $this->writeVueIndex();
        $this->line('  📝 Writing index/05_feature_graph.md...'); $this->writeFeatureGraph();
        $this->line('  📝 Writing index/06_middleware.md...'); $this->writeMiddlewareIndex();
        $this->line('  📝 Writing index/README.md...'); $this->writeReadme();

        $this->newLine();
        $this->info('✅ Knowledge Graph generated in ' . $this->indexDir);
        $this->line('   → Read index/README.md first for navigation guide.');

        return self::SUCCESS;
    }

    // ═══════════════════════════════════════════════════════════════
    // PARSERS
    // ═══════════════════════════════════════════════════════════════

    private function parseRoutes(): void
    {
        $content = File::get(base_path('routes/web.php'));
        $lines = explode("\n", $content);

        $currentMiddleware = [];
        $currentPrefix = '';
        $middlewareStack = [];
        $prefixStack = [];

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);

            // Track group middleware
            if (preg_match('/->middleware\(([^)]+)\)/', $line, $m)) {
                $mw = trim($m[1], " '\"[]");
                $middlewareStack[] = $mw;
                $currentMiddleware = $middlewareStack;
            }

            // Track prefix
            if (preg_match('/->prefix\(\'([^\']+)\'\)/', $line, $m)) {
                $prefixStack[] = $m[1];
                $currentPrefix = implode('/', array_filter($prefixStack));
            }

            // Parse actual routes
            if (preg_match('/Route::(get|post|put|patch|delete|resource)\(\'([^\']+)\',\s*\[([^,]+)::class,\s*\'([^\']+)\'\]\)(?:->name\(\'([^\']+)\'\))?/', $line, $m)) {
                $method     = strtoupper($m[1]);
                $uri        = $m[2];
                $controller = basename(str_replace('\\', '/', trim($m[3])));
                $action     = $m[4];
                $name       = $m[5] ?? '';
                $mwList     = array_values(array_filter(array_unique($middlewareStack)));

                $this->routeData[] = [
                    'method'     => $method,
                    'uri'        => '/' . ltrim($currentPrefix . '/' . ltrim($uri, '/'), '/'),
                    'controller' => $controller,
                    'action'     => $action,
                    'name'       => $name,
                    'middleware' => $mwList,
                    'feature'    => $this->extractFeatureSlug($mwList),
                    'portal'     => $this->extractPortalType($currentPrefix, $mwList),
                ];
            }

            // Reset stacks on closing braces
            if (str_contains($line, '});')) {
                if (!empty($middlewareStack)) array_pop($middlewareStack);
                if (!empty($prefixStack)) array_pop($prefixStack);
                $currentMiddleware = $middlewareStack;
                $currentPrefix = implode('/', array_filter($prefixStack));
            }
        }
    }

    private function parseControllers(): void
    {
        $dirs = [
            base_path('app/Http/Controllers'),
        ];

        foreach ($dirs as $dir) {
            $files = File::allFiles($dir);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') continue;

                $content = $file->getContents();
                $className = $file->getFilenameWithoutExtension();

                // Extract namespace
                preg_match('/^namespace\s+([\w\\\\]+)/m', $content, $nsMatch);
                $namespace = $nsMatch[1] ?? '';

                // Extract use statements (models)
                preg_match_all('/^use\s+App\\\\Models\\\\(\w+)/m', $content, $useMatch);
                $models = $useMatch[1] ?? [];

                // Extract use statements (services)
                preg_match_all('/^use\s+App\\\\Services\\\\(\w+)/m', $content, $svcMatch);
                $services = $svcMatch[1] ?? [];

                // Extract public methods
                preg_match_all('/public\s+function\s+(\w+)\s*\([^)]*\)/', $content, $methodMatch);
                $methods = array_filter($methodMatch[1] ?? [], fn($m) => !in_array($m, ['__construct', 'rules', 'authorize', 'booted', 'boot']));

                // Extract Inertia renders
                preg_match_all('/Inertia::render\([\'"]([^\'"]+)[\'"]/', $content, $inertiaMatch);
                $views = $inertiaMatch[1] ?? [];

                // Extract model queries
                preg_match_all('/(\w+)::(all|where|find|create|firstOrCreate|updateOrCreate|with|query)\(/', $content, $queryMatch);
                $queriedModels = array_unique($queryMatch[1] ?? []);
                $queriedModels = array_filter($queriedModels, fn($m) => !in_array($m, ['DB', 'Cache', 'Log', 'Auth', 'Session']));

                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath);

                $this->controllerData[$className] = [
                    'file'     => $relativePath,
                    'class'    => $namespace . '\\' . $className,
                    'models'   => array_values(array_unique(array_merge($models, array_values($queriedModels)))),
                    'services' => $services,
                    'methods'  => array_values($methods),
                    'views'    => $views,
                ];
            }
        }
    }

    private function parseModels(): void
    {
        $files = File::files(base_path('app/Models'));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = $file->getContents();
            $className = $file->getFilenameWithoutExtension();

            // Extract table name
            preg_match('/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $tableMatch);
            $table = $tableMatch[1] ?? $this->guessTable($className);

            // Extract fillable
            preg_match('/protected\s+\$fillable\s*=\s*\[([^\]]+)\]/s', $content, $fillableMatch);
            $fillable = [];
            if (isset($fillableMatch[1])) {
                preg_match_all('/[\'"](\w+)[\'"]/', $fillableMatch[1], $fm);
                $fillable = $fm[1] ?? [];
            }

            // Extract relationships
            $relationships = [];
            $relTypes = ['hasMany', 'hasOne', 'belongsTo', 'belongsToMany', 'morphMany', 'morphTo', 'hasManyThrough'];
            foreach ($relTypes as $relType) {
                if (preg_match_all('/function\s+(\w+)\s*\(\)[^{]*{\s*(?:\/\/[^\n]*)?\s*return\s+\$this->' . $relType . '\(([^)]+)\)/', $content, $rm)) {
                    foreach ($rm[1] as $idx => $relName) {
                        $target = trim(explode(',', $rm[2][$idx])[0], " \n\t'\"");
                        $target = basename(str_replace('\\', '/', $target));
                        $relationships[] = "$relType → $target (method: $relName)";
                    }
                }
            }

            // Extract casts
            preg_match('/protected\s+\$casts\s*=\s*\[([^\]]+)\]/s', $content, $castsMatch);
            $castFields = [];
            if (isset($castsMatch[1])) {
                preg_match_all('/[\'"](\w+)[\'"]/', $castsMatch[1], $cf);
                $castFields = array_chunk($cf[1] ?? [], 2);
            }

            $this->modelData[$className] = [
                'table'         => $table,
                'fillable'      => $fillable,
                'relationships' => $relationships,
            ];
        }
    }

    private function parseVuePages(): void
    {
        $dirs = [
            base_path('resources/js/Pages'),
            base_path('resources/js/Layouts'),
        ];

        foreach ($dirs as $baseDir) {
            if (!File::exists($baseDir)) continue;
            $files = File::allFiles($baseDir);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'vue') continue;

                $content = $file->getContents();
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath);
                $name = str_replace(['resources/js/', '.vue'], '', $relativePath);

                // Extract defineProps
                $props = [];
                if (preg_match('/defineProps\(\s*\{([^}]+)\}/s', $content, $pm)) {
                    preg_match_all('/(\w+)\s*[:{]/', $pm[1], $propNames);
                    $props = $propNames[1] ?? [];
                }
                // Also match array-style props
                if (preg_match('/defineProps\(\[([^\]]+)\]\)/s', $content, $apm)) {
                    preg_match_all('/[\'"](\w+)[\'"]/', $apm[1], $apn);
                    $props = array_merge($props, $apn[1] ?? []);
                }

                // Extract axios calls
                preg_match_all('/axios\.(get|post|put|patch|delete)\([\'`]([^\'"`]+)[\'`]/', $content, $axiosMatch);
                $apiCalls = [];
                foreach ($axiosMatch[1] as $idx => $axMethod) {
                    $apiCalls[] = strtoupper($axMethod) . ' ' . $axiosMatch[2][$idx];
                }

                // Extract route() calls
                preg_match_all('/route\([\'"]([^\'"]+)[\'"]/', $content, $routeMatch);
                $usedRoutes = array_unique($routeMatch[1] ?? []);

                // Extract Inertia router.get/post
                preg_match_all('/router\.(get|post|put|patch|delete)\(route\([\'"]([^\'"]+)[\'"]/', $content, $inertiaMatch);
                foreach ($inertiaMatch[1] as $idx => $im) {
                    $apiCalls[] = '[Inertia] ' . strtoupper($im) . ' → ' . $inertiaMatch[2][$idx];
                }

                // Extract component imports
                preg_match_all('/import\s+(\w+)\s+from\s+[\'"]([^\'"]+)[\'"]/', $content, $importMatch);
                $components = [];
                foreach ($importMatch[1] as $idx => $importName) {
                    $src = $importMatch[2][$idx];
                    if (str_contains($src, 'Components') || str_contains($src, 'Layouts')) {
                        $components[] = $importName . ' ← ' . $src;
                    }
                }

                $this->vueData[$name] = [
                    'file'        => $relativePath,
                    'props'       => array_values(array_unique($props)),
                    'api_calls'   => $apiCalls,
                    'used_routes' => array_values($usedRoutes),
                    'components'  => $components,
                ];
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // WRITERS
    // ═══════════════════════════════════════════════════════════════

    private function writeRoutesIndex(): void
    {
        $out = "# Route Map\n";
        $out .= "> Auto-generated by `php artisan htms:index` — " . now()->format('Y-m-d H:i') . "\n\n";

        // Group by portal
        $grouped = [];
        foreach ($this->routeData as $r) {
            $grouped[$r['portal']][] = $r;
        }

        foreach ($grouped as $portal => $routes) {
            $out .= "## Portal: `{$portal}`\n\n";
            $out .= "| Method | URI | Route Name | Controller@Action | Middleware | Feature |\n";
            $out .= "|--------|-----|------------|-------------------|------------|--------|\n";
            foreach ($routes as $r) {
                $mw = implode(', ', array_map(fn($m) => "`$m`", $r['middleware']));
                $out .= "| `{$r['method']}` | `{$r['uri']}` | `{$r['name']}` | `{$r['controller']}@{$r['action']}` | {$mw} | `{$r['feature']}` |\n";
            }
            $out .= "\n";
        }

        File::put($this->indexDir . '/01_routes.md', $out);
    }

    private function writeControllersIndex(): void
    {
        $out = "# Controllers Map\n";
        $out .= "> Auto-generated by `php artisan htms:index` — " . now()->format('Y-m-d H:i') . "\n\n";

        // Group by directory
        $grouped = [];
        foreach ($this->controllerData as $name => $data) {
            $dir = dirname($data['file']);
            $dir = str_replace('app/Http/Controllers/', '', $dir);
            $grouped[$dir][$name] = $data;
        }

        ksort($grouped);
        foreach ($grouped as $dir => $controllers) {
            $out .= "## `{$dir}`\n\n";
            foreach ($controllers as $name => $data) {
                $out .= "### `{$name}`\n";
                $out .= "- **File:** `{$data['file']}`\n";
                if ($data['models']) {
                    $out .= "- **Models:** " . implode(', ', array_map(fn($m) => "`$m`", $data['models'])) . "\n";
                }
                if ($data['services']) {
                    $out .= "- **Services:** " . implode(', ', array_map(fn($s) => "`$s`", $data['services'])) . "\n";
                }
                if ($data['views']) {
                    $out .= "- **Inertia Views:** " . implode(', ', array_map(fn($v) => "`$v`", $data['views'])) . "\n";
                }
                if ($data['methods']) {
                    $out .= "- **Methods:** `" . implode('`, `', $data['methods']) . "`\n";
                }
                $out .= "\n";
            }
        }

        File::put($this->indexDir . '/02_controllers.md', $out);
    }

    private function writeModelsIndex(): void
    {
        $out = "# Models Map\n";
        $out .= "> Auto-generated by `php artisan htms:index` — " . now()->format('Y-m-d H:i') . "\n\n";
        $out .= "| Model | Table | Relationships |\n";
        $out .= "|-------|-------|---------------|\n";

        ksort($this->modelData);
        foreach ($this->modelData as $name => $data) {
            $rels = implode('; ', array_slice($data['relationships'], 0, 3));
            if (count($data['relationships']) > 3) $rels .= '...';
            $out .= "| `{$name}` | `{$data['table']}` | {$rels} |\n";
        }

        $out .= "\n## Detailed Model Definitions\n\n";
        foreach ($this->modelData as $name => $data) {
            $out .= "### `{$name}` → `{$data['table']}`\n";
            if ($data['fillable']) {
                $out .= "- **Fillable:** `" . implode('`, `', $data['fillable']) . "`\n";
            }
            if ($data['relationships']) {
                $out .= "- **Relationships:**\n";
                foreach ($data['relationships'] as $rel) {
                    $out .= "  - {$rel}\n";
                }
            }
            $out .= "\n";
        }

        File::put($this->indexDir . '/03_models.md', $out);
    }

    private function writeVueIndex(): void
    {
        $out = "# Vue Pages Map\n";
        $out .= "> Auto-generated by `php artisan htms:index` — " . now()->format('Y-m-d H:i') . "\n\n";

        // Group by directory
        $grouped = [];
        foreach ($this->vueData as $name => $data) {
            $parts = explode('/', $name);
            $dir = count($parts) > 2 ? implode('/', array_slice($parts, 0, 2)) : $parts[0];
            $grouped[$dir][$name] = $data;
        }

        ksort($grouped);
        foreach ($grouped as $dir => $pages) {
            $out .= "## `{$dir}`\n\n";
            foreach ($pages as $name => $data) {
                $out .= "### `{$name}`\n";
                $out .= "- **File:** `{$data['file']}`\n";
                if ($data['props']) {
                    $out .= "- **Props:** `" . implode('`, `', $data['props']) . "`\n";
                }
                if ($data['used_routes']) {
                    $out .= "- **Routes Used:** `" . implode('`, `', array_slice($data['used_routes'], 0, 8)) . "`\n";
                }
                if ($data['api_calls']) {
                    $out .= "- **API Calls:**\n";
                    foreach (array_slice($data['api_calls'], 0, 6) as $call) {
                        $out .= "  - `{$call}`\n";
                    }
                }
                if ($data['components']) {
                    $out .= "- **Components:** " . implode(', ', array_map(fn($c) => "`$c`", array_slice($data['components'], 0, 5))) . "\n";
                }
                $out .= "\n";
            }
        }

        File::put($this->indexDir . '/04_vue_pages.md', $out);
    }

    private function writeFeatureGraph(): void
    {
        $features = [
            'attendance'           => ['portal_type' => 'activities', 'label' => 'Điểm Danh'],
            'visitation'           => ['portal_type' => 'activities', 'label' => 'Thăm Viếng'],
            'members'              => ['portal_type' => 'activities', 'label' => 'Thành Viên'],
            'assignments'          => ['portal_type' => 'activities', 'label' => 'Phân Công'],
            'reports'              => ['portal_type' => 'activities', 'label' => 'Báo Cáo'],
            'finance'              => ['portal_type' => 'activities', 'label' => 'Tài Chính'],
            'care'                 => ['portal_type' => 'global',     'label' => 'Chăm Sóc'],
            'chronicles'           => ['portal_type' => 'global',     'label' => 'Sổ Tay HT'],
            'documents'            => ['portal_type' => 'global',     'label' => 'Tài Liệu'],
            'education-classes'    => ['portal_type' => 'ministry',   'label' => 'Lớp Học'],
            'education-attendance' => ['portal_type' => 'ministry',   'label' => 'Điểm Danh Lớp'],
            'education-offering'   => ['portal_type' => 'ministry',   'label' => 'Tiền Dâng Lớp'],
            'education-report'     => ['portal_type' => 'ministry',   'label' => 'Báo Cáo Giáo Dục'],
            'activity-logs'        => ['portal_type' => 'global',     'label' => 'Nhật Ký'],
            'assets'               => ['portal_type' => 'global',     'label' => 'Thiết Bị'],
            'users-manager'        => ['portal_type' => 'global',     'label' => 'Người Dùng'],
            'forms-manager'        => ['portal_type' => 'global',     'label' => 'Biểu Mẫu'],
        ];

        $out = "# Feature Graph — MAC V2 Execution Chain\n";
        $out .= "> Auto-generated by `php artisan htms:index` — " . now()->format('Y-m-d H:i') . "\n\n";
        $out .= "**Permission flow:**\n```\n";
        $out .= "Request → CheckPortalAccess (portal entry)\n";
        $out .= "        → PortalAccessMiddleware (feature-level: portal.access:slug,type)\n";
        $out .= "        → PortalService::canAccess(user, dept, slug)\n";
        $out .= "            Level 1: FeatureAssignmentService::isFeatureEnabled(dept, slug)\n";
        $out .= "            Level 2: UserDepartmentFeature (explicit override, else inherit Level 1)\n";
        $out .= "```\n\n";

        foreach ($features as $slug => $meta) {
            $out .= "## `{$slug}` — {$meta['label']} (`{$meta['portal_type']}`)\n\n";

            // Find matching routes
            $matchingRoutes = array_filter($this->routeData, fn($r) => $r['feature'] === $slug);

            if ($matchingRoutes) {
                $out .= "**Routes:**\n";
                foreach ($matchingRoutes as $r) {
                    $out .= "- `{$r['method']} {$r['uri']}` → `{$r['controller']}@{$r['action']}`";
                    if ($r['name']) $out .= " (name: `{$r['name']}`)";
                    $out .= "\n";
                }
                $out .= "\n";
            }

            // Find matching Vue pages
            $vuePages = [];
            foreach ($this->vueData as $vueName => $vueInfo) {
                $slugWords = str_replace(['-', '_'], ['', ''], strtolower($slug));
                if (str_contains(strtolower($vueName), $slugWords) || 
                    str_contains(strtolower($vueName), explode('-', $slug)[0])) {
                    $vuePages[] = $vueName;
                }
            }
            if ($vuePages) {
                $out .= "**Vue Pages:** `" . implode('`, `', $vuePages) . "`\n\n";
            }

            // DB tables (based on known models)
            $knownModels = [
                'attendance'           => ['Meeting', 'MeetingAttendance'],
                'visitation'           => ['Visitation', 'VisitationReason'],
                'members'              => ['Member', 'OrgMembership'],
                'assignments'          => ['DutyAssignment', 'DepartmentRole'],
                'reports'              => ['DepartmentReport'],
                'finance'              => ['DepartmentFund', 'DepartmentTransaction'],
                'care'                 => ['CareRequest', 'CareLog'],
                'chronicles'           => ['ChronicleEntry'],
                'documents'            => ['Document'],
                'education-classes'    => ['EduClass', 'EduClassMember'],
                'education-attendance' => ['EduSession', 'EduSessionRecord'],
                'education-offering'   => ['EduClassFund', 'EduClassTransaction'],
                'education-report'     => ['EduReport'],
                'activity-logs'        => ['(Spatie ActivityLog)'],
                'assets'               => ['Asset', 'AssetLoan'],
                'users-manager'        => ['User', 'UserDepartmentFeature'],
                'forms-manager'        => ['FormTemplate'],
            ];

            if (isset($knownModels[$slug])) {
                $out .= "**Models/Tables:** ";
                $tableList = [];
                foreach ($knownModels[$slug] as $modelName) {
                    $table = $this->modelData[$modelName]['table'] ?? $this->guessTable($modelName);
                    $tableList[] = "`{$modelName}` → `{$table}`";
                }
                $out .= implode(', ', $tableList) . "\n\n";
            }

            $out .= "---\n\n";
        }

        File::put($this->indexDir . '/05_feature_graph.md', $out);
    }

    private function writeMiddlewareIndex(): void
    {
        $out = "# Middleware Chain Map\n";
        $out .= "> Auto-generated by `php artisan htms:index` — " . now()->format('Y-m-d H:i') . "\n\n";

        $chains = [
            'Portal Sinh Hoạt (/portal)' => [
                'entry'      => 'auth → CheckPortalAccess:activities',
                'features'   => 'portal.access:slug,activities → PortalAccessMiddleware → PortalService::canAccess()',
                'file_entry' => 'app/Http/Middleware/CheckPortalAccess.php',
                'file_feat'  => 'app/Http/Middleware/PortalAccessMiddleware.php',
            ],
            'Portal Mục Vụ (/ministry)' => [
                'entry'      => 'auth → EnsureMinistryContext',
                'features'   => 'portal.access:slug,ministry → PortalAccessMiddleware',
                'file_entry' => 'app/Http/Middleware/EnsureMinistryContext.php',
                'file_feat'  => 'app/Http/Middleware/PortalAccessMiddleware.php',
            ],
            'Portal Chấp Sự (/deacon)' => [
                'entry'      => 'auth → EnsureDeaconContext',
                'features'   => 'portal.access:slug,leadership → PortalAccessMiddleware',
                'file_entry' => 'app/Http/Middleware/EnsureDeaconContext.php',
                'file_feat'  => 'app/Http/Middleware/PortalAccessMiddleware.php',
            ],
            'Portal Tài Chính (/finance-portal)' => [
                'entry'      => 'auth → EnsureFinanceContext',
                'features'   => '(context-based, no feature middleware)',
                'file_entry' => 'app/Http/Middleware/EnsureFinanceContext.php',
                'file_feat'  => 'N/A',
            ],
            'Portal Tín Hữu (/member)' => [
                'entry'      => 'auth only',
                'features'   => '(no feature access control)',
                'file_entry' => 'N/A',
                'file_feat'  => 'N/A',
            ],
            'Admin Routes (/admin/*)' => [
                'entry'      => 'auth → EnsureSuperAdmin',
                'features'   => '(superadmin bypasses all)',
                'file_entry' => 'app/Http/Middleware/EnsureSuperAdmin.php',
                'file_feat'  => 'N/A',
            ],
        ];

        foreach ($chains as $portal => $info) {
            $out .= "## {$portal}\n\n";
            $out .= "```\n{$info['entry']}\n  └─ {$info['features']}\n```\n\n";
            $out .= "- **Entry Middleware:** `{$info['file_entry']}`\n";
            $out .= "- **Feature Middleware:** `{$info['file_feat']}`\n\n";
        }

        $out .= "## Global (Every Request)\n\n";
        $out .= "```\nHandleInertiaRequests → injects:\n";
        $out .= "  auth.user, auth.allowed_features, allAvailableDepartments\n";
        $out .= "```\n\n";
        $out .= "- **File:** `app/Http/Middleware/HandleInertiaRequests.php`\n";
        $out .= "- `auth.allowed_features` = `PortalService::getAllowedFeaturesForDept(user, activeDeptId)`\n\n";

        $out .= "## MAC V2 Access Service\n\n";
        $out .= "| Method | Purpose | File |\n";
        $out .= "|--------|---------|------|\n";
        $out .= "| `canAccess(user, deptId, slug)` | Gate per-request | `PortalService.php` |\n";
        $out .= "| `getAllowedFeaturesForDept(user, deptId)` | Sidebar/card list | `PortalService.php` |\n";
        $out .= "| `isFeatureEnabledForDepartment(dept, slug)` | Level 1 check | `FeatureAssignmentService.php` |\n";
        $out .= "| `getAvailableFeaturesForDepartment(dept)` | Level 1 full map | `FeatureAssignmentService.php` |\n";

        File::put($this->indexDir . '/06_middleware.md', $out);
    }

    private function writeReadme(): void
    {
        $out = "# HTMS Code Knowledge Graph\n";
        $out .= "> Generated: " . now()->format('Y-m-d H:i') . " | Run `php artisan htms:index` to refresh\n\n";

        $out .= "## Navigation Guide\n\n";
        $out .= "| File | Dùng khi nào |\n";
        $out .= "|------|-------------|\n";
        $out .= "| [01_routes.md](01_routes.md) | Cần biết URL nào → Controller nào, middleware gì |\n";
        $out .= "| [02_controllers.md](02_controllers.md) | Cần biết Controller dùng Model nào, render View gì |\n";
        $out .= "| [03_models.md](03_models.md) | Cần biết Model → Table, relationships |\n";
        $out .= "| [04_vue_pages.md](04_vue_pages.md) | Cần biết Vue page nhận Props gì, gọi API nào |\n";
        $out .= "| [05_feature_graph.md](05_feature_graph.md) | Cần trace feature slug → toàn bộ execution chain |\n";
        $out .= "| [06_middleware.md](06_middleware.md) | Cần hiểu chuỗi middleware của từng portal |\n\n";

        $out .= "## Quick Reference\n\n";
        $out .= "### Portal Entry Points\n";
        $out .= "| URL | Auth Gate | Controller |\n";
        $out .= "|-----|-----------|------------|\n";
        $out .= "| `/portal` | `CheckPortalAccess:activities` | `DepartmentPortalController` |\n";
        $out .= "| `/ministry` | `EnsureMinistryContext` | `MinistryPortalController` |\n";
        $out .= "| `/finance-portal` | `EnsureFinanceContext` | `Portal/FinancePortalController` |\n";
        $out .= "| `/deacon` | `EnsureDeaconContext` | `Portal/DeaconPortalController` |\n";
        $out .= "| `/member` | `auth` | `MemberPortalController` |\n";
        $out .= "| `/admin/*` | `EnsureSuperAdmin` | Various |\n\n";

        $out .= "### Key Service Files\n";
        $out .= "```\nPortalService.php            — canAccess(), getAllowedFeaturesForDept()\n";
        $out .= "FeatureAssignmentService.php — isFeatureEnabled(), getAvailableFeatures()\n";
        $out .= "```\n\n";

        $out .= "### Stats\n";
        $out .= "- **Routes parsed:** " . count($this->routeData) . "\n";
        $out .= "- **Controllers indexed:** " . count($this->controllerData) . "\n";
        $out .= "- **Models indexed:** " . count($this->modelData) . "\n";
        $out .= "- **Vue pages indexed:** " . count($this->vueData) . "\n";

        File::put($this->indexDir . '/README.md', $out);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function extractFeatureSlug(array $middlewares): string
    {
        foreach ($middlewares as $mw) {
            if (preg_match('/portal\.access:([^,]+)/', $mw, $m)) {
                return $m[1];
            }
        }
        return '-';
    }

    private function extractPortalType(string $prefix, array $middlewares): string
    {
        if (str_starts_with($prefix, 'portal')) return 'activities';
        if (str_starts_with($prefix, 'ministry')) return 'ministry';
        if (str_starts_with($prefix, 'deacon')) return 'deacon';
        if (str_starts_with($prefix, 'finance-portal')) return 'finance';
        if (str_starts_with($prefix, 'member')) return 'member';
        if (str_starts_with($prefix, 'admin')) return 'admin';
        foreach ($middlewares as $mw) {
            if (str_contains($mw, 'SuperAdmin')) return 'admin';
            if (str_contains($mw, 'Ministry')) return 'ministry';
        }
        return 'global';
    }

    private function guessTable(string $className): string
    {
        // Simple pluralize
        $name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        if (str_ends_with($name, 'y')) return substr($name, 0, -1) . 'ies';
        if (str_ends_with($name, 's')) return $name . 'es';
        return $name . 's';
    }
}
