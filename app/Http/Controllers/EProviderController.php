<?php
/*
 * File name: EProviderController.php
 * Last modified: 2021.04.14 at 05:59:15
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers;

use App\Criteria\Addresses\AddressesOfUserCriteria;
use App\Criteria\EProviders\EProvidersOfUserCriteria;
use App\Criteria\Users\EProvidersCustomersCriteria;
use App\DataTables\EProviderDataTable;
use App\DataTables\RequestedEProviderDataTable;
use App\Events\EProviderChangedEvent;
use App\Http\Requests\CreateEProviderRequest;
use App\Http\Requests\UpdateEProviderRequest;
use App\Repositories\AddressRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\EProviderRepository;
use App\Repositories\EProviderTypeRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
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
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class EProviderController extends Controller
{
    /** @var  EProviderRepository */
    private $eProviderRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var EProviderTypeRepository
     */
    private $eProviderTypeRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var AddressRepository
     */
    private $addressRepository;
    /**
     * @var TaxRepository
     */
    private $taxRepository;

    public function __construct(EProviderRepository $eProviderRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo
        , EProviderTypeRepository $eProviderTypeRepo
        , UserRepository $userRepo
        , AddressRepository $addressRepo
        , TaxRepository $taxRepo)
    {
        parent::__construct();
        $this->eProviderRepository = $eProviderRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->eProviderTypeRepository = $eProviderTypeRepo;
        $this->userRepository = $userRepo;
        $this->addressRepository = $addressRepo;
        $this->taxRepository = $taxRepo;
    }

    /**
     * Display a listing of the EProvider.
     *
     * @param EProviderDataTable $eProviderDataTable
     * @return mixed
     */
    public function index(EProviderDataTable $eProviderDataTable)
    {
        return $eProviderDataTable->render('e_providers.index');
    }

    /**
     * Display a listing of the EProvider.
     *
     * @param EProviderDataTable $eproviderDataTable
     * @return mixed
     */
    public function requestedEProviders(RequestedEProviderDataTable $requestedEProviderDataTable)
    {
        return $requestedEProviderDataTable->render('e_providers.requested');
    }

    /**
     * Show the form for creating a new EProvider.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $eProviderType = $this->eProviderTypeRepository->pluck('name', 'id');
        $user = $this->userRepository->getByCriteria(new EProvidersCustomersCriteria())->pluck('name', 'id');
        $address = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria(auth()->id()))->pluck('address', 'id');
        $tax = $this->taxRepository->pluck('name', 'id');
        $usersSelected = [];
        $addressesSelected = [];
        $taxesSelected = [];
        $hasCustomField = in_array($this->eProviderRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->eProviderRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('e_providers.create')->with("customFields", isset($html) ? $html : false)->with("eProviderType", $eProviderType)->with("user", $user)->with("usersSelected", $usersSelected)->with("address", $address)->with("addressesSelected", $addressesSelected)->with("tax", $tax)->with("taxesSelected", $taxesSelected);
    }

    /**
     * Store a newly created EProvider in storage.
     *
     * @param CreateEProviderRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateEProviderRequest $request)
    {
        $input = $request->all();
        if (auth()->user()->hasRole(['provider', 'customer'])) {
            $input['users'] = [auth()->id()];
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->eProviderRepository->model());
        try {
            $eProvider = $this->eProviderRepository->create($input);
            $eProvider->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($eProvider, 'image');
                }
            }
            event(new EProviderChangedEvent($eProvider, $eProvider));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.e_provider')]));

        return redirect(route('eProviders.index'));
    }

    /**
     * Display the specified EProvider.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function show(int $id)
    {
        $this->eProviderRepository->pushCriteria(new EProvidersOfUserCriteria(auth()->id()));
        $eProvider = $this->eProviderRepository->findWithoutFail($id);

        if (empty($eProvider)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.e_provider')]));

            return redirect(route('eProviders.index'));
        }

        return view('e_providers.show')->with('eProvider', $eProvider);
    }

    /**
     * Show the form for editing the specified EProvider.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $this->eProviderRepository->pushCriteria(new EProvidersOfUserCriteria(auth()->id()));
        $eProvider = $this->eProviderRepository->findWithoutFail($id);
        if (empty($eProvider)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.e_provider')]));
            return redirect(route('eProviders.index'));
        }
        $eProviderType = $this->eProviderTypeRepository->pluck('name', 'id');
        $user = $this->userRepository->getByCriteria(new EProvidersCustomersCriteria())->pluck('name', 'id');
        $address = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria(auth()->id()))->pluck('address', 'id');
        $tax = $this->taxRepository->pluck('name', 'id');
        $usersSelected = $eProvider->users()->pluck('users.id')->toArray();
        $addressesSelected = $eProvider->addresses()->pluck('addresses.id')->toArray();
        $taxesSelected = $eProvider->taxes()->pluck('taxes.id')->toArray();

        $customFieldsValues = $eProvider->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->eProviderRepository->model());
        $hasCustomField = in_array($this->eProviderRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('e_providers.edit')->with('eProvider', $eProvider)->with("customFields", isset($html) ? $html : false)->with("eProviderType", $eProviderType)->with("user", $user)->with("usersSelected", $usersSelected)->with("address", $address)->with("addressesSelected", $addressesSelected)->with("tax", $tax)->with("taxesSelected", $taxesSelected);
    }

    /**
     * Update the specified EProvider in storage.
     *
     * @param int $id
     * @param UpdateEProviderRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateEProviderRequest $request)
    {
        $this->eProviderRepository->pushCriteria(new EProvidersOfUserCriteria(auth()->id()));
        $oldEProvider = $this->eProviderRepository->findWithoutFail($id);

        if (empty($oldEProvider)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.e_provider')]));
            return redirect(route('eProviders.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->eProviderRepository->model());
        try {
            $input['users'] = isset($input['users']) ? $input['users'] : [];
            $input['addresses'] = isset($input['addresses']) ? $input['addresses'] : [];
            $input['taxes'] = isset($input['taxes']) ? $input['taxes'] : [];
            $eProvider = $this->eProviderRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($eProvider, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $eProvider->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
            event(new EProviderChangedEvent($eProvider, $oldEProvider));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.e_provider')]));

        return redirect(route('eProviders.index'));
    }

    /**
     * Remove the specified EProvider from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function destroy(int $id)
    {
        if (config('installer.demo_app')) {
            Flash::warning('This is only demo app you can\'t change this section ');
            return redirect(route('eProviders.index'));
        }
        $this->eProviderRepository->pushCriteria(new EProvidersOfUserCriteria(auth()->id()));
        $eProvider = $this->eProviderRepository->findWithoutFail($id);

        if (empty($eProvider)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.e_provider')]));

            return redirect(route('eProviders.index'));
        }

        $this->eProviderRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.e_provider')]));

        return redirect(route('eProviders.index'));
    }

    /**
     * Remove Media of EProvider
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $eProvider = $this->eProviderRepository->findWithoutFail($input['id']);
        try {
            if ($eProvider->hasMedia($input['collection'])) {
                $eProvider->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
