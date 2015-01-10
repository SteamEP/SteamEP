<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CompareScrape extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'scrape:import';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Compare scraped items.';

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
		$this->info("Comparing items from table scraped_items to items from table steam_items");
		$allScraped = DB::table('scraped_items')->get();

		$foundGames = 0;
		$foundItems = 0;
		
		foreach ($allScraped as $scraped) {
			$countInLive = DB::table('steam_games')->where('appid', $scraped->appid)->count();
			if ($countInLive < 1) {
				$foundGames += 1;
				$this->info("Found new game (" . $scraped->gamename . " with appid " . $scraped->appid . "), adding to database");
				DB::table('steam_games')->insert(array('appid' => $scraped->appid, 'name' => $scraped->gamename));
			}
		}
		foreach ($allScraped as $scraped) {
			$countInLive = DB::table('steam_items')->where('appid', $scraped->appid)->where('name', $scraped->itemname)->where('type', $scraped->type)->count();
			if ($countInLive < 1) {
				$foundItems += 1;
				$this->info("Found new item (" . $scraped->itemname . " for " . $scraped->gamename .", type " . $scraped->type . "), adding to database");
				DB::table('steam_items')->insert(array('type' => $scraped->type, 'name' => $scraped->itemname, 'image_base64' => $scraped->image_base64 , 'appid'=> $scraped->appid, 'series'=>1));
			} else if ($this->argument('update') == 'update' && $countInLive == 1) {
				DB::table('steam_items')->where('appid', $scraped->appid)->where('name', $scraped->itemname)->where('type', $scraped->type)->update(array('image_base64' => $scraped->image_base64));
				$this->info("Item " . $scraped->itemname . " for " . $scraped->gamename . ", type " . $scraped->type . " is already in the database and has been updated with a new image.");	
			}
			DB::table('scraped_items')->where('id', $scraped->id)->delete();
		}
		$this->info("-------");
		$this->info("Found " . $foundGames . " new games and " . $foundItems . " new items!");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments() {
		return array(
			array(
				'update',
				InputArgument::OPTIONAL,
				'Update current images.'
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
