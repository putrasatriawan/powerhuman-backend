<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'logo',
    ];

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function teams(){
        return $this->hasMany(Team::class);
    }

    public function roles(){
        return $this->hasMany(Role::class);
    }
}
