<?php
/*
 * File name: HasTranslations.php
 * Last modified: 2021.04.12 at 19:39:57
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Traits;

use Spatie\Translatable\HasTranslations as BaseHasTranslations;

trait HasTranslations
{
    use BaseHasTranslations;

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = parent::toArray();
        foreach ($this->getTranslatableAttributes() as $field) {
            if (isset($attributes[$field]) && isJson($attributes[$field])) {
                $attributes[$field] = json_decode($attributes[$field]);
            }
        }
        return $attributes;
    }

    public function getAttributeValue($key)
    {
        if (!$this->isTranslatableAttribute($key)) {
            return parent::getAttributeValue($key);
        } elseif (!isJson(parent::getAttributeValue($key))) {
            return parent::getAttributeValue($key);
        }
        return $this->getTranslation($key, $this->getLocale());

    }

    public function getCasts(): array
    {
        return array_merge(
            parent::getCasts(),
            array_fill_keys($this->getTranslatableAttributes(), 'string')
        );
    }

    /**
     * Encode the given value as JSON.
     *
     * @param mixed $value
     * @return string
     */
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}


