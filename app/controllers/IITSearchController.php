<?php

class IITSearchController extends BaseController {

    public function showIndex()
    {
        $template_data = array(
            'title' => 'Search IIT',
            'body_id' => 'search',
            'body_css' => 'scriptures tab-heading'
        );
        $content_data = array(
            'index_search' => IITSearchRepository::GetIITSearchIndex()
        );
        View::share('chapters', WidgetRepository::GetChapterSelection(0));
        return View::make('layouts.master', $template_data)
            ->nest('heading', 'headings.search')
            ->nest('content', 'search-index', $content_data);
    }

    public function findTerm($search_term)
    {
        $template_data = array(
            'title' => "Search Results",
            'body_id' => 'search',
            'body_css' => 'scriptures tab-heading'
        );

        $IITSearchRepository = new IITSearchRepository();

        $page = Input::get('page', 1);

        $result = $IITSearchRepository->GetIITSearchTerm($search_term, $page);

        $content_data = array(
            'search_term' => $search_term,
            'result_count' => $result['count'],
            'search_html' => $result['html'],
            'paginator' => $result['paginator']->links()
        );

        View::share('search_term', $search_term);
        View::share('chapters', WidgetRepository::GetChapterSelection(0));

        return View::make('layouts.master', $template_data)
            ->nest('heading', 'headings.search')
            ->nest('content', 'search', $content_data);
    }

}