@extends('layouts.settings.default')
@push('css_lib')
    <!-- iCheck -->
    <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{asset('vendor/summernote/summernote-bs4.min.css')}}">
    {{--dropzone--}}
    <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
@endpush
@section('settings_title',trans('lang.user_table'))
@section('settings_content')
    @include('flash::message')
    @include('adminlte-templates::common.errors')
    <div class="clearfix"></div>
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="{!! url()->current() !!}"><i class="fas fa-money-bill mr-2"></i>{{trans('lang.app_setting_'.$tab)}}</a>
                </li>
                <div class="ml-auto d-inline-flex">
                    @can('currencies.index')
                        <li class="nav-item">
                            <a class="nav-link" href="{!! route('currencies.index') !!}"><i class="fas fa-dollar-sign mr-2"></i>{{trans('lang.currency_table')}}
                            </a>
                        </li>
                    @endcan
                </div>

            </ul>
        </div>
        <div class="card-body">
            {!! Form::open(['url' => ['settings/update'], 'method' => 'patch']) !!}
            <div class="row">
                <h5 class="col-12 pb-4"><i class="mr-3 fas fa-money"></i>{!! trans('lang.app_setting_default_tax') !!}</h5>
                <!-- default_tax Field -->
                <div class="form-group row col-6">
                    {!! Form::label('default_tax', trans('lang.app_setting_default_tax'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::text('default_tax', setting('default_tax'),  ['class' => 'form-control','placeholder'=>  trans('lang.app_setting_default_tax_placeholder')]) !!}
                        <div class="form-text text-muted">
                            {!! trans('lang.app_setting_default_tax_help') !!}
                        </div>
                    </div>
                </div>

                <h5 class="col-12 pb-4 custom-field-container"><i class="mr-3 fas fa-money"></i>{!! trans('lang.app_setting_default_currency') !!}</h5>
                <!-- default_currency Field -->
                <div class="form-group row col-6">
                    {!! Form::label('default_currency', trans('lang.app_setting_default_currency'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::select('default_currency',
                        $currencies
                        , setting('default_currency_id',1), ['class' => 'select2 form-control']) !!}
                        <div class="form-text text-muted">{{ trans("lang.app_setting_default_currency_help") }}</div>
                    </div>
                </div>

                <div class="form-group row col-6">
                    {!! Form::label('currency_right', trans('lang.app_setting_currency_right'),['class' => 'col-4 control-label text-right']) !!}
                    <div class="checkbox icheck">
                        <label class="w-100 ml-2 form-check-inline">
                            {!! Form::hidden('currency_right', null) !!}
                            {!! Form::checkbox('currency_right', 1, setting('currency_right', false)) !!}
                            <span class="ml-2">{!! trans('lang.app_setting_currency_right_help') !!}</span> </label>
                    </div>
                </div>

                <!-- Submit Field -->
                <div class="form-group mt-4 col-12 text-right">
                    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
                        <i class="fas fa-save"></i> {{trans('lang.save')}} {{trans('lang.app_setting_payment')}}
                    </button>
                    <a href="{!! route('users.index') !!}" class="btn btn-default"><i class="fas fa-undo"></i> {{trans('lang.cancel')}}</a>
                </div>
            </div>
            {!! Form::close() !!}
            <div class="clearfix"></div>
        </div>
    </div>
    </div>
    @include('layouts.media_modal',['collection'=>null])
@endsection
@push('scripts_lib')
    <!-- iCheck -->

    <!-- select2 -->
    <script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{asset('vendor/summernote/summernote.min.js')}}"></script>
    {{--dropzone--}}
    <script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
@endpush
