<div class="card {{ Request::is('users*') || Request::is('settings/permissions*') || Request::is('settings/roles*') ? '' : 'collapsed-card' }}">
    <div class="card-header">
        <h3 class="card-title">{{trans('lang.permission_menu')}}</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fa {{ Request::is('settings/users*') || Request::is('settings/permissions*') || Request::is('settings/roles*') ? 'fa-minus' : 'fa-plus' }}"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="{!! route('permissions.index') !!}" class="nav-link {{  Request::is('settings/permissions*') ? 'selected' : '' }}">
                    <i class="fas fa-inbox"></i> {{trans('lang.permission_plural')}}
                </a>
            </li>
            <li class="nav-item">
                <a href="{!! route('roles.index') !!}" class="nav-link {{  Request::is('settings/roles*') ? 'selected' : '' }}">
                    <i class="fas fa-inbox"></i> {{trans('lang.role_plural')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! route('users.index') !!}" class="nav-link {{  Request::is('users*') ? 'selected' : '' }}">
                    <i class="fas fa-users"></i> {{trans('lang.user_plural')}}
                </a>
            </li>

        </ul>
    </div>
</div>

<div class="card {{
             Request::is('settings/app/*') ||
             Request::is('settings/mail*') ||
             Request::is('settings/translation*') ||
             Request::is('settings/payment*') ||
             Request::is('settings/currencies*') ||
             Request::is('settings/taxes*') ||
             Request::is('settings/customFields*')
 ? '' : 'collapsed-card' }}">
    <div class="card-header">
        <h3 class="card-title">{{trans('lang.app_setting_globals')}}</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa {{
             Request::is('settings/app/*') ||
             Request::is('settings/mail*') ||
             Request::is('settings/translation*') ||
             Request::is('settings/payment*') ||
             Request::is('settings/currencies*') ||
             Request::is('settings/taxes*') ||
             Request::is('settings/customFields*')
             ? 'fa-minus' : 'fa-plus' }}"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="{!! url('settings/app/globals') !!}" class="nav-link {{  Request::is('settings/app/globals*') ? 'selected' : '' }}">
                    <i class="fas fa-cog"></i> {{trans('lang.app_setting_globals')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/app/localisation') !!}" class="nav-link {{  Request::is('settings/app/localisation*') ? 'selected' : '' }}">
                    <i class="fas fa-language"></i> {{trans('lang.app_setting_localisation')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/app/social') !!}" class="nav-link {{  Request::is('settings/app/social*') ? 'selected' : '' }}">
                    <i class="fas fa-globe"></i> {{trans('lang.app_setting_social')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/payment/payment') !!}" class="nav-link {{  Request::is('settings/payment*') ? 'selected' : '' }}">
                    <i class="fas fa-credit-card"></i> {{trans('lang.app_setting_payment')}}
                </a>
            </li>

            @can('currencies.index')
                <li class="nav-item">
                    <a href="{!! route('currencies.index') !!}" class="nav-link {{ Request::is('settings/currencies*') ? 'selected' : '' }}"><i class="nav-icon fas fa-dollar-sign ml-1"></i>{{trans('lang.currency_plural')}}
                    </a>
                </li>
            @endcan
            @can('taxes.index')
                <li class="nav-item">
                    <a href="{!! route('taxes.index') !!}" class="nav-link {{ Request::is('settings/taxes*') ? 'selected' : '' }}"><i class="nav-icon fas fa-coins ml-1"></i>{{trans('lang.tax_plural')}}
                    </a>
                </li>
            @endcan

            <li class="nav-item">
                <a href="{!! url('settings/app/notifications') !!}" class="nav-link {{  Request::is('settings/app/notifications*') || Request::is('notificationTypes*') ? 'selected' : '' }}">
                    <i class="fas fa-bell"></i> {{trans('lang.app_setting_notifications')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/mail/smtp') !!}" class="nav-link {{ Request::is('settings/mail*') ? 'selected' : '' }}">
                    <i class="fas fa-envelope"></i> {{trans('lang.app_setting_mail')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/translation/en') !!}" class="nav-link {{ Request::is('settings/translation*') ? 'selected' : '' }}">
                    <i class="fas fa-language"></i> {{trans('lang.app_setting_translation')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! route('customFields.index') !!}" class="nav-link {{ Request::is('settings/customFields*') ? 'selected' : '' }}">
                    <i class="fas fa-list"></i> {{trans('lang.custom_field_plural')}}
                </a>
            </li>

        </ul>
    </div>
</div>

<div class="card {{ Request::is('settings/mobile*') || Request::is('customPages*') ? '' : 'collapsed-card' }}">
    <div class="card-header">
        <h3 class="card-title">{{trans('lang.mobile_menu')}}</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fa {{ Request::is('settings/mobile*') ? 'fa-minus' : 'fa-plus' }}"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="{!! url('settings/mobile/globals') !!}" class="nav-link {{  Request::is('settings/mobile/globals*') ? 'selected' : '' }}">
                    <i class="fas fa-inbox"></i> {{trans('lang.mobile_globals')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/mobile/colors') !!}" class="nav-link {{  Request::is('settings/mobile/colors*') ? 'selected' : '' }}">
                    <i class="fas fa-inbox"></i> {{trans('lang.mobile_colors')}}
                </a>
            </li>

            @can('customPages.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('customPages*') ? 'selected' : '' }}" href="{!! route('customPages.index') !!}">
                        <i class="fas fa-file"></i>
                        {{trans('lang.custom_page_plural')}}
                    </a>
                </li>
            @endcan

            @can('slides.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('slides*') ? 'selected' : '' }}" href="{!! route('slides.index') !!}"> <i class="fas fa-images"></i>
                        {{trans('lang.slide_plural')}}
                    </a>
                </li>
            @endcan

        </ul>
    </div>
</div>
