<?php
/*
 * File name: PaymentStatus.php
 * Last modified: 2021.04.12 at 10:00:01
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;

/**
 * Class PaymentStatus
 * @package App\Models
 * @version January 7, 2021, 4:47 pm UTC
 *
 * @property string status
 * @property integer order
 */
class PaymentStatus extends Model
{
    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'status' => 'required|max:127',
        'order' => 'numeric|min:0'
    ];
    public $translatable = [
        'status',
    ];
    public $table = 'payment_statuses';
    public $fillable = [
        'status',
        'order'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'string',
        'order' => 'integer'
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
