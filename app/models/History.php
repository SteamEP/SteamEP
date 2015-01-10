<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 2.0
 */
class History extends Eloquent {

    // The name of the table this model will use
    protected $table      = 'users_history';
    //
    // Specify the columns we can fill with Settings::create()
    protected $fillable   = array('user_id');
    //
    // Our primary key isn't "id", so overwrite
    protected $primaryKey = 'user_id';

    // Eloquent function for specifying relationships
    public function user()
    {
        return $this->belongsTo('User');
    }

}

?>
