<?php namespace Mohsin\Social\Models;

use Model;

class Settings extends Model
{

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'social_settings';

    public $settingsFields = 'fields.yaml';

}