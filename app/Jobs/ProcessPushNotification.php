<?php

namespace App\Jobs;

use App\Models\PushNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kutia\Larafirebase\Facades\Larafirebase;
use Illuminate\Support\Facades\Log;

class ProcessPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $pushNotification;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PushNotification $pushNotification)
    {
        $this->pushNotification = $pushNotification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Processing push notification: {$this->pushNotification->title}");
        Log::info("app company id: {$this->pushNotification->company_id}");
        Log::info("app company id: {$this->pushNotification->message}");

        try {
            $fcmTokens =  User::whereNotNull('fcm_token')->where('app_company_id', $this->pushNotification->company_id)->pluck('fcm_token')->toArray();
            Larafirebase::fromArray(['title' => $this->pushNotification->title, 'body' => $this->pushNotification->message])->sendNotification($fcmTokens);
            $this->pushNotification->status = boolval($this->pushNotification->save());
            Log::info("app company status: { d$this->pushNotification->status}");
            Log::info("token numbers: {" . count($fcmTokens) . "}");
        } catch (\Exception $e) {
            Log::info("push error" . $e->getMessage());
            $this->pushNotification->status = false;
            $this->pushNotification->save();
        }
    }
}
