<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 1.0
 *
 * TODO: Never finished, contains methods copy pasted from ListController.
 */
class LibraryController extends BaseController {

	public function __construct() {
		parent::__construct();
	}

	public function getList($appid = false)
	{
		// Store everything in an object because I like objects
		$data = new stdClass();

		if($appid)
		{
			$data = $this->getItemList($appid);
		}
		else
		{
			$data = $this->getGameList();
		}

		return $data;
	}

	private function getGameList($filter = "sort-newest")
	{
		$allowedFilters = array("sort-newest" => "appid", "sort-oldest" => "id ASC");
		if(!array_key_exists($filter, $allowedFilters))
		{
			$filter = "sort-newest";
		}

		$games = Game::orderBy($allowedFilters[$filter])->remember(10)->get();		
		$title = "Game Library";
		$totalGameCount = $games->count();

		$slice = 0;
		if(Input::has("page"))
		{
			$slice = Input::get('page') * 15 - 15;
		}
		$games = array_slice($games->toArray(), $slice, 15);

		$itemForGames = Item::gameIDs(array_column($games, "appid"))->get();
		$itemInfo = array();
		foreach($itemForGames as $item){
			if(!array_key_exists($item->appid, $itemInfo)){
				$itemInfo[$item->appid] = new stdClass();
				$itemInfo[$item->appid]->normal_card_count = 0;
				$itemInfo[$item->appid]->foil_card_count = 0;
				$itemInfo[$item->appid]->emoticon_count = 0;
				$itemInfo[$item->appid]->background_count = 0;
			}
			if($item->type == 2){
				$itemInfo[$item->appid]->normal_card_count = isset($itemInfo[$item->appid]->normal_card_count) ? $itemInfo[$item->appid]->normal_card_count+1 : 1;
			}	
			if($item->type == 3){
				$itemInfo[$item->appid]->foil_card_count = isset($itemInfo[$item->appid]->foil_card_count) ? $itemInfo[$item->appid]->foil_card_count+1 : 1;
			}	
			if($item->type == 4){
				$itemInfo[$item->appid]->emoticon_count = isset($itemInfo[$item->appid]->emoticon_count) ? $itemInfo[$item->appid]->emoticon_count+1 : 1;
			}	
			if($item->type == 5){
				$itemInfo[$item->appid]->background_count = isset($itemInfo[$item->appid]->background_count) ? $itemInfo[$item->appid]->background_count+1 : 1;
			}	
		}


		return View::make('library.games')->with(
			array(
				"paginator" => Paginator::make(range(1, $totalGameCount), $totalGameCount, 15),
				"title"		=> $title,
				"games"		=> $games,
				"itemInfo" 	=> $itemInfo
			)
		);
	}	

	// Retrieve a list of games that match the searched string where:
	//      - There's a game with a name like $searchString
	//      - There's a game where game_id = $searchString
	//      - There's an item for the game where name like $searchString 
	public function getGamesByName($searchString)
	{
		// Use the Game::search() method to retrieve a list of items
		$games = Game::search($searchString);
		return $games;
	}

	public function getItemsForAppIDs($appids, $type)
	{
		// Get properties for the current user so we can check what items he owns / needs
		$user = User::find(Auth::user()->id);

		// Create a list of items with the correct type for games with appid in $appids
		$items = Item::type($type)->gameIDs($appids)->joinWith($user)->get();
		return $items;
	}

	public function updateItem($newvalue, $itemid)
	{
		// Find the user that issues the update
		$user = User::find(Auth::user()->id);

		// Laravel doesn't like tables with compound primary keys, so just get the query object and delete it
		// without intervention from the model
		if ($newvalue == "r")
		{
			$user->listing('user_items_have')->id($itemid)->getQuery()->delete();
			$user->listing('user_items_need')->id($itemid)->getQuery()->delete();

			// If the new value isn't "r" (remove), then it must be a new record
		}
		else
		{
			// Create a new record
			// TODO: Somehow use the model for this so we don't have database code in a controller
			$method         = ($newvalue == "h") ? "owns" : "needs";
			if($newvalue == "h"){
				$user->listing('user_items_need')->id($itemid)->getQuery()->delete();
			}
			else{
				$user->listing('user_items_have')->id($itemid)->getQuery()->delete();
			}
			$newItemListing = ItemListing::$method()->insertIgnore(array(
				// ID for the user that requests this record to be made
				'user_id' => $user->id,
				// Item ID for the item the user wants to edit
				'item_id' => $itemid
			));
			$user->last_list_update = time();
			$user->save();
		}
		Cache::section('matches')->flush();
	}

