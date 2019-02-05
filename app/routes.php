<?php
//Flatten::flushAll();
//Flatten::flushPattern('resources/.+');
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
Route::get('/Isaiah-Institute-Translation', 'IndexController@showIITInfo');
Route::get('/{chapterNumber}', 'ChapterController@showChapter')->where('chapterNumber', '^(?!67|68|69)[1-6][0-9]|[1-9]$');

Route::get('/bible', 'BibleController@showIndex');
Route::get('/bible/{bookAbbr}', 'BibleController@showBookIndex')->where('bookAbbr', '[a-z]+|\d-[a-z]+');
Route::get('/bible/{bookAbbr}/{chapterNumber}', 'BibleController@showBookChapter')
    ->where(array('bookAbbr' => '[a-z]+|\d-[a-z]+', 'chapterNumber' => '[0-9]+'));

//Route::get('/concordance', 'ConcordanceController@showIndex');
Route::get('/concordance/{concordanceLetter}', 'ConcordanceController@showLetter')->where('concordanceLetter', '[A-Za-z]');

Route::get('/resources', 'ResourceController@showIndex');
Route::get('/resources/{resourcePage}', 'ResourceController@findResource')->where('searchTerm', '(\w*-?)+');

Route::get('/search', 'IITSearchController@showIndex');
Route::get('/search/{searchTerm}', 'IITSearchController@findTerm')->where('searchTerm', '(.*)');

Route::get('/media/commentary/{fileName}', 'MediaController@showMediaCommentary');
/*Route::get('/commentary/ogg/{chapterNumber}', 'MediaController@showMediaOGG')->where('chapterNumber', '[0-9]+');
Route::get('/commentary/mp3/{chapterNumber}', 'MediaController@showMediaMP3')->where('chapterNumber', '[0-9]+');*/

Route::get('/contact', 'ContactController@GetContactForm');
Route::post('/contact', 'ContactController@SubmitContactForm');

App::missing(function($exception)
{
    $redirect_url = RedirectRepository::GetRedirectURL(Request::getRequestUri(), Input::all());

    if(!empty($redirect_url)) {
        return Redirect::to(Config::get('app.url') . $redirect_url);
    } else {
        //dd(['uri' => $uri, 'input_data' => $input_data]);
        //App::abort(404);
        $template_data = array(
            'title' => 'Error 404 (Not Found)!',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );
        $content_data = array('uri' => '/' . Request::path());
        return View::make('layouts.master', $template_data)
            ->nest('heading', 'headings.resources')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'errors.missing', $content_data);
        //return Response::view('errors.missing', array('uri' => Request::path()), 404);
    }
});