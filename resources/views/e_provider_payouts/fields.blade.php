@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Method Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('method', trans("lang.e_provider_payout_method"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('method', ['Bank' => trans('lang.bank'),'Cash'=> trans('lang.cash')], null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.e_provider_payout_method_help") }}</div>
        </div>
    </div>

    <!-- Note Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('note', trans("lang.e_provider_payout_note"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('note', null, ['class' => 'form-control','placeholder'=>
             trans("lang.e_provider_payout_note_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.e_provider_payout_note_help") }}</div>
        </div>
    </div>

</div>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- E Provider Id Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('e_provider_id', trans("lang.e_provider_payout_e_provider_id"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {{Form::hidden('e_provider_id', request('id'))}}
            {{$eProvider->name}}
        </div>
    </div>

    <!-- Amount Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('amount', trans("lang.e_provider_payout_amount"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {{Form::hidden('amount', $amount)}}
            {!! getPrice($amount)  !!}
        </div>
    </div>

</div>
@if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
        {!! $customFields !!}
    </div>
@endif
<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fas fa-save"></i> {{trans('lang.save')}} {{trans('lang.e_provider_payout')}}</button>
    <a href="{!! route('eProviderPayouts.index') !!}" class="btn btn-default"><i class="fas fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
