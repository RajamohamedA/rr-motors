<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    #[Fillable([
        'company_id',
        'store_id',
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'is_super_user',
    ])]
    #[Hidden([
        'password',
        'remember_token',
    ])]

    protected $fillable = [
        'company_id',
        'store_id',
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'is_super_user',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'is_super_user' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Default / Primary Store
    |--------------------------------------------------------------------------
    */

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /*
    |--------------------------------------------------------------------------
    | All Assigned Stores
    |--------------------------------------------------------------------------
    */

    public function stores()
    {
        return $this->belongsToMany(
            Store::class,
            'user_stores'
        );
    }
}
