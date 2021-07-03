<?php
/*
 * File name: NearCriteria.php
 * Last modified: 2021.03.25 at 16:41:38
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Criteria\EServices;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class NearCriteria.
 *
 * @package namespace App\Criteria\EServices;
 */
class NearCriteria implements CriteriaInterface
{
    /**
     * @var array
     */
    private $request;

    /**
     * NearCriteria constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
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
        if ($this->request->has(['myLon', 'myLat'])) {
            $myLat = $this->request->get('myLat');
            $myLon = $this->request->get('myLon');
            return $model->near($myLat, $myLon);
        } else {
            return $model->orderBy('available');
        }
    }
}
