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
            ->nest('tracking_code', 'widgets.tracking-code')
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
            ->nest('tracking_code', 'widgets.tracking-code')
            ->nest('vignette_modal', 'modals.vignette')
            ->nest('heading', 'headings.resources')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', $content_template, $content_data);
    }

    public function showStore() {
        $template_data = array(
            'title' => 'Store',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        $content_data = array(
            'store_data' => ''
        );

        return View::make('layouts.master', $template_data)
            ->nest('tracking_code', 'widgets.tracking-code')
            ->nest('heading', 'headings.chapter-index')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'store', $content_data);
    }

    public function showTestimonials() {
        $template_data = array(
            'title' => 'Testimonials',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        $key='hUFhDILTYUsdL35aYgxZEZ3gbJuJ024I1ySlbS3AxjmJUAGK6gsHlvifF4EQVJjs'; // TODO replace with your Disqus secret key from http://disqus.com/api/applications/
        $forum='isaiah-explained'; // Disqus shortname
        $limit='5'; // The number of comments you want to show
        $thread='3664297995'; // Same as your disqus_identifier
        $endpoint = 'https://disqus.com/api/3.0/threads/listPosts.json?api_secret='.$key.'&forum='.$forum.'&thread='.$thread.'&limit='.$limit;
        //$endpoint = 'http://disqus.com/';

        // Get the results
        $session = curl_init($endpoint);
        $ch = curl_init();
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($session);
        curl_close($session);

        // decode the json data to make it easier to parse with php
        $results = json_decode($data);

        // parse the desired JSON data into HTML for use on your site
        $comments = $results->response;

        $comment_count = count($comments);

        for($i = 0; $i < $comment_count; $i++) {
            $message = $comments[$i]->message;
            $message = str_ireplace('<p>','', $message);
            $message = str_ireplace('</p>','', $message);
            $comments[$i]->message = $message;
            //dd(htmlentities($comment->message));
        }

        /*foreach($comments as $comment) {
            $comment->message = str_ireplace('<p>','', $comment->message);
            $comment->message = str_ireplace('</p>','', $comment->message);
            //dd(htmlentities($comment->message));
        }*/

        //dd($comments);

        $content_data = array(
            'testimonials' => $comments
        );

        return View::make('layouts.master', $template_data)
            ->nest('tracking_code', 'widgets.tracking-code')
            ->nest('heading', 'headings.chapter-index')
            ->nest('mobile_search', 'widgets.search-iit-mobile')
            ->nest('content', 'testimonial-index', $content_data);
    }
}