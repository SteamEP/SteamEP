<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ScrapeCards extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'scrape:cards';

	/**
	 * Scraped item count.
	 *
	 * @var integer
	 */
	protected $itemCount = 0;

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scrape cards from steam.';

	protected $games = array();

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() {
		$this->info("Starting scrape.");
		if (strlen($this->argument('appid')) > 2) {
			$this->info("Processing APPID " . $this->argument('appid') . ".");
			$this->singleGame($this->argument('appid'));	
		} else {
			$this->scrapeApps();
		}
	}

	public function scrapeApps() {
		$firstPage = file_get_html("http://store.steampowered.com/search/results?category1=998&category2=29&sort_ord");
		$pagesHTML = $firstPage->find('div.search_pagination_left', 0)->plaintext;
		preg_match("/showing \d+ - (?P<perPage>\d+) of (?P<total>\d+)/i", trim($pagesHTML), $matches);
		$totalPages = ceil($matches['total'] / $matches['perPage']);
		
		$this->info("Total of ".$totalPages." pages.");
		for ($page = 1; $page <= $totalPages; $page++) {
			if ($page == 1) $pageHTML = $firstPage;
			else $pageHTML = file_get_html("http://store.steampowered.com/search/results?category1=998&category2=29&sort_ord&page=" . $page);
			$this->processPage($pageHTML);
		}
		
		$this->info("There are a total of " . count($this->games) . " appIDs to be crawled.");
		
		foreach ($this->games as $game) {
			$this->singleGame($game);
		}
		$this->info("Scraped a total of " . $this->itemCount . " items.");
	}

	public function processPage($html) {
		foreach ($html->find('div.search_capsule') as $game) {
			$appID = explode("/", explode("/apps/", $game->find('img', 0)->src)[1])[0];
			if (Game::where('appid', $appID)->count() == 0) {
				$this->games[] = $appID;
			}
		}
	}

	public function singleGame($game) {
		$page = file_get_html("http://steamcommunity.com/id/elinea/gamecards/" . $game);
        if ($page->find('title', 0)->plaintext != 'Steam Community :: Steam Badges') {
    	    $gameName = trim(str_replace(array(" Foil Badge", " Badge", "\t"), "", $page->find('div.badge_title', 0)->plaintext));
            $this->processGame($page, $game, $gameName, 2);
            $this->processGame(file_get_html("http://steamcommunity.com/id/elinea/gamecards/" . $game . "?border=1"), $game, $gameName, 3);
        }
	}

	public function processGame($page, $appid, $gameName, $type) {
		foreach ($page->find('div.badge_card_set_card') as $card) {
			$img = explode("/image/", $card->find("img.gamecard", 0)->src)[1];
			$name = str_replace("\t", "", trim($card->find("div.badge_card_set_text", 0)->plaintext));
			DB::table('scraped_items')->insert(array(
				'itemname' => $name,
				'gamename' => $gameName,
				'appid' => $appid,
				'type' => $type,
				'image_base64' => $img));
			$this->itemCount++;
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments() {
		return array(
			array(
				'appid',
				InputArgument::OPTIONAL,
				'An appid.'
			)
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() {
		return array(
		);
	}

}
