<?php

namespace App\Jobs;

use App\Mail\WelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeMailJob implements ShouldQueue
{
    use Queueable;

    public $email;
    public $name;

    /**
     * Create a new job instance.
     */
    public function __construct($email,$name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new WelcomeMail($this->name));
    }
}
