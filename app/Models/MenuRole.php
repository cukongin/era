<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function menu()
    {
        return $this->belongsTo(DynamicMenu::class, 'menu_id');
    }
}
