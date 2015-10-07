<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 2.0
 */
class Item extends Eloquent {

    protected $table        = 'steam_items';

    public $defaultType = 2;

    protected static $typeRegister = array(
        1 => array(
        	"hide" => "dota2_player_cards_ti3_hide",
        	"name" => "Dota 2 Players",
            "css"  => "dota-row",
            "enabled" => false,
            "useFilters" => false,
        ),
        2 => array(
        	"hide" => "steam_trading_cards_hide",
        	"name" => "Trading Cards",
            "css"  => "card-row",
            "enabled" => true,
            "useFilters" => true,
        ),
        3 => array(
        	"hide" => "steam_trading_cards_foil_hide",
        	"name" => "Foil Cards",
            "css"  => "foil-row",
            "enabled" => true,
            "useFilters" => true,
    	),
        4 => array(
        	"hide" => "steam_emoticons_hide",
        	"name" => "Emoticons",
        	"css"  => "emoticons-row",
        	"enabled" => true,
        	"useFilters" => true,
	  	),
		5 => array(
			"hide" => "steam_backgrounds_hide",
			"name" => "Backgrounds",
			"css"  => "backgrounds-row",
			"enabled" => true,
			"useFilters" => true,
	  	),
		6 => array(
			"hide" => "dota2_diretide_hide",
			"name" => "Diretide", 
			"css" => "",
			"enabled" => false,
			"useFilters" => false,
	 	)
    );

    public static function typeCSS($typeID)
    {
        // Check if $typeID actually exists in the register
        if ($typeID != null && array_key_exists($typeID, self::$typeRegister))
        {
            //Return the tablename is $typeID exists
            return self::$typeRegister[$typeID]["css"];
        }

        // Return null if $typeID doesn't exist
        return null;
    }

    public static function hideTable($typeID)
    {
        // Check if $typeID actually exists in the register
        if ($typeID != null && array_key_exists($typeID, self::$typeRegister))
        {
            //Return the tablename is $typeID exists
            return self::$typeRegister[$typeID]["hide"];
        }

        // Return null if $typeID doesn't exist
        return null;
    }

    // Static function to return the entire typeregister so other classes can loop through them
    public static function typeTable()
    {
        return self::$typeRegister;
    }

    // Construct a new Item with type $typeID
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Specify the game_id's we select with our query
    public function scopeGameID($query, $gameid)
    {
        return $query->where('appid', $gameID);
    }

    // Specify the game_id's we select with our query
    public function scopeGameIDs($query, $gameIDs)
    {
        return $query->whereIn('appid', $gameIDs);
    }

    // Specify the item_id's we select with our query
    public function scopeItemIDs($query, $itemIDs)
    {
        return $query->whereIn('id', $itemIDs);
    }

    // ??
    public function scopeJoinWith($query, $userid)
    {
        $query = $query->join('user_items_have', function($join) use($userid) {
                    $join->on('steam_items.id', '=', 'user_items_have.item_id');
                    $join->on('user_items_have.user_id', '=', DB::raw($userid));
                }, null, null, 'left outer');

        return $query->join('user_items_need', function($join) use($userid) {
                            $join->on('steam_items.id', '=', 'user_items_need.item_id');
                            $join->on('user_items_need.user_id', '=', DB::raw($userid));
                        }, null, null, 'left outer');
    }

}

?>
