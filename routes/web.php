<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false]);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'HomeController@index')->name('home');

    Route::get('profile','UsersController@profile')->name('profile');
    Route::get('change-password','UsersController@editPassword')->name('edit-password');
    Route::put('change-password','UsersController@updatePassword')->name('update-password');

    Route::get('sites', 'SitesController@index')->name('sites.list');
    Route::get('sites/create', 'SitesController@create')->name('sites.create');
    Route::post('sites', 'SitesController@store')->name('sites.store');
    Route::get('sites/{site}', 'SitesController@settings')->name('sites.settings');
    Route::get('sites/{site}/filters', 'SitesController@editFilters')->name('filters.edit');
    Route::patch('sites/{site}/filters', 'SitesController@updateFilters')->name('filters.update');
    Route::get('sites/{site}/redirects', 'ApprovedRedirectController@index')->name('sites.redirects.list');
    Route::delete('sites/{site}/redirects/{redirect}', 'ApprovedRedirectController@delete')->name('sites.redirects.delete');
    Route::post('sites/{site}/redirects', 'ApprovedRedirectController@store')->name('sites.redirects.approve');
    Route::get('sites/{site}/throttling', 'SitesController@editThrottling')->name('throttling.edit');
    Route::patch('sites/{site}/throttling', 'SitesController@updateThrottling')->name('throttling.update');
    Route::patch('sites/{site}/verification-refresh', 'SitesController@refreshVerificationCode')->name('verification.refresh');
    Route::patch('sites/{site}/verification-check', 'SitesController@checkVerificationCode')->name('verification.check');
    Route::get('sites/{site}/delete', 'SitesController@deleteRequest')->name('sites.delete.request');
    Route::delete('sites/{site}', 'SitesController@delete')->name('sites.delete');

    Route::get('scans', 'ScansController@index')->name('scans.list');
    Route::post('scans', 'ScansController@store')->name('scans.store');
    Route::post('scans/{scan}/abort', 'ScansController@abort')->name('scans.abort');
    Route::get('scans/{scan}', 'ScansController@show')->name('scans.show');
    Route::post('scans/{scan}/rescan-errors', 'ScansController@rescanErrors')->name('scans.rescan.errors');
    Route::post('scans/{scan}/rescan-referrers', 'ScansController@rescanReferrers')->name('scans.rescan.referrers');
    Route::delete('scans/{scan}', 'ScansController@delete')->name('scans.delete');
    Route::delete('scans', 'ScansController@deleteMany')->name('scans.delete.many');
    Route::post('scans/{scan}/email-self', 'ScansController@emailSelf')->name('scans.email.self');
    Route::post('scans/{scan}/email-all', 'ScansController@emailAll')->name('scans.email.all');

    Route::get('owners/{site}/create','OwnersController@create')->name('owners.create');
    Route::post('owners/{site}','OwnersController@store')->name('owners.store');
    Route::delete('owners/{owner}','OwnersController@delete')->name('owners.delete');
    Route::get('owners/{owner}/edit','OwnersController@edit')->name('owners.edit');
    Route::put('owners/{owner}','OwnersController@update')->name('owners.update');


});
