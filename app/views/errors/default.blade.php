@extends('main.main')
@section('content')
<h1>We're sorry, but..</h1>
<div class="row"  style="text-align:center;">
	@if($code == "404")
	<span>It seems like this is not the page you are looking for</span>
	@else
	<span>An error occured :( Please contact us!</span>
	@endif
	<h1 style="font-size:184;"><i class="icon icon-frown"></i></h1>

	<?php $rand = rand(0,10) ?>
	@if($code != "404")
	<span>You can contact us via Steam or Reddit</span>
	@elseif($rand >= 10)
	<span>(There are over a billion websites on the internet, but you just <i>had</i> to pick this one...)</span>
	@elseif($rand >= 9)
	<span>(Do you see the big frowny guy? It means we're sorry.)</span>
	@elseif($rand >= 8)
	<span>(So go somewhere else)</span>
	@elseif($rand >= 7)
	<span>(Seems like there's as much content on this page as there are cards on your steam account)</span>
	@elseif($rand >= 6)
	<span>(Press alt+f4 to solve this problem)</span>
	@elseif($rand >= 5)
	<span>(Our webpage is in another castle)</span>
	@elseif($rand >= 4)
	<span>(It's all your fault)</span>
	@elseif($rand >= 3)
	<span>(Well, you found it. The only page on the site that doesn't work.)</span>
	@elseif($rand >= 2)
	<span>(It's not our fault. Your internet is just broken.)</span>
	@elseif($rand >= 0)
	<span>(Allan please add message)</span>
	@endif
</div>
@stop

