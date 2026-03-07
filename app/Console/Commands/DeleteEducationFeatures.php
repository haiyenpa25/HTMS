<?php

namespace App\Console\Commands;

use App\Models\Feature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteEducationFeatures extends Command
{
    protected $signature = 'features:cleanup';
    protected $description = 'Xóa các tính năng education-attendance và education-offering thừa';

    public function handle()
    {
        $slugs = ['education-attendance', 'education-offering'];
        $features = Feature::whereIn('slug', $slugs)->get();

        if ($features->isEmpty()) {
            $this->info('Không tìm thấy tính năng cần xóa.');
            return;
        }

        $ids = $features->pluck('id');
        
        // Xóa các records liên quan trong feature_department trước
        DB::table('feature_department')->whereIn('feature_id', $ids)->delete();
        DB::table('user_department_features')->whereIn('feature_id', $ids)->delete();
        
        // Xóa features
        foreach ($features as $f) {
            $this->info("Đã xóa: {$f->name} ({$f->slug})");
            $f->delete();
        }

        $remaining = Feature::count();
        $this->info("Còn lại {$remaining} tính năng trong hệ thống.");
    }
}
