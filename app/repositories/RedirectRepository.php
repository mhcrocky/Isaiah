<?php

class RedirectRepository {
    public static function PageNotFound() {
        $redirect_url = RedirectRepository::GetRedirectURL(Request::getRequestUri(), Input::all());

        $app_url = Config::get('app.url');

        if(!empty($redirect_url)) {
            return Redirect::to($app_url . $redirect_url);
        } else {
            $template_data = array(
                'title' => 'Error 404 (Not Found)!',
                'body_id' => 'chapter-index',
                'body_css' => 'scriptures section-heading'
            );
            $content_data = array('uri' => '/' . Request::path());
            View::share('app_url', $app_url);
            return View::make('layouts.master', $template_data)
                ->nest('heading', 'headings.resources')
                ->nest('mobile_search', 'widgets.search-iit-mobile')
                ->nest('content', 'errors.missing', $content_data);
        }
    }

    public static function GetRedirectURL($uri, $input_data) {
        $redirect_url = '';

        if(Config::get('app.debug') == true) {
            $uri = preg_replace('/(\?|&)XDEBUG_SESSION_START=\d{1,}/', '', $uri);

            unset($input_data['XDEBUG_SESSION_START']);
        }

        if(!empty($input_data) && !empty($input_data['h'])) {
            $h = $input_data['h'];
        } else {
            $h = '';
        }

        if(preg_match('/^\/isaiah_ch_(\d{1,2})\.html/', $uri, $matches)) {
            if(!empty($matches[1])) {
                $chapter_number = ltrim($matches[1], '0');
                $redirect_url = "/{$chapter_number}#one_col";
            }
        } elseif(preg_match('/\/Concordance\/chapter(\d{1,2})\.html/i', $uri, $matches)) {
            if(!empty($matches[1])) {
                $chapter_number = $matches[1];
                if(preg_match('/c\d{1,2}v(\d{1,2})(a|b)?-(\w+)/', $h, $h_matches)) {
                    if(!empty($h_matches) && !empty($h_matches[1]) && !empty($h_matches[3])) {
                        $verse_number = $h_matches[1];
                        if(empty($h_matches[2])) {
                            $verse_number = $verse_number . 'a';
                        } else {
                            $verse_number = $verse_number . $h_matches[2];
                        }
                        $word = $h_matches[3];
                    }
                    $redirect_url = "/{$chapter_number}?citation=c{$chapter_number}v{$verse_number}-{$word}#concordance";
                }
            }
        } elseif(preg_match('/^\/Concordance\/concordance([A-Z])\.html/i', $uri, $matches)) {
            if(!empty($matches[1])) {
                $concordance_letter = $matches[1];
                if(preg_match('/c(\d{1,2})v(\d{1,2})(a|b)?-(\w+)/', $h, $h_matches)) {
                    if(!empty($h_matches) && !empty($h_matches[1]) && !empty($h_matches[2]) && !empty($h_matches[4])) {
                        $chapter_number = $h_matches[1];
                        $verse_number = $h_matches[2];
                        if(empty($h_matches[3])) {
                            $verse_number = $verse_number . 'a';
                        } else {
                            $verse_number = $verse_number . $h_matches[3];
                        }
                        $word = $h_matches[4];
                    }
                    $redirect_url = "/concordance/{$concordance_letter}?citation=c{$chapter_number}v{$verse_number}-{$word}#{$word}";
                }
            }
        } elseif(preg_match('/^\/Concordance\/title\.html/i', $uri)) {
            $redirect_url = '/Isaiah-Institute-Translation/';
        } elseif(preg_match('/^\/Concordance\/index\.html/i', $uri)) {
            $redirect_url = '/Concordance/';
        } else {
            $new_path = '/legacy' . urldecode($uri);
            $filename = public_path() . $new_path;
            if(file_exists($filename)) {
                $redirect_url = $new_path;
            }
        }

        return $redirect_url;
    }
}