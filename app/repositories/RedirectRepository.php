<?php

class RedirectRepository {
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
            $filename = public_path() . '/legacy' . urldecode($uri);
            //dd($filename);
            if(file_exists($filename)) {
                /*if(ends_with($filename, 'html')) {
                    $filesize = filesize($filename);
                    $fh = fopen($filename, 'r');
                    $html = fread($fh, $filesize);
                    $html = str_replace('"style.css"', '/legacy/style.css', $html);
                    $html = str_replace('../', '/legacy/', $html);
                    echo $html;*/
                    /*$doc = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $doc->loadHTMLFile($filename);
                    echo $doc->saveHTML();*/
                    /*exit;
                } else {
                    $redirect_url = $new_path;
                }*/
                $redirect_url = $new_path;
            }
            //dd($filename);
        }

        return $redirect_url;
    }

    /*private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }*/
}