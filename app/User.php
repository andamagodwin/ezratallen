<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use App\Http\Controllers\API\Notifications\VerifyApiEmail;
use Illuminate\Support\Carbon;
use App\Message;
use App\ProductRequestChat;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *ss
     * @var array
     */
    protected $fillable = [
        'full_name', 'phone', 'user_type', 'email', 'last_seen_at',
        'country_code', 'permission', 'account_types',
        'password', 'picture', 'avatar_google', 'registration_type', 'registered_from', 'apple_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    public function getOnlineStatusAttribute()
    {

        $online_status = '';

        if ($this->updated_at) {
            $string  = $this->updated_at->diffForHumans();
            $string = trim(preg_replace('!\s+!', ' ', $string));
            $array_of_words = explode(" ", $string);
            $number = (int)$array_of_words[0];
            $seconds = $array_of_words[1];


            if ($number < 59 && ($seconds == 'seconds' || $seconds == 'second')) {
                $online_status = 'online';
            } else {
                $online_status = $this->updated_at->diffForHumans();
            }
        }

        return $online_status;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans();
    }

    //Email verification logic for the API

    public function sendApiEmailVerificationNotification()
    {
        $this->notify(new VerifyApiEmail); // my notification
    }

    //one to many relationship with OauthAccessToken table

    public function AauthAcessToken()
    {
        return $this->hasMany('\App\OauthAccessToken');
    }

    //one many user product relationship

    public function products()
    {
        return $this->hasMany('\App\product');
    }

    public function receiveMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id', 'id')->orderByDesc('id');
    }

    public function sendMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class, 'sender_id', 'id')->orderByDesc('id');
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class, 'sender_id', 'id');
    }
    public function productRequestChats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        
        return $this->hasMany(ProductRequestChat::class, 'sender_id', 'id');
    }
}
