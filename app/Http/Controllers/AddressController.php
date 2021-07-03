<?php
/*
 * File name: AddressController.php
 * Last modified: 2021.03.21 at 12:19:50
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers;

use App\Criteria\Addresses\AddressesOfUserCriteria;
use App\DataTables\AddressDataTable;
use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Repositories\AddressRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UserRepository;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class AddressController extends Controller
{
    /** @var  AddressRepository */
    private $addressRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(AddressRepository $addressRepo, CustomFieldRepository $customFieldRepo, UserRepository $userRepo)
    {
        parent::__construct();
        $this->addressRepository = $addressRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * Display a listing of the Address.
     *
     * @param AddressDataTable $addressDataTable
     * @return mixed
     */
    public function index(AddressDataTable $addressDataTable)
    {
        return $addressDataTable->render('addresses.index');
    }

    /**
     * Show the form for creating a new Address.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $hasCustomField = in_array($this->addressRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('addresses.create')->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created Address in storage.
     *
     * @param CreateAddressRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateAddressRequest $request)
    {
        $input = $request->all();
        $input['user_id'] = Auth::id();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
        try {
            $address = $this->addressRepository->create($input);
            $address->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.address')]));

        return redirect(route('addresses.edit', $address->id));
    }

    /**
     * Display the specified Address.
     *
     * @param int $id
     *
     * @return Application|Factory|Response|View
     * @throws RepositoryException
     */
    public function show(int $id)
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error('Address not found');

            return redirect(route('addresses.index'));
        }

        return view('addresses.show')->with('address', $address);
    }

    /**
     * Show the form for editing the specified Address.
     *
     * @param int $id
     *
     * @return Application|Factory|Response|View
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.address')]));

            return redirect(route('addresses.index'));
        }
        $customFieldsValues = $address->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
        $hasCustomField = in_array($this->addressRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('addresses.edit')->with('address', $address)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified Address in storage.
     *
     * @param int $id
     * @param UpdateAddressRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateAddressRequest $request)
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error('Address not found');
            return redirect(route('addresses.index'));
        }
        $input = $request->all();
        $input['user_id'] = $address->user->id;
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
        try {
            $address = $this->addressRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $address->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.address')]));

        return redirect(route('addresses.index'));
    }

    /**
     * Remove the specified Address from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function destroy(int $id)
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error('Address not found');

            return redirect(route('addresses.index'));
        }

        $this->addressRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.address')]));

        return redirect(route('addresses.index'));
    }
}
