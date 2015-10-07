<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ScrapeMisc extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'scrape:misc';

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
	protected $description = 'Scrape emoticons/backgrounds from steam.tools.';

	protected $games = array();

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		include('app/libraries/simplehtmldom.php');
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() {
		$this->info("Starting scrape.");
		$this->scrape("emote");
		$this->scrape("bg");
		$this->info("Scraped a total of " . $this->itemCount . " items.");
	}

	public function scrape($type) {
		$this->info("Scraping: " . $type);
		$itemJSON = json_decode(file_get_contents("http://cdn.steam.tools/data/" . $type . ".json"));

		$games = array();
		
		foreach ($itemJSON as $item) {
			$tmp = explode("/", $item->url);
			$tmp = explode("-", $tmp[1]);
			$appid = $tmp[0];
			if (!array_key_exists($appid, $games)) {
				$games[$appid] = array();
			}
			$games[$appid][] = $item;
		}

		$type = $type == "emote" ? 4 : 5;

		foreach ($games as $appid=>$items) {
			$names = DB::table('steam_items')->where('appid', $appid)->where('type', $type)->lists('name');
			$alreadyScraped = DB::table('scraped_items')->where('type', $type)->lists('itemname');
			
			if (count($names) == count($items)) {
				continue;
			}
			foreach ($items as $item) {
				$item->game = str_replace(' Rare', '', $item->game); 
				$item->game = str_replace(' Uncommon', '', $item->game); 
				if (in_array($item->name, $alreadyScraped)) {
					continue;
				}
				if (in_array($item->name, $names)) {
					continue;
				}
				if (!isset($item->img)) {
					$base64 = @file_get_html("http://steamcommunity.com/market/listings/" . $item->url);
					if ($base64 != null) {
						$base64 = $base64->find('div.market_listing_largeimage img', 0);
						if ($base64 == null) {
							continue;
						}
						$base64 = str_replace('http://cdn.steamcommunity.com/economy/image/', '', $base64->src);
						$base64 = str_replace('/360fx360f', '', $base64);
					} else {
						$this->info("Problem! 403?");
						continue;
					}
				} else {
					$base64 = $item->img;
				}

				DB::table('scraped_items')->insert(array('appid' => $appid, 'itemname' => $item->name, 'gamename' => $item->game, 'type' => $type, 'image_base64' => $base64));
				$this->itemCount++;
			}
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
