<?php

namespace App\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendPush extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $message;
    protected $users;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $users, array $message) {
        Log::info('Construct job push');
        $this->users = $users;
        $this->message = $message;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        Log::info('Fire job push');
        $controller = new Controller();
        $controller->sendPushToUser($this->users, $this->message);
    }
}
