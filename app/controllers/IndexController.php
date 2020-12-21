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

    public function showIndex() {
        // dd(Config::get('app.debug'));
        /*$test_data = array('var1' => 'test data1', 'var2' => 'test data 2');
        Log::getMonolog()->addInfo('Log Message', $test_data);*/


        $template_data = array(
            'title' => 'The Book of Isaiah',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        $content_data = array(
            'index_chapters' => IndexRepository::GetChapterIndex()
        );

        View::share('chapters', WidgetRepository::GetChapterSelection(0));
        View::share('letters', WidgetRepository::GetConcordanceSelection());

        return View::make('layouts.master', $template_data)
            ->nest('tracking_code', 'widgets.tracking-code')
            ->nest('vignette_modal', 'modals.vignette')
            ->nest('heading', 'headings.chapter-index')
            ->nest('top_nav', 'widgets.chapter-selection-top')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'chapter-index', $content_data);
    }

    public function showAbout() {
        $template_data = array(
            'title' => 'About',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        $content_data = array();

        return View::make('layouts.master', $template_data)
            ->nest('tracking_code', 'widgets.tracking-code')
            ->nest('vignette_modal', 'modals.vignette')
            ->nest('heading', 'headings.chapter-index')
            ->nest('top_nav', 'widgets.chapter-selection-top')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'resources.about', $content_data);
    }

    public function showIITInfo() {
        $template_data = array(
            'title' => 'Isaiah Institute Translation',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        $content_data = array();

        /*$content_data = array(
            'index_chapters' => IndexRepository::GetChapterIndex()
        );

        View::share('chapters', WidgetRepository::GetChapterSelection(0));
        View::share('letters', WidgetRepository::GetConcordanceSelection());
        View::share('search_term', Input::get('search', ''));*/

        return View::make('layouts.master', $template_data)
            ->nest('tracking_code', 'widgets.tracking-code')
            ->nest('vignette_modal', 'modals.vignette')
            ->nest('heading', 'headings.chapter-index')
            ->nest('top_nav', 'widgets.chapter-selection-top')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'resources.isaiah-institute-translation', $content_data);
    }
}