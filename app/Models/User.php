<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
//use Spatie\Permission\Traits\HasPermissions;
use App\Notifications\SetPasswordNotification;
use Attribute;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Concerns\ResolvesDumpSource;


class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    //use HasPermissions;
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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];
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

    public function members() {
        return $this->hasMany(Member::class);
    }

    public function membership() : Attribute {
        return $this->name.'===';
    }
    public function organizations(){
        return $this->belongsToMany(Organization::class);
    }
    public function issues(){
        return $this->hasMany(Issue::class);
    }
}
