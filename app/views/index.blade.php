@extends('main.main')
@section('content')
<div class="row">
	<h1>@lang('lang.steam_exchange_point')</h1>
	<p class="lead">@lang('lang.site_description')</p>
</div>
<div class="row">
	<p class="centered">
	<img class="img-responsive" src="{{URL::to('res/img/cardtutorial.png')}}" alt="How to use SteamEP"/>
	</p>
	<p class="centered"> <br />
	<a href='https://steamcommunity.com/groups/steam-exchange-point' target='_blank'>Come visit us on our steam group!</a> Feel free to post suggestions or bugs you may encounter there.<br />
	</p>
	<div class="donate"><a href="{{URL::to('donate')}}">Enjoy SteamEP? Consider donating to help keep our servers alive!</a></div>
	<p class="centered">
		<a class="twitter-timeline" href="https://twitter.com/SteamEP" data-widget-id="414849726165417984">Tweets by @SteamEP</a>
		<script>
			!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
		</script>
		<script>
			$(".twitter-timeline").waitUntilExists(function(){
				var $head = $(".twitter-timeline").contents().find("head");
				$head.append($("<link/>", { rel: "stylesheet", href:"https://steamep.com/res/css/twitter.css", type: "text/css"}));
			});
		</script>
	</p>
</div>
@stop