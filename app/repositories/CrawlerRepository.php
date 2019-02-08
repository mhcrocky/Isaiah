<?php

//use DOMElement;
//use Exception;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\Client;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Crawler
{
    private $_blacklisted = [
        'sitemap.xml'
    ];

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * The HttpKernel client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * An array of the application's pages
     *
     * @var array
     */
    protected $queue = [
        '/' => false,
    ];

    /**
     * An array of the application's queued pages with errors
     *
     * @var array
     */
    public $queue_errors = [];

    /**
     * The current page being crawled
     *
     * @var integer
     */
    protected $current;

    /**
     * An array of the pages already crawled
     *
     * @var array
     */
    public $crawled = array();

    /**
     * The root URL
     *
     * @var string
     */
    protected $root;

    protected $excluded = array('#', '?');

    public function __construct(Container $app, HttpKernelInterface $kernel, OutputInterface $output, $root = null, $is_all_canonical = true) {
        $this->app = $app;
        $this->output = $output;
        $this->client = new Client($kernel);
        $this->root   = $root ?: $this->app['config']->get('app.url');
    }

    /**
     * Crawl the pages in the queue
     *
     * @param int $iteration
     * @return int
     */
    public function crawlPages($iteration = 0)
    {
        $message = '<info>%s</info>';

        $pages = array_keys($this->queue);

        foreach ($pages as $key => $page) {
            if (in_array($page, $this->crawled)) {
                continue;
            }
            // Try to display the page
            // Cancel if not found
            $this->current = $key;
            $this->crawlPage($page);
            //$this->queue[$page] = true;
        }

        // Recursive call
        if ($this->hasPagesToCrawl()) {
            ++$iteration;
            //$this->output->writeln(sprintf($message, "Recursion #{$iteration} start..."));
            $this->crawlPages($iteration);
            //$this->output->writeln(sprintf($message, "Recursion #{$iteration} end."));
        } else {
            //$this->output->writeln(sprintf($message, 'All pages crawled.'));
        }

        return true;
        //return count($this->queue);
        //return ['queue' => $this->queue, 'queue_errors' => $this->queue_errors];
    }
    /**
     * Crawl an URL and extract its links
     *
     * @param string $page The page's URL
     *
     * @return false|null
     */
    protected function crawlPage($page)
    {
        // Mark page as crawled
        $this->crawled[] = $page;

        try {
            if (!$crawler = $this->getPage($page)) {
                $this->queue_errors[] = $page;
                return false;
            } else {
                //$this->queue[$page] = true;
            }
        } catch (Exception $e) {
            return $this->error('Page "'.$page.'" errored : '.$e->getMessage());
        }

        // Extract new links
        $this->extractLinks($crawler);
    }

    /**
     * Check if the Crawler still has pages to crawl
     *
     * @return boolean
     */
    protected function hasPagesToCrawl()
    {
        foreach($this->queue as $page => $is_crawled) {
            if(!in_array($page, $this->crawled)) {
                return true;
            } else {
                continue;
            }
        }
        //return !empty($this->queue);
    }

    /**
     * Extract the various links from a page
     *
     * @param DomCrawler $crawler
     */
    protected function extractLinks(DomCrawler $crawler)
    {
        /* @var $link \DomElement */
        foreach ($crawler->filter('a') as $link) {
            if (!$this->isExternal($link)) {
                $is_new = true;
                $excluded = $this->excluded;
                $stripped_url = $link->getAttribute('href');
                foreach($excluded as $skip_char) {
                    $url_parts = explode($skip_char, $stripped_url);
                    $stripped_url = $url_parts[0];
                    if(in_array($stripped_url, $this->queue)) {
                        $is_new = false;
                    }
                }

                if($is_new == true) {
                    $link->setAttribute('href', $stripped_url);
                    $this->queueLink($link);
                }
            }
        }
    }

    /**
     * Add a Link to the list of pages to be crawled
     *
     * @param DOMElement $link
     */
    protected function queueLink(DOMElement $link)
    {
        $link = $link->getAttribute('href');
        // If the page wasn't crawled yet, crawl it
        if (!in_array($link, $this->crawled)) {
            $this->queue[$link] = false;
        }
    }

    /**
     * Check if a Link is external
     *
     * @param DOMElement $link
     *
     * @return boolean
     */
    protected function isExternal(DOMElement $link)
    {
        return !Str::startsWith($link->getAttribute('href'), $this->root);
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param string $url
     *
     * @return null|DomCrawler
     */
    protected function getPage($url)
    {
        $url = str_replace($this->root, null, $url);
        // Call page
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();
        if (!$response->isOk()) {
            return $this->error('Page at "'.$url.'" could not be reached');
        }
        // Format content
        $content = $response->getContent();
        $content = preg_replace('#(href|src)=([\'"])/([^/])#', '$1=$2'.$this->root.'/$3', $content);
        $content = str_replace($this->app['url']->to('/'), $this->root, $content);
        $content = utf8_decode($content);
        // Build message
        $status  = 'Crawled';
        $current = (count($this->queue) - $this->current);
        $padding = str_repeat(' ', 70 - strlen($url) - strlen($status));
        // Display message
        $message = $status.' <info>%s</info>%s<comment>(%s in queue)</comment>';
        $this->output->writeln(sprintf($message, $url, $padding, $current));
        return new DomCrawler($content);
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// OUTPUT //////////////////////////////
    ////////////////////////////////////////////////////////////////////
    /**
     * Write a string as error output.
     *
     * @param string $string
     */
    protected function error($string)
    {
        $this->output->writeln('<error>'.$string.'</error>');
    }
}