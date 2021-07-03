<?php
/*
 * File name: EServiceReviewsOfUserCriteria.php
 * Last modified: 2021.03.23 at 11:47:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Criteria\EServiceReviews;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class EServiceReviewsOfUserCriteria.
 *
 * @package namespace App\Criteria\EServiceReviews;
 */
class EServiceReviewsOfUserCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * EServiceReviewsOfUserCriteria constructor.
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
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            return $model->select('e_service_reviews.*');
        } else if (auth()->check() && auth()->user()->hasRole('provider')) {
            return $model->join("e_services", "e_services.id", "=", "e_service_reviews.e_service_id")
                ->join("e_provider_users", "e_provider_users.e_provider_id", "=", "e_services.e_provider_id")
                ->where('e_provider_users.user_id', $this->userId)
                ->groupBy('e_service_reviews.id')
                ->select('e_service_reviews.*');
        } else if (auth()->check() && auth()->user()->hasRole('customer')) {
            return $model->newQuery()->where('e_service_reviews.user_id', $this->userId)
                ->select('e_service_reviews.*');
        } else {
            return $model->select('e_service_reviews.*');
        }
    }
}
