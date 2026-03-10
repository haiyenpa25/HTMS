<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sao lưu (Backup) toàn bộ Database và cấu hình hệ thống';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = "backup_cms_" . Carbon::now()->format('Y_m_d_H_i_s') . ".sql";
        $disk = Storage::disk('local');
        $backupPath = storage_path("app/backups");

        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $connection = config('database.default');
        
        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            $targetPath = $backupPath . '/' . str_replace('.sql', '.sqlite', $filename);
            
            if (file_exists($dbPath)) {
                copy($dbPath, $targetPath);
                $this->info("✅ Đã sao lưu SQLite Database thành công: " . $targetPath);
                
                // Cleanup old backups (keep last 7 days)
                $this->cleanupOldBackups($backupPath);
                return;
            }
            $this->error("❌ Không tìm thấy file SQLite: " . $dbPath);
            return;
        }

        if ($connection === 'mysql') {
            $host = config('database.connections.mysql.host');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $database = config('database.connections.mysql.database');
            $targetPath = $backupPath . '/' . $filename;
            
            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$targetPath}";
            
            $returnVar = NULL;
            $output  = NULL;
            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                $this->info("✅ Đã sao lưu MySQL Database thành công: " . $targetPath);
                $this->cleanupOldBackups($backupPath);
                return;
            }

            $this->error("❌ Không thể chạy lệnh mysqldump (Vui lòng đảm bảo mysqldump đã nằm trong PATH)");
            return;
        }

        $this->error('⚠️ Chỉ hỗ trợ backup tự động cho SQLite và MySQL hiện tại.');
    }

    private function cleanupOldBackups($path)
    {
        $files = glob($path . '/backup_cms_*');
        $now = time();
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                // Xoá các backup lưu cũ hơn 7 ngày (7 * 24 * 60 * 60 = 604800s)
                if ($now - filemtime($file) >= 604800) {
                    unlink($file);
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->info("🗑 Đã dọn dẹp {$deleted} bản backup cũ (> 7 ngày).");
        }
    }
}
