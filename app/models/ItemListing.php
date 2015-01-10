<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 2.0
 */
class ItemListing extends Eloquent {

    // The name of the table this model will use
    protected $table        = null;
    //
    // The column names we can create by using the ItemListing::create() method
    protected $fillable     = array('user_id', 'item_id', 'item_type', 'need', 'have');
    //
    // Our primary key isn't "id", so overwrite this
    protected $primaryKey   = 'user_id';
    //
    // Disable timestamps
    public $timestamps   = false;
    //
    // Disable automatically incrementing the primary key on save
    public $incrementing = false;

    // Eloquent function for specifying relationships
    public function user()
    {
        return $this->belongsTo('User');
    }

    // Allows us to use ->owns() to select item listings that have 'have' on '1'
    public function scopeOwns($query)
    {
        $this->setTable("user_items_have");
        return $query->from("user_items_have");
    }

    // Allows us to use ->has() to select item listings that have 'need' on '1'
    public function scopeNeeds($query)
    {
        $this->setTable("user_items_need");
        return $query->from("user_items_need");
    }

    // Allows us to use ->id($id) to select item listings for the item with id $id
    public function scopeId($query, $id)
    {
        return $query->where('item_id', $id);
    }

    // Allows us to use ->ids($ids) to select item listings for items where their id is in the $ids array
    public function scopeIds($query, $ids)
    {
        return $query->whereIn('item_id', $ids);
    }

    public function scopeIgnore($query, $userList)
    {
        return $query->whereNotIn('user_id', $userList);
    }

    public function scopeUser($query, $userid)
    {
        
    }

    public function scopeBothAll($query, $userid)
    {
        $owns  = DB::table('user_items_have')->where('user_id', $userid)->select('*', DB::raw('0 AS need'), DB::raw('1 AS have'));
        $needs = DB::table('user_items_need')->where('user_id', $userid)->select('*', DB::raw('1 AS need'), DB::raw('0 AS have'));
        return $owns->union($needs);
    }

    public function scopeInsertIgnore($query, $array)
    {
        if (!is_array(reset($array)))
        {
            $array = array($array);
        }
        $bindings = array();
        foreach ($array as $record)
        {
            $bindings = array_merge($bindings, array_values($record));
        }

        return DB::getPdo()->prepare(str_replace("insert", "insert ignore", $sql = $query->getQuery()->getGrammar()->compileInsert($query->getQuery(), $array)))->execute($bindings);
    }

    public static function getMatchesFor($authedUserListings, $ignoreList)
    {
        // Walk through the items the user needs or has
        $authedUserNeeds = array();
        $authedUserHas   = array();
        foreach ($authedUserListings as $userListing)
        {
            // If the user needs the current item, add it to the needlist
            if ($userListing->need > 0)
            {
                $authedUserNeeds[] = $userListing->item_id;
            }
            if ($userListing->have > 0)
            {
                $authedUserHas[] = $userListing->item_id;
            }
        }

        // If the user doesn't need anything, we can't make any matches, so return false
        if (sizeof($authedUserNeeds) < 1 || sizeof($authedUserHas) < 1)
        {
            return false;
        }
        $qMarksHave   = str_repeat('?, ', sizeof($authedUserNeeds) - 1) . '?';
        $qMarksNeed   = str_repeat('?, ', sizeof($authedUserHas) - 1) . '?';
        $qMarksIgnore = str_repeat('?, ', sizeof($ignoreList) - 1) . '?';
        $query        = "SELECT uih.user_id AS user_id, uih.item_id AS user_has_item, uin.item_id AS user_needs_item from user_items_have uih, user_items_need uin
                  WHERE uih.item_id IN ($qMarksHave)
                  AND uin.item_id  IN ($qMarksNeed)
                  AND uin.user_id NOT IN ($qMarksIgnore)
                  AND uin.user_id = uih.user_id ";
        return DB::select($query, array_merge($authedUserNeeds, $authedUserHas, $ignoreList));
    }

}

?>
