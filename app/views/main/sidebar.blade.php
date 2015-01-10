<div class="sidebar col-lg-2">
    <ul>
        <li class="{{Request::is('/*') ? 'active' : ''}}">
            <a href="{{ URL::to('/') }}">
                <i class="icon-home"></i>
                <span>Home</span>
            </a>
        </li>
        <li class="{{Request::is('list*') ? 'active' : ''}} {{Auth::check() ? '' : 'no-login'}}">
            <a href="{{ URL::to('list') }}">
                <i class="icon-check"></i>
                <span>My list</span>
            </a>
        </li>
        <li class="{{Request::is('matches*') ? 'active' : ''}} {{Auth::check() ? '' : 'no-login'}}">
            <a href="{{ URL::to('matches') }}">
                <i class="icon-sort"></i>
                <span>Matches</span>
            </a>
        </li>
        <li class="{{Request::is('user/settings*') ? 'active' : ''}} {{Auth::check() ? '' : 'no-login'}}">
            <a href="{{ URL::to('user/settings') }}">
                <i class="icon-cog"></i>
                <span>Settings</span>
            </a>
        </li>
	</ul>
	@if(Session::get('offer_url_reminder'))
		<div class="text-center reminder">
			Hey! You should <br /><a href="{{ URL::action('UserController@getSettings') }}">enter your Trade Offer URL</a><br/> for easier trading!
		</div>
	@endif
    <div class="text-center">
        <script type="text/javascript"><!--
        google_ad_client = "ca-pub-9674757343248758";
        google_ad_slot = "7530907115";
        google_ad_width = 180;
        google_ad_height = 150;
        //-->
        </script>
        <script type="text/javascript"
        src="https://pagead2.googlesyndication.com/pagead/show_ads.js">
        </script>
    </div>
</div>
