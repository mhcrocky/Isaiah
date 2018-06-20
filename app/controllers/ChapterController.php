<?php

class ChapterController extends BaseController {

    public function showChapter($chapter_number)
    {
        $template_data = array(
            'title' => "Isaiah ${chapter_number}",
            'body_id' => 'chapters',
            'body_css' => 'scriptures tab-heading'
        );

        $section = (Input::has('section') ? Input::get('section') : 'one_col');

        $content_data = array(
            'chapter_heading' => ChapterRepository::GetChapterHeading($chapter_number),
            'iit_html' => 'TEST IIT HTML',
            'nav_links_light_left' => IITPaginator::GetNav($chapter_number, 'left', 'nav-links-light', $section),
            'nav_links_light_right' => IITPaginator::GetNav($chapter_number, 'right', 'nav-links-light', $section)
        );

        $header_data = array(
            'heading_nav_left' => IITPaginator::GetNav($chapter_number, 'left', 'heading-chapters', $section),
            'heading_nav_right' => IITPaginator::GetNav($chapter_number, 'right', 'heading-chapters', $section)
        );

        View::share('chapter_number', $chapter_number);
        View::share('chapters', WidgetRepository::GetChapterSelection($chapter_number));

        return View::make('layouts.master', $template_data)
            ->nest('verse_modal', 'modals.verse')
            ->nest('chapter_modal', 'modals.chapter')
            ->nest('keyword_modal', 'modals.keyword')
            ->nest('heading', 'headings.chapter', $header_data)
            ->nest('content', 'chapter', $content_data);
    }

}