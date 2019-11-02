<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserModel extends Model
{
    protected $table = 'user_config';

    public static function getDefaultConfig()
    {
        $json_s = Storage::get('setting.json');
        return json_decode($json_s, true);
    }

    public static function getConfig($id)
    {
        $user_config_m = self::where('uid', $id)->get()[0];
        $other_config_m = self::where('uid', -1)->get()[0];
        $user_config = [
            'tinymce_setting' => json_decode($user_config_m->tinymce_setting),
            'ace_setting' => json_decode($user_config_m->ace_setting),
            'xk_setting' => json_decode($user_config_m->xk_setting)
        ];
        $other_config = [
            'tinymce_setting' => json_decode($other_config_m->tinymce_setting),
            'ace_setting' => json_decode($other_config_m->ace_setting),
            'xk_setting' => json_decode($other_config_m->xk_setting)
        ];
        $config = array_merge_recursive($user_config, $other_config);
        return [
            'tinymceSetting' => $config['tinymce_setting'],
            'aceSetting' => $config['ace_setting'],
            'xkSetting' => $config['xk_setting']
        ];
    }
}
