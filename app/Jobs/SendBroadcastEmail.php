<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailBroadcast;
use App\Models\User;
use App\Mail\NewsletterEmail;
use Illuminate\Support\Facades\Mail;

class SendBroadcastEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $broadcast;

    // Timeout job sau 15 phút nếu gửi quá đông
    public $timeout = 900;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailBroadcast $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $query = User::query()->whereNotNull('email');
            
            // Filter by Roles
            if (!empty($this->broadcast->target_roles)) {
                $roleNames = \App\Models\Role::whereIn('id', $this->broadcast->target_roles)->pluck('name')->toArray();
                if (count($roleNames) > 0) {
                    $query->role($roleNames);
                }
            }
            
            // Filter by Departments
            if (!empty($this->broadcast->target_departments)) {
                $query->whereHas('member.departments', function($q) {
                    $q->whereIn('departments.id', $this->broadcast->target_departments);
                });
            }

            // Get all recipients chunked to reduce memory usage
            $totalRecipients = $query->count();
            
            $this->broadcast->update([
                'total_recipients' => $totalRecipients,
                'success_count' => 0,
                'failed_count' => 0,
                'sent_at' => now(),
            ]);

            $success = 0;
            $failed = 0;

            $query->chunk(100, function ($users) use (&$success, &$failed) {
                foreach ($users as $user) {
                    try {
                        Mail::to($user->email)->send(new NewsletterEmail($this->broadcast, $user));
                        $success++;
                    } catch (\Exception $e) {
                        \Log::error("Broadcast Error sending to {$user->email}: " . $e->getMessage());
                        $failed++;
                    }
                }
            });

            $this->broadcast->update([
                'status' => 'completed',
                'success_count' => $success,
                'failed_count' => $failed,
            ]);

        } catch (\Exception $e) {
            \Log::error("Broadcast Job failed (ID: {$this->broadcast->id}): " . $e->getMessage());
            $this->broadcast->update([
                'status' => 'failed'
            ]);
        }
    }
}
