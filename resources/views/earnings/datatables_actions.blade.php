<div class='btn-group btn-group-sm'>
    @can('earnings.create')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.e_provider_payout_create')}}" href="{{ isset($e_provider_id) ? route('eProviderPayouts.create', $e_provider_id ) : "#" }}" class='btn btn-link'>
            <i class="fas fa-money-bill-wave"></i> </a>
    @endcan

</div>
