<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use App\Notifications\SetPasswordNotification;
use Attribute;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;


class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasPermissions;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $with=['roles','member'];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */

    public function sendPasswordResetNotification($token)
    {
        if ($this->password === 'need-to-set') {
            $this->notify(new SetPasswordNotification($token));
            return;
        }

        $this->notify(new ResetPasswordNotification($token));
    }

    public function hasPasswordSet () {
        return $this->password !== 'need-to-set';
    }
    public function isOrganizer(){
        return $this->member?->isOrganizer();
    }

    public function member() {
        return $this->hasOne(Member::class)->where('default',true);
    }
    public function members() {
        return $this->hasMany(Member::class);
    }

    public function membership() : Attribute {
        return $this->name.'===';
    }
    public function organizations(){
        return Organization::whereIn('id', function ($query) {
            $query->select('organization_id')
                  ->from('members') // The members table
                  ->where('user_id', $this->id);
        })->get();
    }
    public function issues(){
        return $this->hasMany(Issue::class);
    }
}
