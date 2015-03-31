<?php namespace Mohsin\Social;

use Event;
use System\Classes\PluginBase;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Controllers\Users as UsersController;
use Mohsin\Social\Models\Social as SocialModel;

/**
 * Social Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'mohsin.social::lang.plugin.name',
            'description' => 'mohsin.social::lang.plugin.description',
            'author'      => 'Saifur Rahman Mohsin',
            'icon'        => 'icon-users'
        ];
    }

    public function boot()
    {
      UserModel::extend(function($model){
          $model -> hasOne['social'] = ['Mohsin\Social\Models\Social'];
      });

      UsersController::extendFormFields(function($form, $model, $context){

          if(!$model instanceof UserModel)
              return;

          if(!$model->exists)
            return;

          // Ensure that the social model always exists!
          SocialModel::getFromUser($model);

          $form->addTabFields([

              'social[facebook]' => [
                  'label' => 'Facebook ID',
                  'tab' => 'mohsin.social::lang.settings.social',
                ],
              'social[google]' => [
                  'label' => 'Google ID',
                  'tab' => 'mohsin.social::lang.settings.social',
                ],
              'social[github]' => [
                  'label' => 'Github ID',
                  'tab' => 'mohsin.social::lang.settings.social',
                ],
              'social[linkedin]' => [
                  'label' => 'LinkedIn ID',
                  'tab' => 'mohsin.social::lang.settings.social',
                ],
              'social[microsoft]' => [
                  'label' => 'Microsoft ID',
                  'tab' => 'mohsin.social::lang.settings.social',
                ],

            ]);

      });
    }

    public function registerComponents()
    {
        return [
            'Mohsin\Social\Components\Facebook' => 'facebook',
            'Mohsin\Social\Components\Google' => 'google',
            'Mohsin\Social\Components\Github' => 'github',
            'Mohsin\Social\Components\LinkedIn' => 'linkedin',
            'Mohsin\Social\Components\Microsoft' => 'microsoft'
        ];
    }

    public function registerSettings()
    {
      return [
        'settings' => [
          'label'       => 'mohsin.social::lang.plugin.name',
          'description' => 'mohsin.social::lang.settings.description',
          'category'    => 'rainlab.user::lang.settings.users',
          'icon'        => 'icon-users',
          'class'       => 'Mohsin\Social\Models\Settings',
          'order'       => 500
        ],
      ];
    }

}
