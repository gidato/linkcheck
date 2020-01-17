<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Filter;
use App\Support\Service\Scan\Filter\FilterManager;
use App\Support\Value\Url;
use App\Support\Value\Throttle;
use Ramsey\Uuid\Uuid;
use App\Support\Service\SiteValidation\SiteValidator;
use App\Rules\UniqueUrl;
use Illuminate\Support\Str;

class SitesController extends Controller
{
    public function index()
    {
        return view('sites.index',['sites' => Site::all()]);
    }

    public function create()
    {
        return view('sites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => ['bail', 'required','max:255','url', new UniqueUrl]
        ]);

        $values = [
            'url' => new Url($validated['url']),
            'throttle' => new Throttle('default : default'),
            'validation_code' => Uuid::uuid4()
        ];

        $site = Site::create($values);
        return redirect(route('sites.settings', $site));
    }

    public function settings(Site $site, FilterManager $filterManager)
    {
        $filters = $filterManager->getFilterSettingsForSite($site);
        return view('sites.settings',compact('site', 'filters'));
    }

    public function editFilters(Site $site, FilterManager $filterManager)
    {
        $filters = $filterManager->getFilterSettingsForSite($site);
        $activated = collect($filters)
            ->mapWithKeys(function($filter) {
                if ($filter->on) {
                    return [$filter->key => $filter->key];
                }
                return [];
            })
            ->filter()
            ->toArray();
        return view('sites.settings.edit-filters', compact('site', 'filters', 'activated'));
    }

    public function updateFilters(Site $site, Request $request, FilterManager $filterManager)
    {
        $filters = $filterManager->getFilterSettingsForSite($site);
        $activated = $request->input('on',[]);
        $validators = [];
        $hasErrors = false;
        foreach($filters as $filter) {
            if ($activated[$filter->key] ?? false) {
                $validators[$filter->key] = $filter->getValidator($request->input($filter->key, []));
                if ($validators[$filter->key]->fails()) {
                    $hasErrors = true;
                }
            }
        }

        if ($hasErrors) {
            $redirect = back();
            foreach ($validators as $key => $validator) {
                $redirect = $redirect->withErrors($validator, Str::camel($key));
            }
            return $redirect->withInput();
        }

        // ok, now update them all!
        foreach($filters as $filter) {
            if ($activated[$filter->key] ?? false) {
                $filter->updateFilterValues($site, $validators[$filter->key]->getData());
            } else {
                $filter->turnFilterOff();
            }
        }

        $request->session()->flash('success', 'Filters updated');
        return redirect(route('sites.settings', $site));
    }

    public function editThrottling(Site $site)
    {
        return view('sites.settings.edit-throttling', compact('site'));
    }

    public function updateThrottling(Site $site, Request $request)
    {
        $validated = $request->validate([
            'internal' => 'nullable|integer',
            'external' => 'nullable|integer',
        ]);

        $site->throttle = new Throttle(
            sprintf(
                '%s:%s',
                $validated['internal'] ?? 'default',
                $validated['external'] ?? 'default'
            )
        );
        $site->save();
        $request->session()->flash('success', 'Throttling updated');
        return redirect(route('sites.settings', $site));
    }

    public function refreshVerificationCode(Site $site, Request $request)
    {
        $site->validation_code = Uuid::uuid4();
        $site->validated = false;
        $site->save();
        $request->session()->flash('success', 'Verification code refreshed. Plesae update site and then verify.');
        return redirect(route('sites.settings', $site));
    }

    public function checkVerificationCode(Site $site, SiteValidator $validator, Request $request)
    {
        if ($validator->validate($site)->isOk()) {
            $site->validated = true;
            $site->save();
            $request->session()->flash('success', 'Site verified sucessfully.');
        } else {
            $site->validated = false;
            $site->save();
            $request->session()->flash('error',
                'Site could not be verified. Please check you\'ve set the code up correctly.'
            );
        }

        return redirect(route('sites.settings', $site));
    }

    public function deleteRequest(Site $site, Request $request)
    {
        return view('sites.delete', compact('site'));
    }

    public function delete(Site $site, Request $request)
    {
        $request->validate([
            'password' => 'required|password'
        ]);
        $site->delete();
        $request->session()->flash('success', 'Site has been removed.');
        return redirect(route('sites.list'));
    }
}
