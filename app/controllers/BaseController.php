<?php

class BaseController extends Controller {

    protected $app_url = '';
	protected $app_domain = '';

    public function __construct()
    {
        //parent::__construct();
        $this->app_url = Config::get('app.url');
		$this->app_domain = Config::get('app.domain');
        View::share('app_url', $this->app_url);
		View::share('app_domain', $this->app_domain);
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
