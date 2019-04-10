<?php

class StoreController extends \BaseController {
	/**
	 * @return \Illuminate\View\View
	 */
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
}
