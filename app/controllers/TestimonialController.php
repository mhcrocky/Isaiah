<?php

class TestimonialController extends \BaseController {
	private $thread = '3669612850';

	/**
	 * @return Illuminate\View\View
	 */
	public function GetTestimonialForm() {
		$template_data = array(
			'title' => 'Testimonials',
			'body_id' => 'chapter-index',
			'body_css' => 'scriptures section-heading'
		);

		$comments = $this->GetDisqusTestimonials();

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
			$result = TestimonialRepository::CreateDisqusPost($input_data, $this->app_url);

			Mail::send('emails.testimonial', $input_data, function($message) use ($input_data)
			{
				$message->from($input_data['email'], $input_data['full_name']);
				$message->to(Config::get('app.contact_email'))->subject(Config::get('app.testimonial_subject'));
				$message->setBody($input_data['body']);
			});
			$content_data = [];
			$content_data['message'] = 'Your testimonial has been submitted. Thank You!';
			return View::make('layouts.master', $template_data)
				->nest('heading', 'headings.resources')
				->nest('content', 'testimonial-index', $content_data);
		}else{
			$content_data['errors'] = $validator->messages();
			return View::make('layouts.master', $template_data)
				->nest('tracking_code', 'widgets.tracking-code')
				->nest('heading', 'headings.resources')
				->nest('content', 'testimonial-index', $content_data);
		}
	}
}
