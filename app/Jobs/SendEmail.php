<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Scan;
use App\User;
use App\Support\Value\EmailOption;
use App\Support\Service\PdfGenerator;
use Illuminate\Contracts\Mail\Mailer;
use App\Mail\ScanResults;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $scan;
    private $email;

    public function __construct(Scan $scan, EmailOption $email)
    {
        $this->scan = $scan;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PdfGenerator $pdfGenerator, Mailer $mailer)
    {
        if (!$this->email->all && !$this->email->self) {
            return;
        }

        $site = $this->scan->site;

        if ($this->email->all) {
            $mailer = $mailer->bcc(User::first());
            foreach($site->owners as $owner) {
                $mailer = $mailer->to($owner);
            }
        }

        if ($this->email->self) {
            $mailer = $mailer->to(User::first());
        }

        $mailer->queue(new ScanResults($this->scan, $pdfGenerator->generate($this->scan)));
    }
}
