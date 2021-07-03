<?php
/*
 * File name: EServicesOfUserCriteria.php
 * Last modified: 2021.03.23 at 11:38:55
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Criteria\EServices;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class EServicesOfUserCriteria.
 *
 * @package namespace App\Criteria\EServices;
 */
class EServicesOfUserCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * EServicesOfUserCriteria constructor.
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
            return $model->join('e_provider_users', 'e_provider_users.e_provider_id', '=', 'e_services.e_provider_id')
                ->groupBy('e_services.id')
                ->where('e_provider_users.user_id', $this->userId)
                ->select('e_services.*');
        } else {
            return $model->select('e_services.*')->groupBy('e_services.id');
        }
    }
}
