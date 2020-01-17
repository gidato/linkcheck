<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Scan;

class ScanResults extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var Scan
     */
    public $scan;

    /**
     * @var string
     */
    private $pdfFilename;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Scan $scan, string $pdfFilename)
    {
        $this->scan = $scan;
        $this->pdfFilename = $pdfFilename;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Link Check Report for ' . $this->scan->site->url)
                    ->view('emails.scan.results')
                    ->attachFromStorageDisk(
                        'local',
                        $this->pdfFilename,
                        'linkcheck_scan_' . $this->scan->updated_at->format('Y-m-d_H-i-s') . '.pdf',
                        [
                            'mime' => 'application/pdf'
                        ]
                    );
    }
}
