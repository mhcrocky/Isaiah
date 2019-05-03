<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BuildSitemap extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'site:map';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Build sitemap based on site crawling (excludes hash tags and url parameters).';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        /* @var $sitemap \Roumen\Sitemap\Sitemap */
        $sitemap = App::make("sitemap");

        $message = '<info>%s</info>';

        $root_option = $this->option('root');
        if(!empty($root_option)) {
            $root = $root_option;
        } else {
            $root = Config::get('app.url');
        }

        $canonical_option = $this->option('all-canonical');
        if(!empty($canonical_option)) {
            $is_all_canonical = boolval((bool)$canonical_option);
            $this->output->writeln(sprintf($message, 'Running all-canonical.'));
        } else {
            $is_all_canonical = false;
            $this->output->writeln(sprintf($message, 'Running non-canonical.'));
        }

        $crawler = new Crawler($this->laravel, $this->laravel, $this->output, $root, $is_all_canonical);
        $crawler->crawlPages();

        $iterativeQueue = new RecursiveArrayIterator($crawler->crawled);
        //$this->output->writeln(sprintf($message, 'Iterator created.'));
        unset($queue);
        //$this->info('Unset original queue array.');
        //$this->output->writeln(sprintf($message, 'Unset original queue array.'));

        $lastMonth = Helpers\getLastMonth();

        $link_count = 0;

        foreach(new RecursiveIteratorIterator($iterativeQueue) as $key => $url) {
            if(!in_array($url, $crawler->queue_errors)) {
                if($url != '/') {
                    $sitemap->add(URL::to($url), $lastMonth, '1.0', 'monthly');
                    //$this->info("Added {$url} to sitemap.");
                    $this->output->writeln(sprintf($message, "Added {$url} to sitemap."));
                    ++$link_count;
                } else {
                    $this->output->writeln(sprintf($message, "Skipped {$url} in sitemap."));
                }
            } else {
                //$this->info("Skipped {$url} due to errors.");
                $this->output->writeln(sprintf($message, "Skipped {$url} due to errors."));
            }
        }

        $this->output->writeln(sprintf($message, 'Saving sitemap...'));

        $sitemap->store();

        //$this->info('Sitemap generated successfully!');
        $this->output->writeln(sprintf($message, 'Sitemap generated successfully! Count: ' . $link_count));

        return true;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			//array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() {
		return array(
            array('root', 'r', InputOption::VALUE_OPTIONAL, 'A root URL to be used when visiting', null),
            array('all-canonical', 'c', InputOption::VALUE_OPTIONAL, 'All links handled canonically (e.g. strips # and ?) if set', null),
		);
	}

    private function getIsBlacklisted($url) {
        if(in_array($url, $this->_blacklisted)) {
            return true;
        } else {
            return false;
        }
    }
}
