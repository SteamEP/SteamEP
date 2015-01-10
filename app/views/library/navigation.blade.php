@extends('main.main')
@section('content')
<div class="row">
	<form class="col-lg-5" action='{{URL::action('LibraryController@getList')}}' id="selectForm" method="get">
		<select class="selectpicker" name="appid" data-size="6">
			<option value="0">Filter by game...</option>
			 @foreach($games as $game)
				 <option value='{{$game['appid']}}' {{ (Input::get('appid')==$game['appid'])?'selected="selected"':''}} >
					{{$game['name']}}
				</option>
			 @endforeach
		 </select>
	 </form>
	{{ HTML::script('res/js/bootstrap-select.min.js') }}
	<script type='text/javascript'>		
    	if($('.selectpicker').length){
        	$('.selectpicker').selectpicker();
        	$('.selectpicker').change(function(){
            	$('#selectForm').submit();
       		});
    	};
	</script>
	<form class="search col-lg-5 pull-right">
		<div class="input-group">
			<input class="form-control" placeholder="Search game or item..." type="text" name="s" />
			<span class="input-group-btn">
				<input type="submit" value=">" class="btn btn-default"/>
			</span>
		</div>
	</form>
</div>
<hr />
<div class="row">
	<div class="pull-right">
		{{ $paginator->links() }}
	</div>
</div>

@foreach($games as $game)
	<div class="row list-row library">
		<div class="game-banner">
			<img src="http://cdn.steampowered.com/v/gfx/apps/{{$game['appid']}}/header_292x136.jpg" alt="{{$game['name']}}"/>
		</div>
		@if(array_key_exists($game['appid'], $itemInfo))
			<div class="item title">{{$game['name']}}<span class="pull-right"><span class="have">43 users have</span> | <span class="need">12 users need</span> something from this</span></div>
			<div class="game-info">
				<div class="item"><span class="tag">{{$itemInfo[$game['appid']]->normal_card_count}}</span><a class="name" href="#">trading cards</a></div>
				<div class="item"><span class="tag">{{$itemInfo[$game['appid']]->foil_card_count}}</span><a class="name" href="#">foil trading cards</a></div>
				<div class="item"><span class="tag">{{$itemInfo[$game['appid']]->emoticon_count}}</span><a class="name" href="#">emoticons</a></div>
				<div class="item"><span class="tag">{{$itemInfo[$game['appid']]->background_count}}</span><a class="name" href="#">>backgrounds</a></div>
			</div>
		@endif
	</div>
@endforeach

@stop
