@extends('main.main')
@section('content')
@if(!$profile)
	{{ HTML::script('res/js/list.js') }}
    <div class="row">
        <form class="col-lg-5" action='{{URL::to('list') . "/2"}}' id="selectForm" method="get">
            <select class="selectpicker" name="appid" data-size="6">
                <option value="0">Filter by game...</option>
                @foreach($data->allGames as $game)
                <option value='{{$game->appid}}' {{ (Input::get('appid')==$game->appid)?'selected="selected"':''}}>{{$game->name}}</option>
                @endforeach
            </select>
        </form>
        {{ HTML::script('res/js/bootstrap-select.min.js') }}
        <form class="search col-lg-5 pull-right">
            <div class="input-group">
                <input class="form-control" placeholder="Search game or item..." type="text" name="s" />
                <span class="input-group-btn">
                    <input type="submit" value=">" class="btn btn-default"/>
                </span>
            </div>
        </form>
    </div>

    <div class="row">
        <ul class="nav nav-tabs">
            @foreach(Item::typeTable() as $id => $type)
				@if($id != 1 && $id != 6)
					<li data-type="{{$id}}" {{Session::get('listType') == $id ? 'class="active"' : ''}}>
                		<a href="{{URL::to('list/' . $id)}}">{{$type['name']}}</a>
					</li>
				@endif
			@endforeach
            <li class="pull-right  {{Session::get('listType') == 'selected' ? 'active' : ''}}">
                <a href="{{URL::to('list/selected')}}">Selected items</a>
            </li>
			<li class="pull-right {{Session::get('listType') == 'inventory' ? 'active' : ''}}">
				<a href="{{URL::to('list/inventory')}}">Inventory</a>
			</li>
        </ul>
        @if($count!=85 && !$profile)
            <div class="pull-right">
                {{ $paginator->links() }}
            </div>
		@endif
    </div>
	</div>
    <script type='text/javascript'>
        var pvar = <?php echo json_encode($pvar) ?>;
    </script>
@stop
