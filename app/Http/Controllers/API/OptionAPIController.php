<?php
/*
 * File name: OptionAPIController.php
 * Last modified: 2021.06.10 at 20:38:02
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\API;


use App\Criteria\Options\OptionsOfUserCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Repositories\OptionRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class OptionController
 * @package App\Http\Controllers\API
 */
class OptionAPIController extends Controller
{
    /** @var  OptionRepository */
    private $optionRepository;
    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(OptionRepository $optionRepo, UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->optionRepository = $optionRepo;
        $this->uploadRepository = $uploadRepository;
    }

    /**
     * Display a listing of the Option.
     * GET|HEAD /options
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->optionRepository->pushCriteria(new RequestCriteria($request));
            $this->optionRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $options = $this->optionRepository->all();

        return $this->sendResponse($options->toArray(), 'Options retrieved successfully');
    }

    /**
     * Display the specified Option.
     * GET|HEAD /options/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $this->optionRepository->pushCriteria(new RequestCriteria($request));
            $this->optionRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->optionRepository->pushCriteria(new OptionsOfUserCriteria(auth()->id()));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $option = $this->optionRepository->findWithoutFail($id);
        if (empty($option)) {
            return $this->sendError('Option not found');
        }

        return $this->sendResponse($option, 'Option retrieved successfully');
    }

    /**
     * Store a newly created EService in storage.
     *
     * @param CreateOptionRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreateOptionRequest $request): JsonResponse
    {
        $input = $request->all();
        try {
            $option = $this->optionRepository->create($input);
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($option, 'image');
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($option->toArray(), __('lang.saved_successfully', ['operator' => __('lang.option')]));
    }

    /**
     * Update the specified EService in storage.
     *
     * @param int $id
     * @param UpdateOptionRequest $request
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateOptionRequest $request): JsonResponse
    {
        $this->optionRepository->pushCriteria(new OptionsOfUserCriteria(auth()->id()));
        $option = $this->optionRepository->findWithoutFail($id);

        if (empty($option)) {
            return $this->sendError('Option not found');
        }
        $input = $request->all();
        try {
            $option = $this->optionRepository->update($input, $id);
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                if ($option->hasMedia('image')) {
                    $option->getFirstMedia('image')->delete();
                }
                $mediaItem->copy($option, 'image');
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($option->toArray(), __('lang.updated_successfully', ['operator' => __('lang.option')]));
    }

    /**
     * Remove the specified EService from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->optionRepository->pushCriteria(new OptionsOfUserCriteria(auth()->id()));
        $option = $this->optionRepository->findWithoutFail($id);

        if (empty($option)) {
            return $this->sendError('Option not found');
        }

        $option = $this->optionRepository->delete($id);

        return $this->sendResponse($option, __('lang.deleted_successfully', ['operator' => __('lang.option')]));

    }
}
