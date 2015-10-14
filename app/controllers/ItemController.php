<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 1.0
 */
class ItemController extends BaseController {

	public function __construct() {
		parent::__construct();
		$this->beforeFilter('auth', array('except' => array('getProfile')));
	}
	
	public function getGamesFromItemInventory() {
		$gameList = SteamAPI::getGamesFromItemInventory(SteamAPI::getItemInventory(Auth::user()->steamid));
		if (!$gameList) return false;
		return $this->refactorInventoryList($gameList);
	}

	public function refactorInventoryList($userInventoryList) {
		$userInventoryAppIDs = array_keys($userInventoryList);
		$allItemsForGames = Item::gameIDs($userInventoryAppIDs)->get();
		$gameNames = Game::appids($userInventoryAppIDs)->lists('name', 'appid');

		$appIDForAllItems = Shared::getColumn($allItemsForGames, 'id');
		$userOwnsItemIDs = DB::table('user_items_have')->where('user_id', Auth::user()->id)->whereIn('item_id', $appIDForAllItems)->select('*', DB::raw('0 AS need'), DB::raw('1 AS have'))->lists('item_id');
		$userNeedsItemIDs = DB::table('user_items_need')->where('user_id', Auth::user()->id)->whereIn('item_id', $appIDForAllItems)->select('*', DB::raw('1 AS need'), DB::raw('0 AS have'))->lists('item_id');
		$typesUsed = array();

		$itemsInInventoryOrdered = array();
		foreach ($userInventoryList as $userInventoryGame) {
			foreach ($userInventoryGame->items as $userInventoryItem) {
				if (isset($itemsInInventoryOrdered[$userInventoryGame->appid][$userInventoryItem->name . $userInventoryItem->type])) {
					continue;
				}
				$itemsInInventoryOrdered[$userInventoryGame->appid][$userInventoryItem->name . $userInventoryItem->type] = $userInventoryItem->count;
			}
		}
		foreach ($allItemsForGames as &$item) {
			$uniqueKey = $item->name . $item->type;
			if (array_key_exists($item->appid, $itemsInInventoryOrdered) && array_key_exists($uniqueKey, $itemsInInventoryOrdered[$item->appid])) {
				$item->in_inventory = 1;
				$item->count = $itemsInInventoryOrdered[$item->appid][$uniqueKey];
				if (!array_key_exists($item->appid, $typesUsed)) {
					$typesUsed[$item->appid] = array();
				}
				$typesUsed[$item->appid][$item->type] = $item->type;
			}
		}

		$viewList = array();

		foreach ($allItemsForGames as &$item) {
			
			if (!isset($typesUsed[$item->appid])) {
				Log::info("[MISSING ITEM] Couldn't find an item from user (" . Auth::user()->steamid . ")'s inventory with appid " . $item->appid . ", check for typos in item names! (ItemController:inventoryList)");
				continue;
			}
			
			if (!in_array($item->type, $typesUsed[$item->appid])) {
				continue;
			}

			if (in_array($item->id, $userOwnsItemIDs)) {
				$item->have = 1; $item->need = 0;
			}
			if (in_array($item->id, $userNeedsItemIDs)) {
				$item->need = 1; $item->have = 0;
			}

			$viewItemID = $item->appid . "_" . $item->type;
			if (!array_key_exists($viewItemID, $viewList)) {
				$viewObj = new stdClass();
				$viewObj->appid = $item->appid;
				$viewObj->item_type = $item->type;
				$viewObj->items = array();
				$viewObj->name = $gameNames[$item->appid];
				$viewList[$viewItemID] = $viewObj;
			}
			$viewList[$viewItemID]->items[] = $item;
		}
		
		usort($viewList, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		return $viewList;
	}

	public function getList($listType = null, $profile = false){
		// If the function got $listType, insert the type into the user's session
		if ((array_key_exists($listType, Item::typeTable()) || $listType == "selected" || $listType == "inventory") && !$profile) {
			Session::put('listType', $listType);
		}

		// If the listType session is still not set, just set it to default
		if (!Session::has('listType') && $listType == null && !$profile) {
			Session::put('listType', Item::$defaultType);
		}


		// Store everything in an object because I like objects
		$data = new stdClass();

		// Retrieve a list of all games and cache it for the future
		//$games = Game::orderBy('name')->remember(60);
		$games = Game::orderBy('name');
		$data->allGames = $games->get();

		// If we're in normal mode
		if (Input::get("s")) {
			$data->currentList = $this->getSearchedItems(Input::get("s"));
			$count = 0;
		} else {
			if (Session::get('listType') == "selected" || $profile) {
				$data->currentList = $this->getSelectedList(($profile ? $profile->id : Auth::user()->id));
				$count             = 0;
			} elseif (Session::get('listType') == "inventory") {
				$data->currentList = $this->getGamesFromItemInventory();
			
				$count = 0;
			} elseif (Input::has('appid') && is_numeric(Input::get('appid'))) {
				$count = 1;
				$data->currentList = $this->getGamePage(Input::get('appid'), Auth::user());
			} elseif ((Session::get('listType') != 1 && Session::get('listType') != 6)) {
				$data->currentList = $this->getPagedGames();
				if (Input::has('appid')) {
					$count = 1;
				} else {
					$count = $games->count();
				}
			} else {
				if (Session::get('listType') == 6) {
					$data->currentList = $this->dotaDiretideList();
					$count = 3;
				} else {	
					$data->currentList = $this->dotaTI3Playerlist();
					$count             = 85;
				}
			}	
		}

		// Array with variables to pass to javascript, since Laravel doesn't parse js files
		$pvar = array(
			"editlist" => URL::to('editlist')
		);

		$title = false;
		if ($profile) {
			$title = explode("\\", SteamAPI::getName($profile->steamid))[0] . "'s Profile";
		}

		// Make a view with the gamelist and the javascript array
		return View::make('list.index')->with(
			array(
				"paginator" => Paginator::make(range(1, $count), $count, 15),
				"pvar"      => $pvar,
				"data"      => $data,
				"profile"   => $profile,
				"count"     => $count,
				"title"     => $title
			)
		);
	}

	public function dotaDiretideList() {
		$diretide = Item::type(6)->joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'image_base64', 'name', 'appid', 'id')->orderBy('appid')->get();
		return $this->createItemObjects($diretide, array(18 => array("name"=>"Essences")));
	}

