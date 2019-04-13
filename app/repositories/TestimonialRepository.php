<?php

class TestimonialRepository {
    /**
     * @param $thread_id string Disqus thread Id
     * @param $prev string Json array of previous cursor values
     * @param $next string Disqus next cursor value
     * @param $direction string 'next' if foward and 'prev' if backwards
     * @return mixed
     */
    public static function GetDisqusTestimonials($thread_id, $next = '', $prev = '', $direction = 'next')
    {
        $key = 'hUFhDILTYUsdL35aYgxZEZ3gbJuJ024I1ySlbS3AxjmJUAGK6gsHlvifF4EQVJjs'; // TODO replace with your Disqus secret key from http://disqus.com/api/applications/
        $forum = 'isaiah-explained'; // Disqus shortname
        $limit = '5'; // The number of comments you want to show
        $thread = $thread_id; // Same as your disqus_identifier

        $endpoint = 'https://disqus.com/api/3.0/threads/listPosts.json?api_secret=' . $key . '&forum=' . $forum . '&thread=' . $thread . '&limit=' . $limit;

        if($direction == 'next') {
            if(!empty($next)) {
                $endpoint .= "&cursor={$next}";
            }
        } else {
            if(!empty($prev)) {
                $endpoint .= "&cursor={$prev}";
            }
        }

        // Get the results
        $session = curl_init($endpoint);
        //$ch = curl_init();
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($session);
        curl_close($session);

        // decode the json data to make it easier to parse with php
        $results = json_decode($data);

        $next = '';

        if (!empty($results->cursor)) {
            $cursor = $results->cursor;
            if (!empty($cursor->hasNext)) {
                $next = $cursor->next;
            }
            if (!empty($cursor->hasPrev)) {
                $prev = $cursor->prev;
            }
        }

        if(!empty($prev)) {
            $testimonials['prevCursor'] = $prev;
        } else {
            $testimonials['prevCursor'] = '';
        }

        if(!empty($next)) {
            $testimonials['nextCursor'] = $next;
        } else {
            $testimonials['nextCursor'] = '';
        }

        // parse the desired JSON data into HTML for use on your site
        $comments = $results->response;

        $comment_count = count($comments);

        for ($i = 0; $i < $comment_count; $i++) {
            $message = $comments[$i]->message;
            $message = str_ireplace('<p>', '', $message);
            $message = str_ireplace('</p>', '', $message);
            $template_match_result = preg_match_all('/###(.+?)###/', $message, $m);
            if(!empty($template_match_result) && $template_match_result == 1) {
                $citation = $m[1][0];
                $comments[$i]->message = preg_replace('/###.*/', '', $message);
                $comments[$i]->author->name = $citation;
            } else {
                $comments[$i]->message = $message;
            }
            //dd(htmlentities($comment->message));
        }

        /*foreach($comments as $comment) {
            $comment->message = str_ireplace('<p>','', $comment->message);
            $comment->message = str_ireplace('</p>','', $comment->message);
            //dd(htmlentities($comment->message));
        }*/

        //dd($comments);
        $testimonials['comments'] = $comments;

        return $testimonials;
    }

    /**
     * @param $input_data
     */
    public static function CreateDisqusPost($input_data, $thread_id, $api_url)
    {
        $author_email = urlencode($input_data['email']);
        $author_name = urlencode($input_data['full_name']);
        $message = urlencode($input_data['body']);

        //TODO: Submit to Disqus

        $fields = [
            'api_key' => 'E8Uh5l5fHZ6gD8U3KycjAIAk46f68Zw7C6eW8WSjZvCLXebZ7p0r1yrYDrLilk2F',
            'message' => $message,
            'thread' => $thread_id,
            'author_email' => $author_email,
            'author_name' => $author_name
        ];

        $endpoint = 'https://disqus.com/api/3.0/posts/create.json';

        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        header("Host: .disqus.com");
        //header("Host: {$this->app_domain}");

        $referrer = $api_url;
        header("Referer: {$referrer}");

        $session = curl_init($endpoint);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_URL, $endpoint);
        curl_setopt($session, CURLOPT_POST, count($fields));
        curl_setopt($session, CURLOPT_POSTFIELDS, $fields_string);

        $data = curl_exec($session);
        curl_close($session);

        //$results->response->raw_message etc. For some reason, response->author_email doesn't come back with anon comment from Disqus

        // decode the json data to make it easier to parse with php
        return json_decode($data);
    }
}