<h5 class="col-12 pb-4">{!! trans('lang.app_setting_cash_credentials') !!}</h5>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Boolean Enabled Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('enable_cash', trans("lang.app_setting_enable_cash"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        {!! Form::hidden('enable_cash', 0, ['id'=>"hidden_enable_cash"]) !!}
        <div class="col-9 icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('enable_cash', 1, setting('enable_cash')) !!}
            <label for="enable_cash"></label>
        </div>
    </div>
</div>
<div class="d-flex flex-column col-sm-12 col-md-6">

</div>
