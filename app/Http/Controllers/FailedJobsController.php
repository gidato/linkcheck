<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Arr;
use App\FailedJob;

class FailedJobsController extends Controller
{
    public function index()
    {
        return view('failed-jobs.index', ['jobs' => FailedJob::all()]);
    }

    public function show(FailedJob $job, Request $request)
    {
        return view('failed-jobs.show', compact('job'));
    }

    public function retry(FailedJob $job, Request $request, QueueManager $queueManager)
    {
        $queueManager->connection($job->connection)->pushRaw(
            json_encode($this->resetAttempts($job->payload)), $job->queue
        );

        if (property_exists($job->command,'scan')) {
            $job->command->scan->message = null;
            $job->command->scan->save();
        }

        $request->session()->flash('success', "The failed job [{$job->id}] has been pushed back onto the queue!");
        $job->delete();
        return redirect(route('failed-jobs.list'));
    }

    private function resetAttempts($payload)
    {
        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }

        return $payload;
    }

    public function delete(FailedJob $job, Request $request)
    {
        $job->delete();
        $request->session()->flash('success', "The failed job [{$job->id}] has been deleted!");
        return redirect(route('failed-jobs.list'));
    }

    public function flush(Request $request)
    {
        FailedJob::truncate();
        $request->session()->flash('success', "All failed jobs have been deleted!");
        return redirect(route('failed-jobs.list'));
    }

}
