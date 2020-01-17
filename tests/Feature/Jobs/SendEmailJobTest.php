<?php

namespace Tests\Feature\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Support\Value\EmailOption;
use App\Mail\ScanResults;
use App\Jobs\SendEmail;
use App\Support\Service\PdfGenerator;
use Illuminate\Mail\Mailer;

class SendEmailJobTest extends TestCase
{
    use RefreshDatabase;

    private $scan;
    private $user;

    public function setup() : void
    {
        parent::setup();
        $this->user = factory(\App\User::class)->create();
        $site = factory(\App\Site::class)->create();
        $this->scan = factory(\App\Scan::class)->create(['site_id' => $site->id]);
        factory(\App\Owner::class)->create(['site_id' => $site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan->id]);
    }

    /** @test */
    public function send_email_to_self()
    {
        Mail::fake();

        $job = new SendEmail($this->scan, new EmailOption('self'));
        $job->handle(app(PdfGenerator::class), app(Mailer::class));

        Mail::assertQueued(ScanResults::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

    }

    /** @test */
    public function send_email_to_all()
    {
        Mail::fake();

        $job = new SendEmail($this->scan, new EmailOption('all'));
        $job->handle(app(PdfGenerator::class), app(Mailer::class));

        Mail::assertQueued(ScanResults::class, function ($mail) {
            return $mail->hasBcc($this->user->email)
                && $mail->hasTo($this->scan->site->owners[0]->email);
        });

    }
}