	public function dotaTI3Playerlist() {
		// Retrieve all the dota players from the database and cache them for the future
		//$dotaPlayers = Item::type(1)->joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'image_base64', 'name', 'appid', 'id')->orderBy('appid')->get();
		$dotaPlayers = DB::select(DB::raw("select`user_items_need`.`item_id` as `need`, `user_items_have`.`item_id` as `have`, `image_base64`, `name`, `appid`, `type`, `id` from `steam_items` left outer join `user_items_have` 
			on `steam_items`.`id` = `user_items_have`.`item_id` and `user_items_have`.`user_id` = " . Auth::user()->id . " left outer join `user_items_need` on `steam_items`.`id` = `user_items_need`.`item_id` and `user_items_need`.`user_id` = " . Auth::user()->id . "
			where `type` = 1 
			order by FIELD(`steam_items`.`appid`,4,8,9,5,2,10,7,11,1,3,6,19,18,17,16,15,14,13,12)"));
		$teamNames = Game::appids(range(1, 19))->get();
		$teamNames = Shared::toKeyedArray($teamNames, "appid");
		return $this->createItemObjects($dotaPlayers, $teamNames);
	}

	// Retrieve a list of games that match the searched string where:
	//      - There's a game with a name like $searchString
	//      - There's a game where game_id = $searchString
	//      - There's an item for the game where name like $searchString 
	public function getGamesByName($searchString) {
		// Use the Game::search() method to retrieve a list of items
		$games = Game::search($searchString);
		return $games;
	}

	public function getItemsForAppIDs($appids, $type) {
		// Get properties for the current user so we can check what items he owns / needs
		$user = User::find(Auth::user()->id);

		// Create a list of items with the correct type for games with appid in $appids
		$items = Item::type($type)->gameIDs($appids)->joinWith($user)->get();
		return $items;
	}

	//
	// AJAX CALL
	//
	public function updateItem($newvalue, $itemid) {
		// Find the user that issues the update
		$user = User::find(Auth::user()->id);

		// Laravel doesn't like tables with compound primary keys, so just get the query object and delete it
		// without intervention from the model
		if ($newvalue == "r") {
			$user->listing('user_items_have')->id($itemid)->getQuery()->delete();
			$user->listing('user_items_need')->id($itemid)->getQuery()->delete();

			// If the new value isn't "r" (remove), then it must be a new record
		}
		else // If the new value isn't "r"(remove), then it must be a new record
		{
			// Create a new record
			// TODO: Somehow use the model for this so we don't have database code in a controller
			$method = ($newvalue == "h") ? "owns" : "needs";
			if ($newvalue == "h") {
				// Remove the "need" listing from the database if the user says he has the item
				$user->listing('user_items_need')->id($itemid)->getQuery()->delete();
			} else {
				// Remove the "have" listing from the database if the user days he needs the item
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
	}
	
	private function getUserItemInfo($id) {
		$owns     = DB::table('user_items_have')->join('steam_items', 'user_items_have.item_id', '=', 'steam_items.id')->where('user_id', $id)->select('*', DB::raw('0 AS need'), DB::raw('1 AS have'));
		$needs    = DB::table('user_items_need')->join('steam_items', 'user_items_need.item_id', '=', 'steam_items.id')->where('user_id', $id)->select('*', DB::raw('1 AS need'), DB::raw('0 AS have'))->orderBy('type');
		return $owns->union($needs)->get();
	}

	private function isPlayerCardTeam($appid) {
		return $appid >= 1 && $appid <= 19;
	}

	private function getCustomBanner($appid) {
		if ($this->isPlayerCardTeam($appid)) {
			return "dota_team/";
		}
		if($appid == 18){
			return "diretide/";
		}
		return false;
	}

	public function createItemObjects($items, $gameNames) {
		$objects = array();
		foreach($items as $item){
			$itemColID = $item->appid . "_" . $item->type;
			// If this is the first time we encounter this game + type combo, initialize some variables
			if (!array_key_exists($itemColID, $objects))
			{
				$objects[$itemColID] = new stdClass();
				$objects[$itemColID]->items = array();
				$objects[$itemColID]->name = $gameNames[$item->appid]['name'];
				if ($customBanner = $this->getCustomBanner($item->appid)) {
					$objects[$itemColID]->custombanner = $customBanner;
				}
			}
			$objects[$itemColID]->items[] = $item;
		}
		return $objects;
	}

	public function getSelectedList($id) {
		$rawItems = $this->getUserItemInfo($id);

		$appids = Shared::getColumn($rawItems, "appid");
		if (!is_array($appids) || sizeof($appids) < 1) {
			return false;
		}
		$allItemsForappids = Item::gameIDs($appids)->get();
		$pagedGames  = Game::appids($appids)->orderBy('name')->get();
		$currentList = array();

		$gameNames = Shared::toKeyedArray($pagedGames, "appid");
		
		$currentList = $this->createItemObjects($rawItems, $gameNames);
		
		foreach($allItemsForappids as $item) {
			$itemColID = $item->appid . "_" . $item->type;
			if (array_key_exists($itemColID, $currentList)) {
				if (!in_array($item->id, Shared::getColumn($currentList[$itemColID]->items, "id"))) {
					$item->need = 0;
					$item->have = 0;
					$currentList[$itemColID]->items[] = $item;
				}
			}
		}

		usort($currentList, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		// Return a list of games
		return $currentList;
	}

	public function getGamePage($appid, $user) {
		$game = Game::where('appid', $appid)->first();
		if ($game->count() < 1) {
			return false;
		}
		$items = Item::joinWith($user->id)
			->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'id', 'type', 'image_base64', 'appid', 'name')
			->where('appid', $appid)
			->orderBy('name')->get();
		$list = $this->createItemObjects($items, array($game->appid => array("name" => $game->name)));

		usort($list, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		return $list;

	}

	public function getPagedGames() {
		$start = Input::has('page') ? (Input::get('page') - 1) * 15 : 0;
		
		$pagedGames = Game::skip($start)->take(15)->orderBy('name')->get();

		$appIDs = array();
		foreach ($pagedGames as $game) {
			$appIDs[] = $game->appid;
		}

		if (sizeof($appIDs) < 1) {
			return false;
		}	
		$gameNames = Shared::toKeyedArray($pagedGames, "appid");

		$rawItems = Item::type(Session::get('listType', 2))
			->joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'id', 'type', 'image_base64', 'appid', 'name')
			->whereIn('appid', $appIDs)
			->orderBy('name')->get();
		
		$currentList = $this->createItemObjects($rawItems, $gameNames);
		usort($currentList, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		// Return a list of games
		return $currentList;
	}


	public function getSearchedItems($searchString) {
		if (strlen($searchString) < 3) {
			return false;
		}
		$result = Game::search($searchString)->get();

		//$appIDs = Shared::getColumn($result, "appid");
		$appIDsForGames = array();
		$itemIDsForItems = array();
		$appIDsForItems = array();
		foreach ($result as $res) {
			if ($res->game == 1) {
				$appIDsForGames[] = $res->appid;
			}
			if ($res->game == 0) {
				$itemIDsForItems[] = $res->id;
				$appIDsForItems[] = $res->appid;
			}
		}

		if (sizeof(array_merge($appIDsForGames, $appIDsForItems)) < 1) {
			return false;
		}
		$pagedGames  = Game::appids(array_merge($appIDsForGames, $appIDsForItems))->orderBy('name')->get();
		$gameNames = Shared::toKeyedArray($pagedGames, "appid");

		$userItems = array(); $itemsForGames = array();

		if (count($itemIDsForItems) > 0) {
			$userItems = Item::joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'id', 'type', 'image_base64', 'appid', 'name')
				->whereIn('id', $itemIDsForItems)->get()->toArray();
		}
		if (count($appIDsForGames) > 0) {
			$itemsForGames = Item::joinWith(Auth::user()->id)->select('user_items_need.item_id AS need', 'user_items_have.item_id AS have', 'id', 'type', 'image_base64', 'appid', 'name')
				->whereIn('appid', $appIDsForGames)->orderBy('appid')->get()->toArray();
		}
		$rawItems = array_merge($userItems, $itemsForGames);
		$currentList = array();
	
		foreach ($rawItems as &$rawItem) {
			$newRawItem = new stdClass();
			foreach ($rawItem as $key => $value) {
				$newRawItem->$key = $value;
			}
			$rawItem = $newRawItem;
		}
		$currentList = $this->createItemObjects($rawItems, $gameNames);
		
		usort($currentList, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		// Return a list of games
		return $currentList;
	}

	public function getProfile($steamid) {
		$user = User::where('steamid', $steamid)->first();
		if ($user!=NULL && $user->settings->hide_profile == 0) {
			return $this->getList('selected', $user);
		} else {
			return Redirect::to('/');
		}
	}
}