<?php namespace Mohsin\Social\Components;

use Auth;
use Lang;
use Input;
use Flash;
use Session;
use Redirect;
use Validator;
use Cms\Classes\Page;
use ValidationException;
use InvalidArgumentException;
use Mohsin\Social\Models\Settings;
use RainLab\User\Components\Account;
use October\Rain\Auth\AuthException;
use RainLab\User\Models\User as UserModel;
use Mohsin\Social\Models\Social as SocialModel;

class Facebook extends Account
{

    public function componentDetails()
    {
        return [
            'name'        => 'Facebook Login',
            'description' => 'Insert a Facebook Login Button to page'
        ];
    }

    public function onRender()
    {
      $currentPage = $this -> currentPageUrl();
      $exception = null;
      if(Session::has('provider') && Session::get('provider') == 'facebook')
        {
        /**
         * Check if there are any errors from the previous request.
         */
        if (Input::has('error'))
          {
            $reason = Input::get('error_reason');
            if($reason == 'user_denied')
              Flash::error("Cancelled by user");
            return Redirect::to($currentPage);
          }

        /**
         * Check given state against previously stored one to mitigate CSRF attack.
         */
        if (Input::has('state') && Input::get('state') !== Session::get('oauth2state'))
        {
          Flash::error("Invalid state");
          return Redirect::to($currentPage);
        }

        /**
         * Previous request registered the user, login now and redirect
         */
        if (Session::has('user_id'))
        {
          $user_id = Session::get('user_id');
          $user = UserModel::where( 'id', $user_id )->first();
          Auth::login($user);

          /*
           * Logged in, clear session and redirect to the intended page
           */
          Session::remove('provider');
          $redirectUrl = $this->pageUrl($this->property('redirect'));
          if ($redirectUrl = post('redirect', $redirectUrl))
            return Redirect::intended($redirectUrl);
          else
            return Redirect::to($currentPage);
        }

        /**
         * Consume the OAuth token code and perform registration.
         */
        $provider = $this -> getProvider();
        if (Input::has('code'))
          {
            $input = Input::get('code');
            try {
              // Use the OAuth2 token to get an access token
              $token = $provider->getAccessToken('authorization_code', [
                  'code' => $input
              ]);

              // Get user details now
              $userDetails = $provider->getUserDetails($token);

              // Check if the user already exists
              $user = UserModel::where( 'email', $userDetails -> email )->first();

              /*
               * If user doesn't exist, create a new user
               */
              if (!$user) {
                $password = uniqid();
                $data = array (
                  'name' => $userDetails -> name,
                  'surname' => $userDetails -> lastName,
                  'email' => $userDetails -> email,
                  'password' => $password,
                  'password_confirmation' => $password
                );

                // Register
                $user = $this -> register($data);
              }

             // Link the user to Facebook
             if($user -> social == null)
                $user -> social = SocialModel::getFromUser($user);
              $user -> social -> facebook = $userDetails -> uid;
              $user -> social -> save();

              /*
               * Registered, tell the plugin to login
               */
              Session::flash('user_id', $user -> id);
            } catch (InvalidArgumentException $ex) { // Expired token and missing arguments
              $exception = 'This login token has already been used!';
            } catch (AuthException $ex) { // Expired token and missing arguments
              $exception = $ex->getMessage();
            } catch (Exception $ex) { // All other exceptions
              $exception = $ex->getMessage();
            }
            if ($exception)
              Flash::error();
            return Redirect::to($this->currentPageUrl());
          }
        }
    }

    /**
     * Register the user
     */
    public function register($data)
    {
        /*
         * Validate input
         */
        if (!array_key_exists('password_confirmation', $data)) {
            $data['password_confirmation'] = post('password');
        }
        $rules = [
            'email'    => 'required|email|between:2,64',
            'password' => 'required|min:2'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        /*
         * Register user
         */
        return Auth::register($data, true);
    }

    /**
     * Make an OAuth2 request to Facebook to get a token
     */
    public function onFacebook()
    {
        Session::put('provider','facebook');
        $provider = $this -> getProvider();
        $authUrl = $provider -> getAuthorizationUrl();
        Session::flash('oauth2state', $provider->state);
        return Redirect::to($authUrl);
    }

    /**
     * @var Provider The provider object for this component.
     */
    private function getProvider()
    {
        return new \League\OAuth2\Client\Provider\Facebook([
            'clientId'      => Settings::get('facebook_id'),
            'clientSecret'  => Settings::get('facebook_secret'),
            'redirectUri'   => $this -> currentPageUrl(),
            'scopes'        => ['email', 'public_profile', 'user_friends'],
        ]);
    }

}