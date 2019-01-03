<?php

class BibleController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function showIndex()
	{
        $template_data = array(
            'title' => 'The Holy Bible',
            'body_id' => 'book-index',
            'body_css' => 'bible scriptures section-heading'
        );

        $book_index = BibleRepository::GetKJVBookIndex();

        $books = [];
        $book_count = count($book_index);
        for($i = 0; $i < $book_count; $i++) {
            $books[$i]['book_lds_url'] = $book_index[$i]->book_lds_url;
            $books[$i]['book_title'] = $book_index[$i]->book_title;
            $books[$i]['book_chapters'] = BibleRepository::GetBookChapters($book_index[$i]->book_lds_url);
        }

        $content_data = array(
            'books' => $books
        );

        /*View::share('chapters', WidgetRepository::GetChapterSelection(0));
        View::share('letters', WidgetRepository::GetConcordanceSelection());*/

        return View::make('layouts.master', $template_data)
            ->nest('heading', 'headings.chapter-index')
            //->nest('top_nav', 'widgets.chapter-selection-top')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'bible.book-index', $content_data);
	}

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function showBookIndex($book_abbr)
    {
        $title = BibleRepository::GetBookTitleFromAbbr($book_abbr);
        $template_data = array(
            'title' => $title,
            'body_id' => 'book-index',
            'body_css' => 'bible scriptures section-heading'
        );

        $content_data = array(
            'book_title' => $title,
            'book_abbr' => $book_abbr,
            'book_chapters' => BibleRepository::GetBookChapters($book_abbr)
        );

        View::share('chapters', WidgetRepository::GetKJVChapterSelection(0, count(BibleRepository::GetBookChapters($book_abbr))));
        View::share('letters', WidgetRepository::GetConcordanceSelection());

        return View::make('layouts.master', $template_data)
            ->nest('heading', 'headings.chapter-index')
            ->nest('top_nav', 'widgets.chapter-selection-top')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'bible.book-chapter-index', $content_data);
    }

    /**
     * @param $book_abbr
     * @param $chapter_number
     * @return View
     */
    public function showBookChapter($book_abbr, $chapter_number)
    {
        $title = BibleRepository::GetBookTitleFromAbbr($book_abbr);
        $template_data = array(
            'title' => "$title $chapter_number",
            'body_id' => 'book-index',
            'body_css' => 'bible scriptures section-heading'
        );

        $content_data = array(
            'book_title' => $title,
            'chapter_number' => $chapter_number,
            //'book_abbr' => $book_abbr,
            'book_verses' => BibleRepository::GetChapterVerses($book_abbr, $chapter_number)
        );

        View::share('book_abbr', $book_abbr);
        View::share('chapters', WidgetRepository::GetKJVChapterSelection($chapter_number, count(BibleRepository::GetBookChapters($book_abbr))));
        View::share('letters', WidgetRepository::GetConcordanceSelection());

        return View::make('layouts.master', $template_data)
            ->nest('heading', 'headings.chapter-index')
            ->nest('top_nav', 'widgets.chapter-selection-kjv-top')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'bible.book-chapter', $content_data);
    }

}
