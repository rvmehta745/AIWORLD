<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\SendResetPasswordEmail;

class SendTemplateEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $templateType;
    protected $templateData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($templateType, $templateData)
    {
        $this->templateType = $templateType;
        $this->templateData = $templateData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->templateType === "RESET_PASSWORD") {
            
            \Mail::to($this->templateData['email'])->send(new SendResetPasswordEmail($this->templateData));
        }
        if ($this->templateType === "FORGOT_PASSWORD") {
            
            \Mail::to($this->templateData['email'])->send(new SendResetPasswordEmail($this->templateData));
        }
    }
}
