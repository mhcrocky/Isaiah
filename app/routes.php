<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

/*Route::get('/', function()
{
	return View::make('index');
});*/

Route::get('/', 'IndexController@showIndex');
Route::get('/{chapterNumber}', 'ChapterController@showChapter')->where('chapterNumber', '[0-9]+');

Route::get('/concordance', 'ConcordanceController@showIndex');
Route::get('/concordance/{concordanceLetter}', 'ConcordanceController@showLetter')->where('concordanceLetter', '[A-Za-z]');
//Route::get('/concordance', 'ConcordanceController@showIndex');
//Route::get('/concordance/{concordanceLetter}', 'ConcordanceController@showLetter')->where('concordanceLetter', '[A-Za-z]');

Route::get('/search', 'IITSearchController@showIndex');
Route::get('/search/{searchTerm}', 'IITSearchController@findTerm')->where('searchTerm', '(\w*\s?)+');
//Route::get('/search', 'IITSearchController@showIndex');
//Route::get('/search/{searchTerm}', 'IITSearchController@findTerm')->where('searchTerm', '(\w*\s?)+');

Route::get('/media/commentary/{fileName}', 'MediaController@showMediaCommentary');
/*Route::get('/commentary/ogg/{chapterNumber}', 'MediaController@showMediaOGG')->where('chapterNumber', '[0-9]+');
Route::get('/commentary/mp3/{chapterNumber}', 'MediaController@showMediaMP3')->where('chapterNumber', '[0-9]+');*/