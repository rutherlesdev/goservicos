<?php
/*
 * File name: EProviderPayoutController.php
 * Last modified: 2021.03.25 at 16:41:38
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers;

use App\Criteria\EProviders\EProvidersOfUserCriteria;
use App\DataTables\EProviderPayoutDataTable;
use App\Http\Requests\CreateEProviderPayoutRequest;
use App\Repositories\CustomFieldRepository;
use App\Repositories\EarningRepository;
use App\Repositories\EProviderPayoutRepository;
use App\Repositories\EProviderRepository;
use Carbon\Carbon;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class EProviderPayoutController extends Controller
{
    /** @var  EProviderPayoutRepository */
    private $eProviderPayoutRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var EProviderRepository
     */
    private $eProviderRepository;
    /**
     * @var EarningRepository
     */
    private $earningRepository;

    public function __construct(EProviderPayoutRepository $eProviderPayoutRepo, CustomFieldRepository $customFieldRepo, EProviderRepository $eProviderRepo, EarningRepository $earningRepository)
    {
        parent::__construct();
        $this->eProviderPayoutRepository = $eProviderPayoutRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->eProviderRepository = $eProviderRepo;
        $this->earningRepository = $earningRepository;
    }

    /**
     * Display a listing of the EProviderPayout.
     *
     * @param EProviderPayoutDataTable $eProviderPayoutDataTable
     * @return Response
     */
    public function index(EProviderPayoutDataTable $eProviderPayoutDataTable)
    {
        return $eProviderPayoutDataTable->render('e_provider_payouts.index');
    }

    /**
     * Show the form for creating a new EProviderPayout.
     *
     * @param int $id
     * @return Application|Factory|Response|View
     * @throws RepositoryException
     */
    public function create(int $id)
    {
        $this->eProviderRepository->pushCriteria(new EProvidersOfUserCriteria(auth()->id()));
        $eProvider = $this->eProviderRepository->findWithoutFail($id);
        if (empty($eProvider)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.e_provider')]));
            return redirect(route('eProviderPayouts.index'));
        }
        $earning = $this->earningRepository->findByField('e_provider_id', $id)->first();
        $totalPayout = $this->eProviderPayoutRepository->findByField('e_provider_id', $id)->sum("amount");

        $hasCustomField = in_array($this->eProviderPayoutRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->eProviderPayoutRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('e_provider_payouts.create')->with("customFields", isset($html) ? $html : false)->with("eProvider", $eProvider)->with("amount", $earning->e_provider_earning - $totalPayout);
    }

    /**
     * Store a newly created EProviderPayout in storage.
     *
     * @param CreateEProviderPayoutRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateEProviderPayoutRequest $request)
    {
        $input = $request->all();
        $earning = $this->earningRepository->findByField('e_provider_id', $input['e_provider_id'])->first();
        $totalPayout = $this->eProviderPayoutRepository->findByField('e_provider_id', $input['e_provider_id'])->sum("amount");
        $input['amount'] = $earning->e_provider_earning - $totalPayout;
        if ($input['amount'] <= 0) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.earning')]));
            return redirect(route('eProviderPayouts.index'));
        }
        $input['paid_date'] = Carbon::now();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->eProviderPayoutRepository->model());
        try {
            $eProviderPayout = $this->eProviderPayoutRepository->create($input);
            $eProviderPayout->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.e_provider_payout')]));

        return redirect(route('eProviderPayouts.index'));
    }
}
