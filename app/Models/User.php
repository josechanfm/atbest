<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
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
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */

    protected $with=['roles','member'];
     
    protected $appends = [
        'profile_photo_url',
        'is_organizer',
        'organizer',
    ];

    public function getOrganizerAttribute(){
        return $this->organizer()->get();
    }
    public function getisOrganizerAttribute(){
        return $this->isOrganizer();
    }

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
    public function organizer(){
        return $this->belongsToMany(Organization::class, 'organization_user');
    }

    public function member() {
        return $this->hasOne(Member::class)->where('is_default',true);
    }
    public function members() {
        return $this->hasMany(Member::class);
    }

    public function membership() : Attribute {
        return $this->name.'===';
    }
    public function organizations()
    {
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
