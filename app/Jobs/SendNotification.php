<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Services\NotificationsService;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $notificationsService;
    private $tokens;
    private $notification;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(NotificationsService $_notificationsService, $_tokens, $_title, $_body="", $_data=null)
    {  
        $this->notificationsService = $_notificationsService;
        $this->tokens = $_tokens;
        $this->notification['title'] = $_title;
        $this->notification['body'] = $_body;
        $this->notification['data'] = $_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->notificationsService->notify(
            $this->tokens,
            $this->notification['title'],
            $this->notification['body'],
            $this->notification['data']
        );
    }
}