	public function getSelectedList($id)
	{

		$owns     = DB::table('user_items_have')->join('steam_items', 'user_items_have.item_id', '=', 'steam_items.id')->where('user_id', $id)->select('*', DB::raw('0 AS need'), DB::raw('1 AS have'));
		$needs    = DB::table('user_items_need')->join('steam_items', 'user_items_need.item_id', '=', 'steam_items.id')->where('user_id', $id)->select('*', DB::raw('1 AS need'), DB::raw('0 AS have'))->orderBy('type');
		$rawItems = $owns->union($needs)->get();

		$appids = Shared::getColumn($rawItems, "appid");
		if (!is_array($appids) || sizeof($appids) < 1)
		{
			return false;
		}
		$allItemsForappids = Item::gameIDs($appids)->remember(3600)->get();
		$pagedGames  = Game::appids($appids)->orderBy('name')->get();
		$currentList = array();

		$gameNames = Shared::toKeyedArray($pagedGames, "appid");
		// Walk through every dota player and insert them into $teams[ player's team id ]
		foreach ($rawItems as &$rawItem)
		{

			// If this is the first time we encounter a team, initialize some variables
			if (!array_key_exists($rawItem->appid . "_" . $rawItem->type, $currentList))
			{
				// Store everything in an object because I like those
				$currentList[$rawItem->appid . "_" . $rawItem->type] = new stdClass();

				// Set the appid for easy access by the template
				$currentList[$rawItem->appid . "_" . $rawItem->type]->appid = $rawItem->appid;

				// Set the item type for easy access by the template
				$currentList[$rawItem->appid . "_" . $rawItem->type]->item_type = $rawItem->type;
				if($rawItem->appid>=1 && $rawItem->appid<=17){ 
					//Dota 2 TI3 Player Cards
					$playerInfo = explode(" - ", $rawItem->name);
					$currentList[$rawItem->appid . "_" . $rawItem->type]->name = $playerInfo[1];
					$currentList[$rawItem->appid . "_" . $rawItem->type]->custombanner = "dota_team/";
				}
				else
				{
					$currentList[$rawItem->appid . "_" . $rawItem->type]->name      = $gameNames[$rawItem->appid]['name'];
				}

				// Create an array of items to store our items in
				$currentList[$rawItem->appid . "_" . $rawItem->type]->items = array(
				);
			}
			// Add the item to the correct game
			$currentList[$rawItem->appid . "_" . $rawItem->type]->items[] = $rawItem;
		}

		foreach($allItemsForappids as $item){
			if(array_key_exists($item->appid . "_" . $item->type, $currentList)){
				$currentItems = $currentList[$item->appid . "_" . $item->type]->items;
				$itemIDs = Shared::getColumn($currentItems, "id");
				if(!in_array($item->id, $itemIDs)){
					$item->need = 0;
					$item->have = 0;
					$currentList[$item->appid . "_" . $item->type]->items[] = $item;
				}
			}
		}

		usort($currentList, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		// Return a list of games
		return $currentList;
	}

	public function getPagedGames()
	{
		$start = 0;
		if (Input::has('page'))
		{
			$start = (Input::get('page') - 1) * 15;
		}

		if(Input::has('appid'))
			$pagedGames = Game::where('appid', Input::get('appid'))->get();
		else
			$pagedGames = Game::skip($start)->take(15)->orderBy('name')->get();

		$appIDs = array();
		foreach ($pagedGames as $game)
		{
			$appIDs[] = $game->appid;
		}

		if(sizeof($appIDs) < 1){
			return false;
		}	
		$gameNames = Shared::toKeyedArray($pagedGames, "appid");

		if(Input::has('appid') && is_numeric(Input::get('appid'))){
			$rawItems = Item::joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'id', 'type', 'image_base64', 'appid', 'name')
				->whereIn('appid', $appIDs)
				->orderBy('name')->get();
		}
		else{
			$rawItems = Item::type(Session::get('listType', 2))
				->joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'id', 'type', 'image_base64', 'appid', 'name')
				->whereIn('appid', $appIDs)
				->orderBy('name')->get();
		}


		$currentList = array();


