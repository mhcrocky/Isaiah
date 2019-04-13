<?php

class TestimonialController extends \BaseController {
	private $thread;

	public function __construct() {
		parent::__construct();

		if(!App::environment('local', 'staging')) {
			$this->thread = '3664297995';
		} else {
			$this->thread = '3669612850';
		}
	}

	/**
	 * @return Illuminate\View\View
	 */
	public function GetTestimonialForm() {
		$template_data = array(
			'title' 	=> 'Testimonials',
			'body_id' 	=> 'chapter-index',
			'body_css' 	=> 'scriptures section-heading'
		);

		if(!Request::isMethod('post')) {
			$prev = json_decode($this->GetInputValue('prev'));
			$next = json_decode($this->GetInputValue('next'));
			$direction = json_decode($this->GetInputValue('direction'));
		} else {
			$inputData = Input::get('formData');
			parse_str($inputData, $formFields);
			$prev = $formFields['prev'];
			$next = $formFields['next'];
			$direction = $formFields['direction'];
		}

		$testimonials = TestimonialRepository::GetDisqusTestimonials($this->thread, $next, $prev, $direction);

		if(!empty($testimonials['prevCursor'])) {
			$prev = $testimonials['prevCursor'];
		} else {
			$prev = '';
		}

		if(!empty($testimonials['nextCursor'])) {
			$next = $testimonials['nextCursor'];
		} else {
			$next = '';
		}

		if(!Request::isMethod('post')) {
			$content_data = array(
				'testimonials' 	=> $testimonials['comments'],
				'prev'			=> $prev,
				'next' 			=> $next
			);

			return View::make('layouts.master', $template_data)
				->nest('tracking_code', 'widgets.tracking-code')
				->nest('heading', 'headings.chapter-index')
				->nest('mobile_search', 'widgets.search-iit-mobile')
				->nest('content', 'testimonial-index', $content_data);
		} else {
			return Response::json(array(
				'success' => true,
				'testimonials' 	=> json_encode($testimonials['comments']),
				'prev'			=> json_encode($prev),
				'next'			=> json_encode($next)
			));
		}
	}

	/**
	 * @return Illuminate\View\View
	 */
	public function SubmitTestimonialForm() {
		$template_data = array(
			'title' 	=> 'Testimonials',
			'body_id' 	=> 'chapter-index',
			'body_css' 	=> 'scriptures section-heading'
		);

		//$content_data = [];

		$prev = json_decode($this->GetInputValue('prev'));
		$next = json_decode($this->GetInputValue('next'));
		$direction = json_decode($this->GetInputValue('direction'));

		$rules = array (
			'full_name' 	=> 'required',
			'email' 		=> 'required|email',
			//'subject' 	=> 'required',
			'phone_number' 	=> array('Regex:/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/'),
			'body' 			=> 'required|min:5'
		);

		$input_data = Input::all();
		//$content_data = ['input_data' => $input_data, 'prev' => $prev, 'next' => $next];
		$validator = Validator::make ($input_data, $rules);

		$testimonials = TestimonialRepository::GetDisqusTestimonials($this->thread, $next, $prev, $direction);
		$comments = $testimonials['comments'];
		$next = $testimonials['nextCursor'];

		if ($validator->passes()){
			$result = TestimonialRepository::CreateDisqusPost($input_data, $this->thread, $this->app_url);

			Mail::send('emails.testimonial', $input_data, function($message) use ($input_data)
			{
				$message->from($input_data['email'], $input_data['full_name']);
				$message->to(Config::get('app.contact_email'))->subject(Config::get('app.testimonial_subject'));
				$message->setBody($input_data['body']);
			});
			$content_data = [
				'message' 		=> 'Your testimonial has been submitted. Thank You!',
				'testimonials' 	=> $comments,
				'prev' 			=> $prev,
				'next' 			=> $next
			];
			return View::make('layouts.master', $template_data)
				->nest('heading', 'headings.resources')
				->nest('content', 'testimonial-index', $content_data);
		}else{
			$content_data = [
				'errors' 		=> $validator->messages(),
				'input_data' 	=> $input_data,
				'testimonials' 	=> $comments,
				'prev'			=> $prev,
				'next'			=> $next
			];
			return View::make('layouts.master', $template_data)
				->nest('tracking_code', 'widgets.tracking-code')
				->nest('heading', 'headings.resources')
				->nest('content', 'testimonial-index', $content_data);
		}
	}

	/**
	 * @param $input_key string Key value from Input array
	 * @return string
	 */
	public function GetInputValue($input_key)
	{
		return Input::get($input_key, '');
	}
}
