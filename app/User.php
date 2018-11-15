<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Swagger\Annotations as SWG;


/**
 * @OA\Schema()
 */

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname','lastname','mobile', 'email', 'password'
    ];
    
    /**
     * The User id
     * @var integer
     * @OA\Property()
     */
 
    public $id;
    /**
     * The User firstname
     * @var string
     * @OA\Property()
     */
 
    public $firstname;
    /**
     * The User lastname
     * @var string
     * @OA\Property()
     */
 
    public $lastname;
    /**
     * The User mobile
     * @var string
     * @OA\Property()
     */
 
    public $mobile;
    /**
     * The User email
     * @var string
     * @OA\Property()
     */
 
    public $email;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}






