<?php
/**
 * Description of SteamAPI
 *
 * @author Peter
 */
class SteamAPI {

    public static $apiKey = '';

    private static function getURL($url)
    {
        $ch   = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function getName($steamid, $extras = false) {
        if ($steamid == '' || !is_numeric($steamid)) {
            return null;
        } elseif (Cache::section('steamUserMap')->has($steamid)) {
            return Cache::section('steamUserMap')->get($steamid);
        }

        $jsonResponse = json_decode(
        	self::getURL('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . self::$apiKey . '&steamids=' . $steamid), TRUE);
        if ($jsonResponse['response']['players'][0]['personastate'] == 0) {
            $state = 'Offline';
        } else {
            $state = 'Online';
        }
        if ($extras && ($steamid == "76561197996660437" || $steamid == "76561198040674324")) {
                $jsonResponse['response']['players'][0]['personaname'] .= "<span class=\"devc\"> [DEV]</span>";
        }
        $resp = $jsonResponse['response']['players'][0]['personaname'] . '\\' . $state . '\\' . 
            $jsonResponse['response']['players'][0]['avatar'];
		$user = User::where('steamid', $steamid)->first();
		if ($jsonResponse['response']['players'][0]['personaname'] != '') {
			$user->displayname = $jsonResponse['response']['players'][0]['personaname'];
			$user->save();
			Cache::section('steamUserMap')->put($steamid, $resp, 30);
        	return $resp;
		} else {
			if ($user->displayname!='' && $user->displayname!='V1.0 User') {
				$displayName = $user->displayname;
			} else {
				$displayName = 'Unknown';
			}
			return $displayName."\Unknown\http://media.steampowered.com/steamcommunity/public/images/avatars/fe/fef49e7fa7e1997310d705b2a6158ff8dc1cdfeb_full.jpg";
		}
    }

    public static function getItemInventory($steamid)
    {
        $url = "http://steamcommunity.com/profiles/$steamid/inventory/json/753/6?trading=1";
        $data = $lastRequest = json_decode(self::getURL($url));
        while ($lastRequest->more == true) {
        	$lastRequest = json_decode(self::getURL($url . '&start=' . $lastRequest->more_start));
        	$data = (object) array_merge_recursive((array) $data, (array) $lastRequest);
        }
        return $data;
    }

    public static function getGamesFromItemInventory($inventory)
	{
        //Throw a 404 if the object does not contain the properties we need 
        if (!$inventory || !array_key_exists("rgDescriptions", $inventory))
        {
            $out = print_r($inventory, true);
            return false;
        }

        //Get a list of games the user has items for
        $gameNames = array();
		$cards = array();
		$cardCount = array();
		foreach ($inventory->rgInventory as $item){
			if(array_key_exists($item->classid, $cardCount)){
				$cardCount[$item->classid] += $item->amount;	
			}
			else{
				$cardCount[$item->classid] = $item->amount;	
			}
		}
		foreach ($inventory->rgDescriptions as $item)
        {
            if (!array_key_exists('tags', $item))
            {
                if (!array_key_exists("rgDescriptions", $inventory))
                {
                    App::abort(404);
                }
			}

			$tag = new stdClass();
			$tag->card = false;
			$tag->game = false;
			$tag->type = false;
			
			foreach($item->tags as $t){
				if($t->internal_name == "cardborder_1"){ // Foil Trading Cards
					$tag->type = 3; // foil
				}
				elseif($t->internal_name == "item_class_2"){ // Trading cards
					$tag->card = $t;
					if($tag->type != 3)
						$tag->type = 2;
				}
				elseif($t->internal_name == "item_class_3"){ // Backgrounds
					$tag->card = $t;
					$tag->type = 5;
				}
				elseif($t->internal_name == "item_class_4"){ //Emotes
					$tag->card = $t;
					$tag->type = 4;
				}
				elseif(strpos($t->internal_name, "app") === 0){
					$tag->game = $t;
				}	
			}
			
			if (!$tag->card || !$tag->game)
            {
                continue;
			}

			$game_appid = substr($tag->game->internal_name, 4);
			if (!array_key_exists($game_appid, $gameNames)){
				$game                                                = new stdClass();
				$game->items										 = array();
				$game->appid                                         = $game_appid;
				$game->name                                          = $tag->game->name;
				$gameNames[$game_appid] = $game;
			}
			$card = new stdClass();
			$card->classid = $item->classid;
			// FIXME: Yeaaaaaaaaaa about that......
			$card->name = trim(preg_replace("/\([^)]+\)$/", "", str_replace($game_appid . "-", '', $item->market_hash_name)));
			$card->type = $tag->type;
			$card->count = 1;
			if(array_key_exists($card->classid, $cardCount)){
				if($cardCount[$card->classid] > 1){
					$card->count = $cardCount[$card->classid];
				}
			}
			$gameNames[$game_appid]->items[] = $card;
		
		}
        return $gameNames;
    }

}

?>
