<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlienCoordinates extends Model
{
    public function planet(){
        return $this->belongs_to('App\\Planet', 'planet_id');
    }

    const CREATED_AT = null;
    const UPDATED_AT = null;
}
