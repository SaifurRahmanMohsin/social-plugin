<?php namespace Mohsin\Social\Components;

use Lang;
use Input;
use Flash;
use Session;
use Redirect;
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

  public function onRun()
  {
      // Initialize the provider
      $this -> setProvider();

      // Set the current page
      self::$currentPage = $this -> currentPageUrl();
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
            Flash::error(Lang::get('mohsin.social::lang.errors.redirect_uri_mismatch') . $currentPage);
              break;
          case 'bad_verification_code':
            Flash::error(Lang::get('mohsin.social::lang.errors.bad_verification_code', ['provider' => $chosenProvider ]) . $currentPage);
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

}