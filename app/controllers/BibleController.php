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
            'body_css' => 'bible scriptures tab-heading'
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
            ->nest('heading', 'headings.bible-chapter-index')
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
            'body_css' => 'bible scriptures tab-heading'
        );

        $content_data = array(
            'book_title' => $title,
            'book_abbr' => $book_abbr,
            'book_chapters' => BibleRepository::GetBookChapters($book_abbr)
        );

        View::share('chapters', WidgetRepository::GetKJVChapterSelection(0, count(BibleRepository::GetBookChapters($book_abbr))));
        View::share('letters', WidgetRepository::GetConcordanceSelection());

        return View::make('layouts.master', $template_data)
            ->nest('heading', 'headings.bible-chapter-index')
            //->nest('top_nav', 'widgets.chapter-selection-top')
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
        $book_title = BibleRepository::GetBookTitleFromAbbr($book_abbr);

        $template_data = array(
            'title' => "{$book_title} {$chapter_number}",
            'body_id' => 'book-index',
            'body_css' => 'bible scriptures tab-heading'
        );

        $content_data = array(
            'book_verses' => BibleRepository::GetChapterVerses($book_abbr, $chapter_number),
            'nav_links_light_left' => KJVPaginator::GetNav($book_abbr, $chapter_number, 'left', 'nav-links-light'),
            'nav_links_light_right' => KJVPaginator::GetNav($book_abbr, $chapter_number, 'right', 'nav-links-light')
        );

        $header_data = array(
            'heading_nav_left' => KJVPaginator::GetNav($book_abbr, $chapter_number, 'left', 'heading-chapters'),
            'heading_nav_right' => KJVPaginator::GetNav($book_abbr, $chapter_number, 'right', 'heading-chapters')
        );

        View::share('book_title', $book_title);
        View::share('chapter_number', $chapter_number);
        View::share('book_abbr', $book_abbr);
        View::share('chapters', WidgetRepository::GetKJVChapterSelection($chapter_number, count(BibleRepository::GetBookChapters($book_abbr))));
        View::share('letters', WidgetRepository::GetConcordanceSelection());

        return View::make('layouts.master', $template_data)
            //->nest('heading', 'headings.bible-chapter')
            //->nest('top_nav', 'widgets.chapter-selection-top-kjv-book-chapter')
            ->nest('chapter_modal', 'modals.bible-chapter')
            ->nest('heading', 'headings.bible-chapter', $header_data)
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'bible.book-chapter', $content_data);
    }

}
