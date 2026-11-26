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
use App\Services\EmailGateway;
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
        'mobile',
        'mobile_validated_at',
        'email',
        'password',
        'is_active',
        'language',
        'google_id',
        'google_email',
        'google_avatar',
        'registration_method'
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
        // 'is_organizer',
        // 'organizer',
    ];

    //public function getOrganizerAttribute(){
        
        //$member= $this->member()->first();
        //dd($member);
        //return $this->organizer()->get();
    //}
    // public function getIsOrganizerAttribute(){
    //     return $this->is_organizer();
    // }

    public function sendPasswordResetNotification($token ): void
    {
        $emailGateway = app(EmailGateway::class);

        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        if ($this->password === 'need-to-set') {
            $emailGateway->send(
                $this->email,
                '設定帳號密碼通知',
                "您好，{$this->name}：\n\n"
                    . "您的帳號已建立，請點擊以下連結設定您的登入密碼：\n"
                    . $resetUrl . "\n\n"
                    . "此連結將於 {$expireMinutes} 分鐘後失效。如連結已過期，請前往登入頁面點擊「忘記密碼」重新獲取連結。"
            );

            return;
        }

        $emailGateway->send(
            $this->email,
            '重設密碼通知',
            "您好，{$this->name}：\n\n"
                . "我們收到了您的帳號重設密碼申請。請點擊以下連結重設您的密碼：\n"
                . $resetUrl . "\n\n"
                . "此重設密碼連結將於 {$expireMinutes} 分鐘後失效。\n"
                . "如果您未曾提出重設密碼申請，請忽略此郵件，您的帳號將不會有任何變更。"
        );
    }

    public function hasPasswordSet () {
        return $this->password !== 'need-to-set';
    }
    
    public function isOrganizer(){
        return $this->member?->isOrganizer();
    }
    public function organizer(){
        return $this->member()->where('is_organizer',true)->first();
        //return $this->belongsToMany(Organization::class, 'organization_user');
    }

    public function member() {
        return $this->hasOne(Member::class)->where('is_default',true);
    }
    public function members() {
        return $this->hasMany(Member::class)->with('organization');
    }

    public function membership() : Attribute {
        return $this->name.'===';
    }
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'members', 'user_id', 'organization_id');
        // return Organization::whereIn('id', function ($query) {
        //     $query->select('organization_id')
        //           ->from('members') // The members table
        //           ->where('user_id', $this->id);
        // })->get();
    }
    public function incidents(){
        return $this->hasMany(Incident::class);
    }

    public function businessCard()
    {
        return $this->hasOne(BusinessCard::class);
    }
}
