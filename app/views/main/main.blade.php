<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
		@if(isset($title) && $title != false)
		<title>@lang('lang.steam_exchange_point') - {{$title}}</title>
		@else
        <title>@lang('lang.steam_exchange_point') - Exchange Steam Cards, Emoticons and Backgrounds</title>
		@endif
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Trade your Steam Trading Cards, Emoticons and Backgrounds one on one with other people! Complete all your badges and raise your Steam level through the roof. Create custom profiles with all your cards to link on other websites and trade even faster!" />
        <link rel="shortcut icon" type="image/x-icon" href="{{URL::to('/')}}/favicon.ico" />
		<link href="//fonts.googleapis.com/css?family=Tangerine:400,700|Bad+Script:400" rel="stylesheet" type="text/css">
        {{ HTML::style('res/css/bootstrap.min.css') }}
        <link href="https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
		{{ HTML::style('res/css/style.css') }}
		{{ HTML::style('res/css/select2.css') }}
        <!--[if lt IE 9]>
          <script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <script src="https://code.jquery.com/jquery.min.js"></script>
        {{ HTML::script('res/js/bootstrap.min.js') }}

		{{ HTML::script('res/js/common.js') }}		
    	<script type="text/javascript">
        	(function(i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function() {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
            ga('create', 'UA-42609491-1', 'steamep.com');
            ga('send', 'pageview');
       </script>
    </head>
    <body id="top">
        <div class="container">
            @include('main.userbar')

            <div class="row site-title">
                <img class="img-responsive" src="{{URL::asset('res/img/site_title.png')}}" alt="@lang('lang.steam_exchange_point')" />
			</div>
			
            @include('main.sidebar')

            <div class="content col-lg-10">

                @yield('content')

			</div>
			<div class="back-to-top"><a href="#top">Back to top</a></div>
        </div>

        <div class="row text-center" id="footer">
			<a href="https://twitter.com/steamep" target="_blank"><img src="{{URL::asset('res/img/twitter.png')}}" alt="Follow us on Twitter" /></a>
			<a href="http://steamcommunity.com/groups/steam-exchange-point/discussions" target="_blank"><img src="{{URL::asset('res/img/steamcommunity.png')}}" alt="Join the Steam Community Group" /></a>
			<br /><br />
			<span>Copyright &copy; swordbeta.com. All rights reserved.</span>
            <br />
            <span>Powered by Steam, </span>
			<span>{{DB::table('users')->remember(5)->count()}} total users.</span>
			<br />
			<span><a href='{{URL::to('disclaimer')}}'>Disclaimer</a> - <a href='{{URL::to('policy')}}'>Privacy Policy</a></span>
        </div>
    </body>
</html>
