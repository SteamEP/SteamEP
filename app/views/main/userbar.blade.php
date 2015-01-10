<div class="user-bar row">
    <div class="user-buttons">
        @if (Auth::check())
        <div class="image-holder">
            @if (Cache::section('steamUserMap')->has(Auth::user()->steamid))
            <img width="30" height="30" src="{{ last(explode('\\', Cache::section('steamUserMap')->get(Auth::user()->steamid))) }}" id="user-avatar" alt="Avatar"/>
            @else     
            <img width="30" height="30" src="{{ URL::to('res/img/default_avatar.jpg') }}" id="user-avatar" alt="Avatar"/>
            @endif
        </div>
        <div class="username">
	    <a href='{{URL::to(Auth::user()->steamid)}}'>
            @if (Cache::section('steamUserMap')->has(Auth::user()->steamid))
            <span>{{ current(explode('\\', Cache::section('steamUserMap')->get(Auth::user()->steamid))) }}</span>
            @else
            <span id="user-name">..loading</span>
            <script type="text/javascript">
                $.ajax({url: "{{URL::to('user/name')}}/{{ Auth::user()->steamid }}"}).done(function(data) {
                    $("#user-name").html(data.split('\\')[0]);
                    $("#user-avatar").attr('src', data.split('\\')[2]);
                });
            </script>
            @endif
	    </a>
            <span class="sign-out"><a href="{{ URL::to('user/logout') }}">(Sign out)</a></span>
        </div>
        @else
        <div class="steam-small-login">
            <a href="{{ URL::to('user/login') }}">
                <img src="{{URL::to('res/img/sits_small.png')}}" alt="Login with Steam" />
            </a>
        </div>
        @endif
    </div>
</div>
