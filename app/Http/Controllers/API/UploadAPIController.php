<?php
/*
 * File name: UploadAPIController.php
 * Last modified: 2021.06.10 at 20:38:02
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadRequest;
use App\Repositories\UploadRepository;
use Exception;
use Prettus\Validator\Exceptions\ValidatorException;

class UploadAPIController extends Controller
{
    private $uploadRepository;

    /**
     * UploadController constructor.
     * @param UploadRepository $uploadRepository
     */
    public function __construct(UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->uploadRepository = $uploadRepository;
    }

    /**
     * @param UploadRequest $request
     */
    public function store(UploadRequest $request)
    {
        $input = $request->all();
        try {
            $upload = $this->uploadRepository->create($input);
            $upload->addMedia($input['file'])
                ->withCustomProperties(['uuid' => $input['uuid'], 'user_id' => auth()->id()])
                ->toMediaCollection($input['field']);
            return $this->sendResponse($input['uuid'], "Uploaded Successfully");
        } catch (ValidatorException $e) {
            return $this->sendResponse(false, $e->getMessage());
        }
    }

    /**
     * clear cache from Upload table
     */
    public function clear(UploadRequest $request)
    {
        $input = $request->all();
        if (!isset($input['uuid'])) {
            return $this->sendResponse(false, 'Media not found');
        }
        try {
            if (is_array($input['uuid'])) {
                $result = $this->uploadRepository->clearWhereIn($input['uuid']);
            } else {
                $result = $this->uploadRepository->clear($input['uuid']);
            }
            return $this->sendResponse($result, 'Media deleted successfully');
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error when delete media');
        }

    }
}
