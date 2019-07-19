<?php namespace Mohsin\Social;

use Event;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
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
            'name' => 'mohsin.social::lang.plugin.name',
            'description' => 'mohsin.social::lang.plugin.description',
            'author' => 'Saifur Rahman Mohsin',
            'icon' => 'icon-users'
        ];
    }

    public function boot()
    {
        UserModel::extend(function ($model) {
            $model->hasOne['social'] = ['Mohsin\Social\Models\Social'];
        });

        UsersController::extendFormFields(function ($form, $model, $context) {

            if (!$model instanceof UserModel) {
                return;
            }

            if (!$model->exists) {
                return;
            }

            // Ensure that the social model always exists!
            SocialModel::getFromUser($model);

            $form->addTabFields([

                'social[facebook]' => [
                    'label' => 'mohsin.social::lang.social.facebook_id',
                    'tab' => 'mohsin.social::lang.plugin.name',
                  ],
                'social[google]' => [
                    'label' => 'mohsin.social::lang.social.google_id',
                    'tab' => 'mohsin.social::lang.plugin.name',
                  ],
                'social[github]' => [
                    'label' => 'mohsin.social::lang.social.github_id',
                    'tab' => 'mohsin.social::lang.plugin.name',
                  ],
                'social[github_url]' => [
                    'label' => 'mohsin.social::lang.social.github_url',
                    'tab' => 'mohsin.social::lang.plugin.name',
                  ],
                'social[linkedin]' => [
                    'label' => 'mohsin.social::lang.social.linkedin_id',
                    'tab' => 'mohsin.social::lang.plugin.name',
                  ],
                'social[linkedin_url]' => [
                    'label' => 'mohsin.social::lang.social.linkedin_url',
                    'tab' => 'mohsin.social::lang.plugin.name',
                  ],
                'social[microsoft]' => [
                    'label' => 'mohsin.social::lang.social.microsoft_id',
                    'tab' => 'mohsin.social::lang.plugin.name',
                  ],
                'social[microsoft_url]' => [
                    'label' => 'mohsin.social::lang.social.microsoft_url',
                    'tab' => 'mohsin.social::lang.plugin.name',
                  ],

              ]);
        });
    }

    public function registerComponents()
    {
        return [
            'Mohsin\Social\Components\Facebook'   => 'facebook',
            'Mohsin\Social\Components\Google'     => 'google',
            'Mohsin\Social\Components\Github'     => 'github',
            'Mohsin\Social\Components\LinkedIn'   => 'linkedin',
            'Mohsin\Social\Components\Microsoft'  => 'microsoft'
        ];
    }

    public function registerPermissions()
    {
        return [
            'mohsin.social.access_settings' => ['tab' => 'rainlab.user::lang.plugin.tab', 'label' => 'mohsin.social::lang.plugin.access_settings']
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'mohsin.social::lang.settings.name',
                'description' => 'mohsin.social::lang.settings.description',
                'category'    => SettingsManager::CATEGORY_USERS,
                'icon'        => 'icon-users',
                'class'       => 'Mohsin\Social\Models\Settings',
                'order'       => 502,
                'permissions' => ['mohsin.social.access_settings']
            ]
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'ucfirst' => 'ucfirst'
            ]
        ];
    }
}
