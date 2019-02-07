<?php

class ResourceController extends BaseController {

    public function showIndex() {
        $template_data = array(
            'title' => 'Resources',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        $content_data = array();

        return View::make('layouts.master', $template_data)
            ->nest('heading', 'headings.chapter-index')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'resource-index', $content_data);
    }

    public function findResource($resource) {
        $template_data = array(
            'title' => 'Resources',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        $content_template = 'resource-index';

        $content_data = array();

        switch($resource) {
            case 'key-features-of-the-prophecy-of-isaiah':
                $template_data['title'] = 'Key Features of the Prophecy of Isaiah';
                $content_template = 'resources.key-features-of-the-prophecy-of-isaiah';
                break;
            case 'isaiahs-layered-literary-structures':
                $template_data['title'] = 'Isaiah’s Layered Literary Structures';
                $content_template = 'resources.isaiahs-layered-literary-structures';
                break;
            case 'isaiahs-seven-spiritual-levels-of-humanity':
                $template_data['title'] = 'Isaiah’s Seven Spiritual Levels of Humanity';
                $content_template = 'resources.isaiahs-seven-spiritual-levels-of-humanity';
                break;
            case 'isaiahs-ancient-types-of-end-time-events':
                $template_data['title'] = 'Isaiah’s Ancient Types of End-Time Events';
                $content_template = 'resources.isaiahs-ancient-types-of-end-time-events';
                break;
            case 'overviews-of-the-prophecy-of-isaiah':
                $template_data['title'] = 'Overviews of the Prophecy of Isaiah';
                $content_template = 'resources.overviews-of-the-prophecy-of-isaiah';
                break;
            case 'glossary-of-terms-relating-to-isaiah':
                $template_data['title'] = 'Glossary of Terms Relating to Isaiah';
                $content_template = 'resources.glossary-of-terms-relating-to-isaiah';
                break;
            default:
                break;
        }
        return View::make('layouts.master', $template_data)
            ->nest('vignette_modal', 'modals.vignette')
            ->nest('heading', 'headings.resources')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', $content_template, $content_data);
    }
}