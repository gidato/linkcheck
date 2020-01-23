<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Scan;
use App\Support\Service\Scan\NewScanGenerator;
use App\Support\Service\Scan\RescanErrorsScanGenerator;
use App\Support\Service\Scan\RescanReferrersScanGenerator;
use App\Jobs\ProcessScan;
use App\Jobs\SendEmail;
use App\FailedJob;
use Illuminate\Support\Facades\DB;
use App\Support\Value\EmailOption;
use Illuminate\Queue\QueueManager;

class ScansController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->get('id');
        if ($siteId) {
            $scans = Scan::where('site_id', $siteId)->orderBy('updated_at','DESC')->get();
        } else {
            $scans = Scan::orderBy('updated_at','DESC')->get();
        }
        $sites = Site::orderBy('url')->get();
        return view('scans.index', compact('siteId','scans','sites'));
    }

    public function store(Request $request, NewScanGenerator $generator)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id'
        ]);

        $site = Site::findOrFail($validated['site_id']);
        $scan = $generator->generateScan($site);
        ProcessScan::dispatch($scan);
        $request->session()->flash('success', 'Scan queued');
        return back();
    }

    /**
     * try to restart a failed job for this scan
     */
    public function retry(Scan $scan, Request $request, QueueManager $queueManager)
    {
        $job = $this->findJobForScan($scan);
        if (!$job) {
            $request->session()->flash('error', 'Can\'t find failed job');
            $scan->status = 'errors';
            $scan->save();
            return back();
        }

        $queueManager->connection($job->connection)->pushRaw(
            json_encode($this->resetAttempts($job->payload)), $job->queue
        );

        $request->session()->flash('success', "The failed job has been pushed back onto the queue!");
        $job->delete();
        return back();
    }

    private function findJobForScan(Scan $scan) : ?FailedJob
    {
        return FailedJob::all()
            ->filter(function($job) use ($scan) {
                return ProcessScan::class == $job->jobName // same job
                    && property_exists($job->command,'scan')
                    && $job->command->scan->id == $scan->id // same scan
                    && 1 > abs($job->failed_at->diffInSeconds($scan->updated_at)); // within a second of each other
            })
            ->first();
    }

    private function resetAttempts($payload)
    {
        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }

        return $payload;
    }


    public function rescanErrors(Scan $scan, Request $request, RescanErrorsScanGenerator $generator)
    {
        if (!$scan->hasLinkErrors() && !$scan->hasWarnings()) {
            return redirect('sites.list');
        }

        $scan = $generator->generateScan($scan);
        ProcessScan::dispatch($scan);
        $request->session()->flash('success', 'Scan queued');
        return back();
    }

    public function rescanReferrers(Scan $scan, Request $request, RescanReferrersScanGenerator $generator)
    {
        if (!$scan->hasLinkErrors() && !$scan->hasWarnings()) {
            return redirect('sites.list');
        }

        $scan = $generator->generateScan($scan);
        ProcessScan::dispatch($scan);
        $request->session()->flash('success', 'Scan queued');
        return back();
    }

    public function abort(Scan $scan, Request $request)
    {
        if ($scan->status == 'queued' || $scan->status == 'processing') {
            $scan->status = 'aborted';
            $scan->save();
            $request->session()->flash('success', 'Scan aborted');
        } else {
            $request->session()->flash('success', 'Scan already completed');
        }

        return back();
    }

    public function show(Scan $scan)
    {
        return view('scans.show', compact('scan'));
    }

    public function delete(Scan $scan, Request $request)
    {
        $scan->delete();
        $request->session()->flash('success', 'Scan removed');
        return back();
    }

    public function deleteMany(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required'
        ]);

        DB::table('scans')->whereIn('id', $validated['id'])->delete();
        $request->session()->flash('success', 'Scans removed');
        return back();
    }

    public function emailSelf(Scan $scan, Request $request)
    {
        SendEmail::dispatch($scan, new EmailOption('self'));
        $request->session()->flash('success', 'Email sent');
        return back();
    }

    public function emailAll(Scan $scan, Request $request)
    {
        SendEmail::dispatch($scan, new EmailOption('all'));
        $request->session()->flash('success', 'Email sent');
        return back();
    }

}
