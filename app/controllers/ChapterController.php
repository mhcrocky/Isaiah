<?php

class ChapterController extends BaseController {

    public function showChapter($chapter_number)
    {
        $template_data = array(
            'title' => "Isaiah ${chapter_number}",
            'body_id' => 'chapters',
            'body_css' => 'scriptures tab-heading'
        );

        $chapterRepository = new ChapterRepository();
//<a href="#defmodal" class="modal-trigger def-trigger red" data-toggle="modal">razor</a>
        $content_data = array(
            'chapter_heading' => $chapterRepository->GetChapterHeading($chapter_number),
            'iit_html' => $chapterRepository->GetIITChapter($chapter_number),
            'three_col_html' => $chapterRepository->GetThreeColHtml($chapter_number),
            'commentary_html' => $chapterRepository->GetIITCommentary($chapter_number),
            'concordance_html' => $chapterRepository->GetIITConcordance($chapter_number),
            'nav_links_light_left' => IITPaginator::GetNav($chapter_number, 'left', 'nav-links-light'),
            'nav_links_light_right' => IITPaginator::GetNav($chapter_number, 'right', 'nav-links-light')
        );

        $header_data = array(
            'heading_nav_left' => IITPaginator::GetNav($chapter_number, 'left', 'heading-chapters'),
            'heading_nav_right' => IITPaginator::GetNav($chapter_number, 'right', 'heading-chapters')
        );

        $verse_modal_data = array(
            'verse_count' => $chapterRepository->verse_count
        );

        View::share('chapter_number', $chapter_number);
        View::share('chapters', WidgetRepository::GetChapterSelection($chapter_number));
        VIEW::share('footnotes', $chapterRepository->GetIITFootnotesList($chapter_number));

        return View::make('layouts.master', $template_data)
            ->nest('top_nav', 'widgets.chapter-selection-top')
            ->nest('bottom_nav', 'widgets.chapter-selection-bottom')
            ->nest('verse_modal', 'modals.verse', $verse_modal_data)
            ->nest('chapter_modal', 'modals.chapter')
            ->nest('keyword_modal', 'modals.keyword')
            ->nest('heading', 'headings.chapter', $header_data)
            ->nest('content', 'chapter', $content_data);
    }

}