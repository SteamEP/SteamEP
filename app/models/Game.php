<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 2.0
 */
class Game extends Eloquent {

    // The name of the table this model will use
    protected $table      = 'steam_games';
    protected $primaryKey = 'appid';

    // Search for any game or item in the database
    public static function search($searchString)
    {
        // Get trading cards for the game in case the searchstring is just the name of the game
        $games  = DB::table('steam_games')->select(DB::raw("1 as game"), 'appid', DB::raw('0 as id'), 'name')->where('appid', '=', $searchString)->orWhere('name', 'LIKE', '%' . $searchString . '%');
	$search = Item::select(DB::raw("0 as game"), "appid", 'id', "name")->where('name', 'LIKE', '%' . $searchString . '%');
	foreach($search->get() as $item)
	{
		//Geen duplicate cards toevoegen aan games die al in de query zijn.
		if($item->game==0)
		{
			foreach($games->get() as $game)
			{
				if($game->appid==$item->appid)
				{
					$search->where('appid', '!=', $item->appid);
				}			
			}
		}
	}
	$search->union($games);

        //Return the searchresults
        return $search;
    }

    public function scopeAppids($query, $appids)
    {
        return $query->whereIn('appid', $appids);
    }

}

?>
