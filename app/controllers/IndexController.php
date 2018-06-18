<?php

class IndexController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | Default Index Controller
    |--------------------------------------------------------------------------
    |
    | You may wish to use controllers instead of, or in addition to, Closure
    | based routes. That's great! Here is an example controller method to
    | get you started. To route to this controller, just add the route:
    |
    |	Route::get('/', 'IndexController@showIndex');
    |
    */

    public function showIndex()
    {
        $template_data = array(
            'title' => 'The Book of Isaiah',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );
        $content_data = array(
            'widget_chapter_sel' => WidgetRepository::GetChapterSelection(0),
            'index_chapters' => IndexRepository::GetChapterIndex()
        );
        return View::make('layouts.application', $template_data)->nest('content', 'chapter-index', $content_data);
        /*$view = View::make('layouts.application', $template_data)->nest('content', 'chapter-index');
        View::composer(array('chapter-index'), function($view)
        {
            $view->with('widget_chapter_sel', WidgetRepository::GetChapterSelection(0));
        });
        return $view;*/
    }

}