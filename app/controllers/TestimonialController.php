<?php

class TestimonialController extends \BaseController {
	/**
	 * @return Illuminate\View\View
	 */
	public function GetTestimonialForm() {
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
		//$ch = curl_init();
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

	/**
	 * @return Illuminate\View\View
	 */
	public function SubmitTestimonialForm() {
		$template_data = array(
			'title' => 'Testimonials',
			'body_id' => 'chapter-index',
			'body_css' => 'scriptures section-heading'
		);

		//$content_data = [];

		$input_data = Input::all();

		$content_data = ['input_data' => $input_data];

		$rules = array (
			'full_name' => 'required',
			'email' => 'required|email',
			//'subject' => 'required',
			'phone_number' => array('Regex:/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/'),
			'body' => 'required|min:5'
		);

		$validator = Validator::make ($input_data, $rules);

		if ($validator->passes()){
			$author_email = urlencode($input_data['email']);
			$author_name = urlencode($input_data['full_name']);
			$message = urlencode($input_data['body']);

			//TODO: Submit to Disqus
			header("Host: {$this->app_domain}");
			header("Referer: {$this->app_url}");

			$fields = [
				'api_secret'	=> 'hUFhDILTYUsdL35aYgxZEZ3gbJuJ024I1ySlbS3AxjmJUAGK6gsHlvifF4EQVJjs',
				'message' 		=> $message,
				'thread' 		=> '3664297995',
				'author_email' 	=> $author_email,
				'author_name' 	=> $author_name
			];

			$endpoint = 'https://disqus.com/api/3.0/posts/create.json';

			$fields_string = '';
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');

			$session = curl_init($endpoint);
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($session,CURLOPT_URL, $endpoint);
			curl_setopt($session,CURLOPT_POST, count($fields));
			curl_setopt($session,CURLOPT_POSTFIELDS, $fields_string);

			$data = curl_exec($session);
			curl_close($session);

			// decode the json data to make it easier to parse with php
			$results = json_decode($data);

			dd($results);

			Mail::send('emails.testimonial', $input_data, function($message) use ($input_data)
			{
				//$message->from(Config::get('app.system_email'), $input_data['full_name']);
				$message->from($input_data['email'], $input_data['full_name']);
				$message->to(Config::get('app.contact_email'))->subject(Config::get('app.testimonial_subject'));
				$message->setBody($input_data['body']);
			});
			// Redirect to page
			/*return Redirect::route('contact')
                ->with('message', 'Your message has been sent. Thank You!');*/
			$content_data = [];
			$content_data['message'] = 'Your testimonial has been submitted. Thank You!';
			return View::make('layouts.master', $template_data)
				->nest('heading', 'headings.resources')
				->nest('content', 'contact', $content_data);
			//return View::make('contact');
		}else{
			//return contact form with errors
			/*return Redirect::route('contact')
                ->with('error', 'Feedback must contain more than 5 characters. Try Again.');*/
			$content_data['errors'] = $validator->messages();
			return View::make('layouts.master', $template_data)
				->nest('tracking_code', 'widgets.tracking-code')
				->nest('heading', 'headings.resources')
				->nest('content', 'contact', $content_data);
		}
	}
}
