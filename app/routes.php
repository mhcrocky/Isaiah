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
Route::get('/{chapterNumber}', 'ChapterController@showChapter')->where('chapterNumber', '[0-9]+');

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
    $redirect_url = '';
    $uri = Request::getRequestUri();
    $input_data = Input::all();
    $uri = preg_replace('/(\?|&)XDEBUG_SESSION_START=\d{1,}/', '', $uri);

    if(!empty($input_data) && !empty($input_data['h'])) {
        $h = $input_data['h'];
    } else {
        $h = '';
    }

    if(preg_match('/\/isaiah_ch_(\d{1,2})\.html/', $uri, $matches)) {
        if(!empty($matches[1])) {
            $chapter_number = ltrim($matches[1], '0');
            $redirect_url = "/{$chapter_number}#one_col";
        }
    } elseif(preg_match('/\/Concordance\/chapter(\d{1,2})\.html/i', $uri, $matches)) {
        if(!empty($matches[1])) {
            $chapter_number = $matches[1];
            if(preg_match('/c\d{1,2}v(\d{1,2})(a|b)?-(\w+)/', $h, $h_matches)) {
                if(!empty($h_matches) && !empty($h_matches[1] && !empty($h_matches[3]))) {
                    $verse_number = $h_matches[1];
                    if(empty($h_matches[2])) {
                        $verse_number = $verse_number . 'a';
                    } else {
                        $verse_number = $verse_number . $h_matches[2];
                    }
                    $word = $h_matches[3];
                }
                $redirect_url = "/{$chapter_number}?citation=c{$chapter_number}v{$verse_number}-{$word}#concordance";
            }
        }
    } elseif(preg_match('/\/Concordance\/concordance([A-Z])\.html/i', $uri, $matches)) {
        if(!empty($matches[1])) {
            $concordance_letter = $matches[1];
            if(preg_match('/c(\d{1,2})v(\d{1,2})(a|b)?-(\w+)/', $h, $h_matches)) {
                if(!empty($h_matches) && !empty($h_matches[1] && !empty($h_matches[2]) && !empty($h_matches[4]))) {
                    $chapter_number = $h_matches[1];
                    $verse_number = $h_matches[2];
                    if(empty($h_matches[3])) {
                        $verse_number = $verse_number . 'a';
                    } else {
                        $verse_number = $verse_number . $h_matches[3];
                    }
                    $word = $h_matches[4];
                }
                $redirect_url = "/concordance/{$concordance_letter}?citation=c{$chapter_number}v{$verse_number}-{$word}#{$word}";
            }
        }
    }

    if(!empty($redirect_url)) {
        return Redirect::to('http://isaiahexplained.local' . $redirect_url);
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