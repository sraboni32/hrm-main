<div class="main-header">
    <div class="logo">
        <a href="/" class="logo-link">
            @if($setting && $setting->logo && file_exists(public_path('assets/images/'.$setting->logo)))
                <img src="{{asset('assets/images/'.$setting->logo)}}"
                     alt="{{$setting->CompanyName ?? 'Onchain'}}"
                     class="logo-img img-fluid"
                     onerror="this.src='{{asset('assets/images/logo-default.png')}}'">
            @else
                <img src="{{asset('assets/images/logo-default.png')}}"
                     alt="{{$setting->CompanyName ?? 'Onchain'}}"
                     class="logo-img img-fluid">
            @endif
        </a>
    </div>

    <div class="menu-toggle d-block d-lg-none" id="mobile-menu-toggle">
        <div></div>
        <div></div>
        <div></div>
    </div>

    <div class="margin_auto"></div>

    <div class="header-part-right">
        <!-- Full screen toggle -->
        <i class="i-Full-Screen header-icon d-none d-sm-inline-block" data-fullscreen></i>
        <!-- Grid menu Dropdown -->
        <div class="dropdown widget_dropdown">
            <i class="i-Globe text-muted header-icon" role="button" id="dropdownMenuButton" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false"></i>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <div class="menu-icon-grid">
                    <a href="{{route('language.switch','en')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/gb.svg')}}">English
                    </a>
                    <a href="{{route('language.switch','fr')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/fr.svg')}}">Frensh
                    </a>
                    <a href="{{route('language.switch','ar')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/sa.svg')}}">Arabic
                    </a>
                    <a href="{{route('language.switch','tr')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/tr.svg')}}">Turkish
                    </a>
                    <a href="{{route('language.switch','th')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/th.svg')}}">Thaï
                    </a>
                    <a href="{{route('language.switch','hn')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/in.svg')}}">Hindi
                    </a>
                    <a href="{{route('language.switch','es')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/es.svg')}}">Spanish
                    </a>
                    <a href="{{route('language.switch','it')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/it.svg')}}">Italien
                    </a>
                    <a href="{{route('language.switch','id')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/id.svg')}}">Indonesian
                    </a>
                    <a href="{{route('language.switch','vn')}}">
                        <img class="flag-icon" src="{{asset('assets/flags/vn.svg')}}">Vietnamese
                    </a>
                </div>
            </div>
        </div>

        <!-- Notification Dropdown -->
        <div class="dropdown" id="notificationDropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <i class="i-Bell header-icon"></i>
                <span class="badge badge-danger" id="notification-count">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown" id="notification-list" style="width: 350px; max-height: 400px; overflow-y: auto;">
                <div class="dropdown-header">Notifications</div>
                <div id="notification-items"></div>
                <div class="dropdown-divider"></div>
                <a href="{{ route('notifications.index') }}" class="dropdown-item text-center">View All</a>
            </div>
        </div>
        <!-- End Notification Dropdown -->

        <!-- User avatar dropdown -->
        <div class="dropdown">
            <div class="user col align-self-end">
                <img src="{{asset('assets/images/avatar/'.Auth::user()->avatar)}}" id="userDropdown" alt="" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <div class="dropdown-header">
                        <i class="i-Lock-User mr-1"></i> {{ Auth::user()->username }}
                    </div>
                    @if(auth()->user()->role_users_id == 1)
                    <a class="dropdown-item" href="{{route('profile.index')}}">{{ __('translate.Profile') }}</a>
                    @elseif(auth()->user()->role_users_id == 3)
                    <a class="dropdown-item" href="{{route('client_profile')}}">{{ __('translate.Profile') }}</a>
                    @else
                    <a class="dropdown-item" href="{{route('employee_profile')}}">{{ __('translate.Profile') }}</a>
                    @endif
                    @can('settings')
                        <a class="dropdown-item" href="{{route('system_settings.index')}}">{{ __('translate.System_Settings') }}</a>
                    @endcan

                    <a class="dropdown-item" href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- header top menu end -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function fetchNotifications() {
    $.get('{{ route('notifications.fetch') }}', function(data) {
        $('#notification-count').text(data.unread_count);
        let items = '';
        if (data.notifications.length === 0) {
            items = '<div class="dropdown-item text-center text-muted">No new notifications</div>';
        } else {
            data.notifications.forEach(function(n) {
                items += `
                    <a href="${n.data.url || '#'}" class="dropdown-item notification-link" data-id="${n.id}">
                        <div><strong>${n.data.title || 'Notification'}</strong></div>
                        <div>${n.data.message || ''}</div>
                        <small class="text-muted">${n.created_at}</small>
                    </a>
                    <div class="dropdown-divider"></div>
                `;
            });
        }
        $('#notification-items').html(items);
    });
}
setInterval(fetchNotifications, 15000);
fetchNotifications();
$(document).on('click', '.notification-link', function(e) {
    var id = $(this).data('id');
    $.post('/notifications/' + id + '/read', {_token: '{{ csrf_token() }}'});
});
</script>