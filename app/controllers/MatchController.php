<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 2.0
 */
class MatchController extends BaseController {

    public function __construct() {
    	parent::__construct();
        $this->beforeFilter('auth');
    }

    public function matches($matchType = null) {
        if ($matchType != null)
        {
            Session::put('matchType', $matchType);
		} else if (!Session::has('matchType')) {
			Session::put('matchType', Item::$defaultType);
		}	
        if ($matchType!='recently' || Session::get('matchType')!='recently') {
            $data               = new stdClass();
            $data->matches      = $this->getMatches(Session::get('matchType', 2));
            $data->totalMatches = !$data->matches ? 0 : count($data->matches);

            if (!$data->matches) {
                $data->matches = array();
            }
            $paginator     = Paginator::make($data->matches, $data->totalMatches, 10);
            $data->matches = array_slice($data->matches, ($paginator->getCurrentPage() - 1) * $paginator->getPerPage(), $paginator->getPerPage());
            return View::make('matches.index')->with(array(
                    'paginator' => $paginator,
                    'data'      => $data
            ));
        } else {
            $data               = new stdClass();
            $data->matches = History::where('user_id', Auth::user()->id)->orderBy('updated_at', 'desc')->get();
            $data->totalMatches = count($data->matches);
            $paginator     = Paginator::make(range(1, $data->totalMatches), $data->totalMatches, 10);
            $data->matches = array_slice($data->matches->toArray(), ($paginator->getCurrentPage() - 1) * $paginator->getPerPage(), $paginator->getPerPage());
            return View::make('matches.index')->with(array(
                    'paginator' => $paginator,
                    'history'   => TRUE,
                    'data'      => $data
            ));
        }
    }

    public function getMatches($itemType = 1) {
        // TODO: Cache this somehow.

        // Retrieve the user's ignorelist from the database
        $ignoreList   = array();
        $userSettings = User::find(Auth::user()->id)->settings;
        $ignoreList = json_decode($userSettings->ignore_list, true);

        // If the user doesn't have an ignorelist, create an empty array
        if (!is_array($ignoreList)) {
            $ignoreList = array();
        }

        // Add the user's id to the list so we don't get matches from the user himself
        $ignoreList[] = Auth::user()->id;

        // Retrieve a list of items the user either needs or has
        // This doesn't work for some reason.
        //$authedUserListings = ItemListing::bothAll(Auth::user()->id)->getQuery()->remember(1)->get();

        $owns               = DB::table('user_items_have')->join('steam_items', 'user_items_have.item_id', '=', 'steam_items.id')->where('type', $itemType)->where('user_id', Auth::user()->id)->select('*', DB::raw('0 AS need'), DB::raw('1 AS have'));
        $needs              = DB::table('user_items_need')->join('steam_items', 'user_items_need.item_id', '=', 'steam_items.id')->where('type', $itemType)->where('user_id', Auth::user()->id)->select('*', DB::raw('1 AS need'), DB::raw('0 AS have'));
        $authedUserListings = $owns->union($needs)->get();

        // Create an array to store everything the user needs
        $authedUserNeeds = array();

        // Create an array to store everything
        $authedUserHas = array();

        // Retrieve a list of user_id's that have any items the user needs, ignoring anyone on the ignorelist
        $fullMatches = ItemListing::getMatchesFor($authedUserListings, $ignoreList);

        // If nobody has anything, return false
        if (!$fullMatches || sizeof($fullMatches) < 1) {
            return false;
        }
		
		// Get games we have.
        $appids = Shared::getColumn($authedUserListings, "appid");
        $games = Game::appids($appids)->remember(300)->get();
        $games = Shared::toKeyedArray($games, "appid");

        // Get settings from the users that we had a match with.
        $fullMatchesUserIDs   = array_unique(Shared::getColumn($fullMatches, 'user_id'));
        $matchedUsersSettings = Shared::toKeyedArray(Settings::whereIn('user_id', $fullMatchesUserIDs)->get(), 'user_id');

        // Initialize the arrays used.
        $result         = array();
        $matchedItemIDs = array();

        foreach ($fullMatches as $fullMatch)
        {
            // Does the matched user have settings?
            if (array_key_exists($fullMatch->user_id, $matchedUsersSettings))
            {
                // Check if he has hidden this type of items.
                if ($matchedUsersSettings[$fullMatch->user_id]->{Item::hideTable($itemType)} > 0)
                {
                    continue;
                }
                // Do we only want matches with trade offers?
                if ($userSettings['only_trade_offers'] == 1 && $matchedUsersSettings[$fullMatch->user_id]->tradeoffer_url == "") {
                    continue;
                }
            }
            if (!array_key_exists($fullMatch->user_id, $result))
            {
                $result[$fullMatch->user_id]          = new stdClass();
                $result[$fullMatch->user_id]->have    = array(
                );
                $result[$fullMatch->user_id]->need    = array(
                );
                $result[$fullMatch->user_id]->user_id = $fullMatch->user_id;
			    if(array_key_exists($fullMatch->user_id, $matchedUsersSettings))
			    {
			        $result[$fullMatch->user_id]->settings = $matchedUsersSettings[$fullMatch->user_id];
			    }
            }
				
            if (!array_key_exists($fullMatch->user_needs_item, $result[$fullMatch->user_id]->need))
            {
                $matchedItemIDs[]                                               = $fullMatch->user_needs_item;
                $result[$fullMatch->user_id]->need[$fullMatch->user_needs_item] = new stdClass();
            }
            if (!array_key_exists($fullMatch->user_has_item, $result[$fullMatch->user_id]->have))
            {
                $matchedItemIDs[]                                             = $fullMatch->user_has_item;
                $result[$fullMatch->user_id]->have[$fullMatch->user_has_item] = new stdClass();
            }
        }
				
		if(sizeof($matchedItemIDs) < 1) return false;
				
        $matchedUsers = Shared::toKeyedArray(User::whereIn('id', array_unique(Shared::getColumn($fullMatches, 'user_id')))->get(), 'id');
        $matchedItems = Shared::toKeyedArray(Item::itemIDs($matchedItemIDs)->get(), 'id');
        foreach ($result as $userid => &$resultRow)
        {
            foreach ($resultRow->have as $key => &$value)
            {
                if($itemType==1 || $itemType==6) $value->name = $matchedItems[$key]->name;
                else $value->name = $matchedItems[$key]->name.' ('.$games[$matchedItems[$key]->appid]->name.')';
                $value->type = $matchedItems[$key]->type;
            }
            foreach ($resultRow->need as $key => &$value)
            {
                if($itemType==1 || $itemType==6) $value->name = $matchedItems[$key]->name;
                else $value->name = $matchedItems[$key]->name.' ('.$games[$matchedItems[$key]->appid]->name.')';
                $value->type = $matchedItems[$key]->type;
            }

            if (array_key_exists($userid, $matchedUsersSettings))
            {
                $resultRow->sameGameOnly = $matchedUsersSettings[$userid]->same_game_only;
            } else {
                $resultRow->sameGameOnly = 0;
            }
            $resultRow->user         = $matchedUsers[$userid];
        }
				
        usort($result, function($a, $b) {
            return ($a->user->last_list_update > $b->user->last_list_update) ? -1 : 1;
        });

        return $result;
    }

}

