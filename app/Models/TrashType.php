<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrashType extends Model
{
    use HasFactory;

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function wastes(){
        return $this->hasMany(Waste::class);
    }

    public function wasteCollectionCenters(){
        return $this->belongsToMany(WasteCollectionCenter::class);
    }
}
