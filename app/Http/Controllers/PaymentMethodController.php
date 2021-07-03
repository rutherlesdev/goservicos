<?php
/*
 * File name: PaymentMethodController.php
 * Last modified: 2021.05.07 at 19:12:31
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers;

use App\DataTables\PaymentMethodDataTable;
use App\Http\Requests\CreatePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Repositories\CustomFieldRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\UploadRepository;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Validator\Exceptions\ValidatorException;

class PaymentMethodController extends Controller
{
    /** @var  PaymentMethodRepository */
    private $paymentMethodRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(PaymentMethodRepository $paymentMethodRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->paymentMethodRepository = $paymentMethodRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the PaymentMethod.
     *
     * @param PaymentMethodDataTable $paymentMethodDataTable
     * @return Response
     */
    public function index(PaymentMethodDataTable $paymentMethodDataTable)
    {
        return $paymentMethodDataTable->render('payment_methods.index');
    }

    /**
     * Show the form for creating a new PaymentMethod.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $hasCustomField = in_array($this->paymentMethodRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->paymentMethodRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('payment_methods.create')->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created PaymentMethod in storage.
     *
     * @param CreatePaymentMethodRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreatePaymentMethodRequest $request)
    {
        $input = $request->all();
        $input['order'] = $input['order'] ?: 0;
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->paymentMethodRepository->model());
        try {
            $paymentMethod = $this->paymentMethodRepository->create($input);
            $paymentMethod->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['logo']) && $input['logo']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['logo']);
                $mediaItem = $cacheUpload->getMedia('logo')->first();
                $mediaItem->copy($paymentMethod, 'logo');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.payment_method')]));

        return redirect(route('paymentMethods.index'));
    }

    /**
     * Display the specified PaymentMethod.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function show($id)
    {
        $paymentMethod = $this->paymentMethodRepository->findWithoutFail($id);

        if (empty($paymentMethod)) {
            Flash::error('Payment Method not found');

            return redirect(route('paymentMethods.index'));
        }

        return view('payment_methods.show')->with('paymentMethod', $paymentMethod);
    }

    /**
     * Show the form for editing the specified PaymentMethod.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function edit(int $id)
    {
        $paymentMethod = $this->paymentMethodRepository->findWithoutFail($id);


        if (empty($paymentMethod)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.payment_method')]));

            return redirect(route('paymentMethods.index'));
        }
        $customFieldsValues = $paymentMethod->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->paymentMethodRepository->model());
        $hasCustomField = in_array($this->paymentMethodRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('payment_methods.edit')->with('paymentMethod', $paymentMethod)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified PaymentMethod in storage.
     *
     * @param int $id
     * @param UpdatePaymentMethodRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function update(int $id, UpdatePaymentMethodRequest $request)
    {
        if (config('installer.demo_app')) {
            Flash::warning('This is only demo app you can\'t change this section ');
        } else {
            $paymentMethod = $this->paymentMethodRepository->findWithoutFail($id);

            if (empty($paymentMethod)) {
                Flash::error('Payment Method not found');
                return redirect(route('paymentMethods.index'));
            }
            $input = $request->all();
            $input['order'] = $input['order'] ?: 0;
            $input['enabled'] = $this->getEnabledValue($input);
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->paymentMethodRepository->model());
            try {
                $paymentMethod = $this->paymentMethodRepository->update($input, $id);

                if (isset($input['logo']) && $input['logo']) {
                    $cacheUpload = $this->uploadRepository->getByUuid($input['logo']);
                    $mediaItem = $cacheUpload->getMedia('logo')->first();
                    $mediaItem->copy($paymentMethod, 'logo');
                }
                foreach (getCustomFieldsValues($customFields, $request) as $value) {
                    $paymentMethod->customFieldsValues()
                        ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
                }
                $input = array_filter($input, function ($key) {
                    return preg_match('/(paypal|razorpay|stripe|cash)/i', $key);
                }, ARRAY_FILTER_USE_KEY);
                setting($input)->save();
            } catch (ValidatorException $e) {
                Flash::error($e->getMessage());
            }

            Flash::success(__('lang.updated_successfully', ['operator' => __('lang.payment_method')]));
        }
        return redirect(route('paymentMethods.index'));
    }

    private function getEnabledValue($input): bool
    {
        $array = array_filter($input, function ($key) {
            return preg_match('/(enable_)/i', $key);
        }, ARRAY_FILTER_USE_KEY);

        if (count($array)) {
            return array_values($array)[0];
        }
        return false;
    }

    /**
     * Remove the specified PaymentMethod from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $paymentMethod = $this->paymentMethodRepository->findWithoutFail($id);

        if (empty($paymentMethod)) {
            Flash::error('Payment Method not found');

            return redirect(route('paymentMethods.index'));
        }

        $this->paymentMethodRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.payment_method')]));

        return redirect(route('paymentMethods.index'));
    }

    /**
     * Remove Media of PaymentMethod
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $paymentMethod = $this->paymentMethodRepository->findWithoutFail($input['id']);
        try {
            if ($paymentMethod->hasMedia($input['collection'])) {
                $paymentMethod->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
