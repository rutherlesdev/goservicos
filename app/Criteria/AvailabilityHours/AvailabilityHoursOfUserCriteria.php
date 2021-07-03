<?php
/*
 * File name: AvailabilityHoursOfUserCriteria.php
 * Last modified: 2021.03.23 at 11:46:05
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Criteria\AvailabilityHours;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class AvailabilityHoursOfUserCriteria.
 *
 * @package namespace App\Criteria\AvailabilityHours;
 */
class AvailabilityHoursOfUserCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * AvailabilityHoursOfUserCriteria constructor.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if (auth()->check() && auth()->user()->hasRole('provider')) {
            return $model->join('e_provider_users', 'e_provider_users.e_provider_id', '=', 'availability_hours.e_provider_id')
                ->groupBy('availability_hours.id')
                ->select('availability_hours.*')
                ->where('e_provider_users.user_id', $this->userId);
        } else {
            return $model;
        }
    }
}
