<h5 class="col-12 pb-4">{!! trans('lang.app_setting_paypal_credentials') !!}</h5>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Boolean Enabled Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('enable_paypal', trans("lang.app_setting_enable_paypal"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        {!! Form::hidden('enable_paypal', 0, ['id'=>"hidden_enable_paypal"]) !!}
        <div class="col-9 icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('enable_paypal', 1, setting('enable_paypal')) !!}
            <label for="enable_paypal"></label>
        </div>
    </div>
    <!-- Route Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('paypal_username', trans("lang.app_setting_paypal_username"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('paypal_username', setting('paypal_username'),  ['class' => 'form-control','placeholder'=>  trans("lang.app_setting_paypal_username_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.app_setting_paypal_username_help") }}
            </div>
        </div>
    </div>
    <!-- Route Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('paypal_secret', trans("lang.app_setting_paypal_secret"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('paypal_secret', setting('paypal_secret'),  ['class' => 'form-control','placeholder'=>  trans("lang.app_setting_paypal_secret_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.app_setting_paypal_secret_help") }}
            </div>
        </div>
    </div>
</div>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Boolean Enabled Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('paypal_mode', trans("lang.app_setting_paypal_mode"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        {!! Form::hidden('paypal_mode', 0, ['id'=>"hidden_paypal_mode"]) !!}
        <div class="col-9 icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('paypal_mode', 1, setting('paypal_mode')) !!}
            <label for="paypal_mode"></label>
        </div>
    </div>
    <!-- Route Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('paypal_password', trans("lang.app_setting_paypal_password"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('paypal_password', setting('paypal_password'),  ['class' => 'form-control','placeholder'=>  trans("lang.app_setting_paypal_password_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.app_setting_paypal_password_help") }}
            </div>
        </div>
    </div>
    <!-- Route Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('paypal_app_id', trans("lang.app_setting_paypal_app_id"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('paypal_app_id', setting('paypal_app_id'),  ['class' => 'form-control','placeholder'=>  trans("lang.app_setting_paypal_app_id_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.app_setting_paypal_app_id_help") }}
            </div>
        </div>
    </div>
</div>
