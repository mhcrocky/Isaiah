<?php

class ChapterController extends BaseController {

    public function showChapter($chapter_number)
    {
        $template_data = array(
            'title' => "Isaiah ${chapter_number}",
            'body_id' => 'chapters',
            'body_css' => 'scriptures tab-heading'
        );
        $content_data = array(
            //'widget_chapter_sel' => WidgetRepository::GetChapterSelection($chapter_number),
            'chapter_heading' => ChapterRepository::GetChapterHeading($chapter_number),
            'iit_html' => 'TEST IIT HTML'
        );
        View::share('chapter_number', $chapter_number);
        View::share('chapters', WidgetRepository::GetChapterSelection($chapter_number));
        return View::make('layouts.master', $template_data)
            ->nest('verse_modal', 'modals.verse')
            ->nest('chapter_modal', 'modals.chapter')
            ->nest('keyword_modal', 'modals.keyword')
            ->nest('heading', 'headings.chapter')
            ->nest('content', 'chapter', $content_data);
    }

}