		// Walk through every dota player and insert them into $teams[ player's team id ]
		foreach ($rawItems as &$rawItem)
		{
			// If this is the first time we encounter a team, initialize some variables
			if (!array_key_exists($rawItem->appid . '_' . $rawItem->type, $currentList))
			{
				// Store everything in an object because I like those
				$currentList[$rawItem->appid . '_' . $rawItem->type] = new stdClass();

				// Set the appid for easy access by the template
				$currentList[$rawItem->appid . '_' . $rawItem->type]->appid = $rawItem->appid;
				$currentList[$rawItem->appid . '_' . $rawItem->type]->name  = $gameNames[$rawItem->appid]['name'];

				// Set the item type for easy access by the template
				$currentList[$rawItem->appid . '_' . $rawItem->type]->item_type = $rawItem->type;

				// Create an array of items to store our items in
				$currentList[$rawItem->appid . '_' . $rawItem->type]->items = array();
			}
			// Add the item to the correct game
			$currentList[$rawItem->appid . '_' . $rawItem->type]->items[] = $rawItem;
		}

		usort($currentList, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});


		// Return a list of games
		return $currentList;
	}


	public function getSearchedItems($searchString)
	{

		if(strlen($searchString) < 3){
			return false;
		}
		$result = Game::search($searchString)->get();

		//$appIDs = Shared::getColumn($result, "appid");
		$appIDsForGames = array();
		$itemIDsForItems = array();
		$appIDsForItems = array();
		foreach($result as $res){
			if($res->game == 1){
				$appIDsForGames[] = $res->appid;
			}
			if($res->game == 0){
				$itemIDsForItems[] = $res->id;
				$appIDsForItems[] = $res->appid;
			}
		}

		if(sizeof(array_merge($appIDsForGames, $appIDsForItems)) < 1){
			return false;
		}
		$pagedGames  = Game::appids(array_merge($appIDsForGames, $appIDsForItems))->orderBy('name')->get();
		$gameNames = Shared::toKeyedArray($pagedGames, "appid");


		$userItems = array(); $itemsForGames = array();

		if(count($itemIDsForItems) > 0){
			$userItems = Item::joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'id', 'type', 'image_base64', 'appid', 'name')
				->whereIn('id', $itemIDsForItems)->get()->toArray();
		}
		if(count($appIDsForGames) > 0){
			$itemsForGames = Item::joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'id', 'type', 'image_base64', 'appid', 'name')
				->whereIn('appid', $appIDsForGames)->orderBy('appid')->get()->toArray();
		}
		$rawItems = array_merge($userItems, $itemsForGames);
		$currentList = array();


		// Walk through every dota player and insert them into $teams[ player's team id ]
		foreach ($rawItems as &$rawItem)
		{
			$newRawItem = new stdClass();
			foreach($rawItem as $key => $value){
				$newRawItem->$key = $value;
			}
			$rawItem = $newRawItem;

			// If this is the first time we encounter a team, initialize some variables
			if (!array_key_exists($rawItem->appid . "_" . $rawItem->type, $currentList))
			{
				// Store everything in an object because I like those
				$currentList[$rawItem->appid . "_" . $rawItem->type] = new stdClass();

				// Set the appid for easy access by the template
				$currentList[$rawItem->appid . "_" . $rawItem->type]->appid = $rawItem->appid;

				// Set the item type for easy access by the template
				$currentList[$rawItem->appid . "_" . $rawItem->type]->item_type = $rawItem->type;
				if($rawItem->appid>=1 && $rawItem->appid<=17){ 
					//Dota 2 TI3 Player Cards
					$playerInfo = explode(" - ", $rawItem->name);
					$currentList[$rawItem->appid . "_" . $rawItem->type]->name = $playerInfo[1];
					$currentList[$rawItem->appid . "_" . $rawItem->type]->custombanner = "dota_team/";
				}
				else
				{
					$currentList[$rawItem->appid . "_" . $rawItem->type]->name      = $gameNames[$rawItem->appid]['name'];
				}

				// Create an array of items to store our items in
				$currentList[$rawItem->appid . "_" . $rawItem->type]->items = array(
				);
			}
			// Add the item to the correct game
			$currentList[$rawItem->appid . "_" . $rawItem->type]->items[] = $rawItem;
		}



		usort($currentList, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		// Return a list of games
		return $currentList;
	}

	public function getProfile($steamid)
	{
		$user = User::where('steamid', $steamid)->first();
		if($user->settings->hide_profile==0)
			return $this->getList('selected', $user);
		else
			return Redirect::to('/');
	}

	public function getInventory()
	{
		$inventoryURL = "http://steamcommunity.com/profiles/" . Auth::user()->steamid . "/inventory/json/753/6";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $inventoryURL);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($ch);
		curl_close($ch);

		die($json);
	}

}
