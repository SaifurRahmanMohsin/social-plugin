<?php namespace Mohsin\Social\Components;

use Auth;
use Lang;
use Input;
use Flash;
use Session;
use Redirect;
use Cms\Classes\Page;
use InvalidArgumentException;
use Cms\Classes\ComponentBase;
use Mohsin\Social\Models\Settings;
use October\Rain\Auth\AuthException;
use RainLab\User\Models\User as UserModel;
use Mohsin\Social\Models\Social as SocialModel;
use League\OAuth2\Client\Exception\IDPException;
use Mohsin\Social\Components\BaseProviderComponent;

class Microsoft extends BaseProviderComponent
{

    public function componentDetails()
    {
        return [
            'name'        => 'mohsin.social::lang.component.microsoft_login',
            'description' => 'mohsin.social::lang.component.microsoft_desc'
        ];
    }

    public function onRun()
    {
      parent::onRun();
      $exception = null;

      // Check for errors
      if($this -> hasErrors())
        return Redirect::to(self::$currentPage);

      if(Session::has('provider') && Session::get('provider') == 'microsoft')
        {

        /**
         * Previous request registered the user, login now and redirect
         */
        if (Session::has('user_id'))
        {
          $user_id = Session::get('user_id');
          $user = UserModel::where( 'id', $user_id )->first();
          if(!$user)
            Flash::error('Forged Request Error');
          else
            Auth::login($user);

          /*
           * Logged in, clear session and redirect to the intended page
           */
          Session::remove('provider');
          $redirectUrl = $this->pageUrl($this->property('redirect'));
          if ($redirectUrl = post('redirect', $redirectUrl))
            return Redirect::intended($redirectUrl);
          else
            return Redirect::to(self::$currentPage);
        }

        /**
         * Check given state against previously stored one to mitigate CSRF attack.
         */
        if (Input::has('state') && Input::get('state') !== Session::get('oauth2state'))
        {
          Flash::error("Invalid state");
          return Redirect::to(self::$currentPage);
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
                $file = $this -> addImage('m' . $userDetails -> uid, substr($userDetails -> imageUrl, 0, strrpos($userDetails -> imageUrl, ':')));
                $data = array (
                  'name' => $userDetails -> name,
                  'surname' => $userDetails -> lastName,
                  'email' => $userDetails -> email,
                  'password' => $password,
                  'password_confirmation' => $password,
                  'avatar' => $file
                );

                // Register
                $user = $this -> register($data, $userDetails -> uid);

                // Create the relation between the image and user
                $relation = $user->{'avatar'}();
                $relation -> add($file, $this->sessionKey);
              }

             // Link the user to Microsoft
             if($user -> social == null)
                $user -> social = SocialModel::getFromUser($user);
              $user -> social -> microsoft = $userDetails -> uid;
              $urls = $userDetails -> urls;
              if(!empty($urls))
                $user -> social -> microsoft_url = is_array($urls) ? array_shift($urls) : $urls;
              $user -> social -> save();

              /*
               * Registered, tell the plugin to login
               */
              Session::flash('user_id', $user -> id);
            } catch (InvalidArgumentException $ex) { // Expired token and missing arguments
              $exception = Lang::get('mohsin.social::lang.errors.used_token');
            } catch (AuthException $ex) { // Expired token and missing arguments
              $exception = $ex->getMessage();
            } catch (IDPException $ex) { // All other exceptions
              $exception = $ex->getMessage();
            } catch (Exception $ex) { // All other exceptions
              $exception = $ex->getMessage();
            }
            if ($exception)
              Flash::error();
            return Redirect::to(self::$currentPage);
          }
        }
    }

    /**
     * Make an OAuth2 request to Microsoft to get a token
     */
    public function onMicrosoft()
    {
        if(Session::has('provider'))
          Session::remove('provider');
        Session::put('provider', 'microsoft');
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
        return new \League\OAuth2\Client\Provider\Microsoft([
            'clientId'      => Settings::get('microsoft_id'),
            'clientSecret'  => Settings::get('microsoft_secret'),
            'redirectUri'   => $this -> currentPageUrl(),
            'scopes'        => ['wl.basic', 'wl.emails'],
        ]);
    }

}
