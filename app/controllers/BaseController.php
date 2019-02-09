<?php

class BaseController extends Controller {

    protected $app_url = '';

    public function __construct()
    {
        //parent::__construct();
        $this->app_url = Config::get('app.url');
        View::share('app_url', $this->app_url);
    }

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

}
