<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of bHasMany
 *
 * @author Peter
 */
class BHasMany extends Illuminate\Database\Eloquent\Relations\HasMany {

    public function __construct($query, $parent, $table, $foreignKey)
    {
        parent::__construct($query, $parent, $foreignKey, 'id');
        $this->from($table);
        $this->foreignKey = $table . '.' . $foreignKey;
    }

}

?>
