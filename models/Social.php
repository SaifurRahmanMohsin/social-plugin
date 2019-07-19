<?php namespace Mohsin\Social\Models;

use Model;

/**
 * Social Model
 */
class Social extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'mohsin_social_socials';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [ 'facebook' ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
      'user' => ['RainLab\User\Models\User']
    ];

    public static function getFromUser($user)
    {
        if ($user->social) {
            return $user->social;
        }

        $social = new static;
        $social->user = $user;
        $social->save();

        $user->social = $social;

        return $social;
    }
}
