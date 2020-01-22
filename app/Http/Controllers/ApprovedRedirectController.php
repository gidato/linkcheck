<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\ApprovedRedirect;
use App\Support\Value\Url;

class ApprovedRedirectController extends Controller
{
    public function index(Site $site)
    {
        return view('sites.settings.redirects.index', compact('site'));
    }

    public function delete(Site $site, ApprovedRedirect $redirect, Request $request)
    {
        if ($redirect->site->id != $site->id) {
            abort(404);
        }

        $this->updateAllScansWithMatchingRedirects($site, $redirect, $approved = false);
        $redirect->delete();
        $request->session()->flash('success', 'Redirect deleted');
        return back();
    }

    public function store(Site $site, Request $request)
    {
        $validated = $request->validate([
            'from_url' => ['bail', 'required','max:255','url'],
            'to_url' => ['bail', 'required','max:255','url']
        ]);

        if ($site->approvedRedirects->where('from_url', $validated['from_url'])->where('to_url', $validated['to_url'])->count() == 0) {
            // add an new approval (ie, hen not already one set)
            $redirect = $site->approvedRedirects()->create([
                'from_url' => new Url($validated['from_url']),
                'to_url' => new Url($validated['to_url']),
            ]);
            $request->session()->flash('success', 'Redirect approved');
            $this->updateAllScansWithMatchingRedirects($site, $redirect, $approved = true);
        }
        return back();
    }

    private function updateAllScansWithMatchingRedirects(Site $site, ApprovedRedirect $redirect, $approval)
    {
        foreach ($site->scans as $scan) {
            foreach ($scan->getRedirects() as $page) {
                if ($page->url == $redirect->from_url && $page->redirect == $redirect->to_url) {
                    $page->redirect_approved = $approval;
                    $page->save();
                }
            }
        }
    }
}
