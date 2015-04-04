<?php namespace Mohsin\Social\Components;

use Auth;
use Lang;
use Input;
use Flash;
use Session;
use Redirect;
use Validator;
use System\Models\File;
use October\Rain\Network\Http;
use Mohsin\Social\Models\Settings;
use RainLab\User\Components\Account;

class BaseProviderComponent extends Account
{
  /**
   * @var String provider for current
   */
  protected $provider = null;

  protected static $currentPage = null;

  public function setProvider()
  {
    $this -> provider = (new \ReflectionClass($this)) -> getShortName();
  }

  public function defineProperties()
  {
      $properties = array(
          'redirect' => [
              'title'       => 'rainlab.user::lang.account.redirect_to',
              'description' => 'rainlab.user::lang.account.redirect_to_desc',
              'type'        => 'dropdown',
              'default'     => ''
          ],
      );
    if(Settings::get('use_styles'))
      {
          $properties['size'] = array(
                'title'       => 'mohsin.social::lang.component.btn_size',
                'description' => 'mohsin.social::lang.component.btn_desc',
                'type'        => 'dropdown',
                'default'     => ''
          );
      }
    return $properties;
  }

  public function getsizeOptions()
  {
    return [
      '' => 'Normal',
      'lg' => 'Large',
      'sm' => 'Small',
      'xs' => 'Extra Small',
      ];
  }

  public function onRun()
  {
      // Initialize the provider
      $this -> setProvider();

      // Set the current page
      self::$currentPage = $this -> currentPageUrl();

      // Inject bootstrap social icons
      $this -> addCss('/plugins/mohsin/social/assets/css/bootstrap-social.css');
      $this -> page['useButton'] = Settings::get('use_styles');
      $this -> page['showIcon'] = Settings::get('show_icon');
      $this -> page['showText'] = Settings::get('show_text');
      $this -> page[strtolower($this -> provider) . '_btn_size'] = $this -> property('size');
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

  public function hasErrors()
  {
    /**
     * Check if there are any errors from the previous request.
     */

    $currentPage = $this -> currentPageUrl();
    if (Input::has('error'))
      {
        if(Input::has('error_reason'))
          $reason = Input::get('error_reason');
        else
          $reason = Input::get('error');
        switch($reason)
        {
          case 'user_denied':
            Flash::error(Lang::get('mohsin.social::lang.errors.user_denied'));
            break;
          case 'access_denied':
            Flash::error(Lang::get('mohsin.social::lang.errors.access_denied'));
            break;
          case 'redirect_uri_mismatch':
            Flash::error(Lang::get('mohsin.social::lang.errors.redirect_uri_mismatch') . self::$currentPage);
              break;
          case 'bad_verification_code':
            Flash::error(Lang::get('mohsin.social::lang.errors.bad_verification_code', ['provider' => $chosenProvider ]) . self::$currentPage);
              break;
          case 'incorrect_client_credentials':
            Flash::error(Lang::get('mohsin.social::lang.errors.incorrect_client_credentials'));
              break;
          default:
            Flash::error(Lang::get('mohsin.social::lang.errors.error_occured') . ': ' . $reason);
        }
        return true;
      }
    return false;
  }

  /**
   * Saves an image to disk.
   *
   * @param  string $imageUrl
   * @return File
   */
  public function addImage($imageUrl)
  {
      // Save image to disk
      $file = new File;
      $tempFile = $file -> getLocalTempPath() . 'jpg';
      $file -> copyStorageToLocal($file -> getDiskPath(), $tempFile);
      $result = Http::get($imageUrl);
      $result -> toFile($tempFile);
      $result -> send();
      $file -> fromFile($tempFile);
      $file -> data = $tempFile;
      $file -> save();
      return $file;
  }

}