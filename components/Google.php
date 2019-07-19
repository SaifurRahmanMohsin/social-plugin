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
use Mohsin\Social\Components\BaseProviderComponent;

class Google extends BaseProviderComponent
{
    public function componentDetails()
    {
        return [
            'name' => 'mohsin.social::lang.component.google_login',
            'description' => 'mohsin.social::lang.component.google_desc'
        ];
    }

    public function onRun()
    {
        parent::onRun();
        $exception = null;

        // Check for errors
        if ($this->hasErrors()) {
            return Redirect::to(self::$currentPage);
        }

        if (Session::has('provider') && Session::get('provider') == 'google') {
            // Previous request registered the user, login now and redirect
            if (Session::has('user_id')) {
                $user_id = Session::get('user_id');
                $user = UserModel::where('id', $user_id)->first();
                if (!$user) {
                    Flash::error('Forged Request Error');
                } else {
                    Auth::login($user);
                }

                // Logged in, clear session and redirect to the intended page
                Session::remove('provider');
                $redirectUrl = $this->pageUrl($this->property('redirect'));
                if ($redirectUrl = post('redirect', $redirectUrl)) {
                    return Redirect::intended($redirectUrl);
                } else {
                    return Redirect::to(self::$currentPage);
                }
            }

            // Check given state against previously stored one to mitigate CSRF attack.
            if (Input::has('state') && Input::get('state') !== Session::get('oauth2state')) {
                Flash::error("Invalid state");
                return Redirect::to(self::$currentPage);
            }

            // Consume the OAuth token code and perform registration.
            $provider = $this->getProvider();
            if (Input::has('code')) {
                $input = Input::get('code');
                try {
                    // Use the OAuth2 token to get an access token
                    $token = $provider->getAccessToken('authorization_code', [
                        'code' => $input
                    ]);

                    // Get user details now
                    $userDetails = $provider->getResourceOwner($token);

                    // Check if the user already exists
                    $user = UserModel::where('email', $userDetails->getEmail())->first();

                    // If user doesn't exist, create a new user
                    if (!$user) {
                        $password = uniqid();
                        $file = $this->addImage('g' . $userDetails->getId(), substr($userDetails->getAvatar(), 0, strrpos($userDetails->getAvatar(), '?')));
                        $data = array (
                          'name' => $userDetails->getFirstName(),
                          'surname' => $userDetails->getLastName(),
                          'email' => $userDetails->getEmail(),
                          'password' => $password,
                          'password_confirmation' => $password,
                          'avatar' => $file
                        );

                        // Register
                        $user = $this->register($data, $userDetails->getId());

                        // Create the relation between the image and user
                        $relation = $user->{'avatar'}();
                        $relation->add($file, $this->sessionKey);
                    }

                    // Link the user to Google
                    if ($user->social == null) {
                        $user->social = SocialModel::getFromUser($user);
                    }
                    $user->social->google = $userDetails->getId();
                    $user->social->save();

                    // Registered, tell the plugin to login
                    Session::flash('user_id', $user->id);
                } catch (InvalidArgumentException $ex) { // Expired token and missing arguments
                    $exception = 'This login token has already been used!';
                } catch (AuthException $ex) { // Expired token and missing arguments
                    $exception = $ex->getMessage();
                } catch (Exception $ex) { // All other exceptions
                    $exception = $ex->getMessage();
                }
                if ($exception) {
                    Flash::error();
                }
                return Redirect::to(self::$currentPage);
            }
        }
    }

    /**
     * Make an OAuth2 request to Google to get a token
     */
    public function onGoogle()
    {
        if (Session::has('provider')) {
            Session::remove('provider');
        }
        Session::put('provider', 'google');
        $provider = $this->getProvider();
        $authUrl = $provider->getAuthorizationUrl();
        Session::flash('oauth2state', $provider->getState());
        return Redirect::to($authUrl);
    }

    /**
     * @var Provider The provider object for this component.
     */
    private function getProvider()
    {
        return new \League\OAuth2\Client\Provider\Google([
            'clientId'      => Settings::get('google_id'),
            'clientSecret'  => Settings::get('google_secret'),
            'redirectUri'   => $this->pageUrl($this->property('redirect')) . '/',
            'scopes'        => ['profile','email'],
        ]);
    }
}
