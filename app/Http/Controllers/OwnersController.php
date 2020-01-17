<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Owner;
use App\Http\Requests\OwnerRequest;

class OwnersController extends Controller
{
    public function delete(Owner $owner, Request $request)
    {
        $site = $owner->site;
        $owner->delete();
        $request->session()->flash('success', 'Owner deleted');
        return redirect(route('sites.settings', $site));
    }

    public function create(Site $site)
    {
        $owner = new Owner;
        return view('owners.create', compact('site', 'owner'));
    }

    public function store(Site $site, OwnerRequest $request)
    {
        Owner::create($request->all() + ['site_id' => $site->id]);
        $request->session()->flash('success', 'Owner added');
        return redirect(route('sites.settings', $site));
    }

    public function edit(Owner $owner)
    {
        $site = $owner->site;
        return view('owners.edit', compact('owner', 'site'));
    }

    public function update(Owner $owner, OwnerRequest $request)
    {
        $site = $owner->site;
        $owner->update($request->all());
        $request->session()->flash('success', 'Owner updated');
        return redirect(route('sites.settings', $site));
    }
}
