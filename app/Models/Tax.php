<?php
/*
 * File name: Tax.php
 * Last modified: 2021.04.12 at 10:00:01
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;

/**
 * Class Tax
 * @package App\Models
 * @version January 13, 2021, 11:43 am UTC
 *
 * @property integer id
 * @property string name
 * @property double value
 * @property string type
 */
class Tax extends Model
{

    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|max:127',
        'value' => 'required|max:99999999,99|min:0',
        'type' => 'required|max:50'
    ];
    public $translatable = [
        'name',
    ];
    public $table = 'taxes';
    public $fillable = [
        'name',
        'value',
        'type'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'value' => 'double',
        'type' => 'string'
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
