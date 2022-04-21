<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    use HasFactory;

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function trashType(){
        return $this->belongsTo(TrashType::class);
    }
}
