<?php

namespace App\Support\Service;

use App\Scan;
use Barryvdh\DomPDF\PDF;
use Illuminate\Filesystem\FilesystemManager;

class PdfGenerator
{
    private $pdf;
    private $local;

    public function __construct(Pdf $pdf, FilesystemManager $storage)
    {
        $this->pdf = $pdf;
        $this->local = $storage->disk('local');
    }

    public function generate(Scan $scan) : string
    {
        $filename = sprintf('scans/scan_%s_%s.pdf',
            $scan->id,
            $scan->updated_at->format('Ymd_His')
        );

        if (!$this->local->exists($filename)) {
            $this->pdf->loadView('scans.pdf', compact('scan'));
            $this->pdf->setWarnings(false);
            $this->local->put($filename, $this->pdf->output());
        }

        return $filename;
    }

}
