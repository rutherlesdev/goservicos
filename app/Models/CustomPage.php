<?php
/*
 * File name: CustomPage.php
 * Last modified: 2021.04.12 at 09:51:43
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;

/**
 * Class CustomPage
 * @package App\Models
 * @version February 24, 2021, 10:28 am CET
 *
 * @property string title
 * @property string content
 * @property boolean published
 */
class CustomPage extends Model
{

    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required|max:127',
        'content' => 'required'
    ];
    public $translatable = [
        'title',
        'content',
    ];
    public $table = 'custom_pages';
    public $fillable = [
        'title',
        'content',
        'published'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'string',
        'content' => 'string'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',

    ];

    public function getCustomFieldsAttribute()
    {
        $hasCustomField = in_array(static::class, setting('custom_field_models', []));
        if (!$hasCustomField) {
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields', 'custom_fields.id', '=', 'custom_field_values.custom_field_id')
            ->where('custom_fields.in_table', '=', true)
            ->get()->toArray();

        return convertToAssoc($array, 'name');
    }

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }


}
