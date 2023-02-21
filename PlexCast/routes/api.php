<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'tag'], function () {
    Route::get('/search', ['as' => 'api.tag.search', 'uses' => 'App\Http\Controllers\API\TagController@search']);
    Route::post('/create', ['as' => 'api.tag.create', 'uses' => 'App\Http\Controllers\API\TagController@create']);
    Route::get('/{id}', ['as' => 'api.tag.get', 'uses' => 'App\Http\Controllers\API\TagController@get'])
        ->where('id', '[0-9]+');
    Route::put('/{id}', ['as' => 'api.tag.update', 'uses' => 'App\Http\Controllers\API\TagController@update'])
        ->where('id', '[0-9]+');
});

Route::group(['prefix' => 'tagging'], function () {
    Route::post('/create',
        ['as' => 'api.tagging.create', 'uses' => 'App\Http\Controllers\API\TaggingController@create']);
    Route::get('/metadataitem/{id}', [
        'as' => 'api.tagging.metadataitem.get',
        'uses' => 'App\Http\Controllers\API\TaggingController@getTagsByMetadataItemId'
    ])
        ->where('id', '[0-9]+');
    Route::put('/index/{id}', ['as' => 'api.tagging.updateIndex', 'uses' => 'App\Http\Controllers\API\TaggingController@updateOrder']);
    Route::get('{id}', ['as' => 'api.tagging.get', 'uses' => 'App\Http\Controllers\API\TaggingController@get'])
        ->where('id', '[0-9]+');
});

Route::group(['prefix' => 'metadataitem'], function () {
    Route::get('/search',
        ['as' => 'api.metadataItem.search', 'uses' => 'App\Http\Controllers\API\MetadataItemController@search']);
    Route::get('{id}',
        ['as' => 'api.metadataItem.get', 'uses' => 'App\Http\Controllers\API\MetadataItemController@get'])
        ->where('id', '[0-9]+');
});
