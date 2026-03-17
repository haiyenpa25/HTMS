<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\FeatureSeeder;
use App\Models\Feature;
use Illuminate\Support\Facades\Log;

class SyncFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:sync-features';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ/Cập nhật danh sách tính năng (Features) hệ thống an toàn mà không làm mất phân quyền';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Feature Synchronization...');
        
        $features = FeatureSeeder::FEATURES;
        $count = 0;

        foreach ($features as $data) {
            $feature = Feature::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $this->line("✅ Synced feature: {$feature->name} ({$feature->slug})");
            $count++;
        }

        $this->info("🎉 Successfully synced {$count} features without dropping existing scopes!");
    }
}
