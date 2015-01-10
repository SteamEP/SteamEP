@extends('main.main')
@section('content')
@if(!$profile)
	{{ HTML::script('res/js/list.js') }}
    <div class="row" style="margin-bottom: 5px;:">
        <form class="col-lg-5" action='{{URL::to('list') . "/2"}}' id="selectForm" method="get">
            <select id="listSelect" name="appid" data-size="6">
                <option value="0">Filter by game...</option>
                @foreach($data->allGames as $game)
                <option value='{{$game->appid}}' {{ (Input::get('appid')==$game->appid)?'selected="selected"':''}}>{{$game->name}}</option>
                @endforeach
            </select>
        </form>
		{{ HTML::script('res/js/select2.min.js') }}
		<script type="text/javascript">
			$("#listSelect").select2();
			$("#listSelect").click(function(){$("#selectForm").submit();});
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

    <div class="row">
        <ul class="nav nav-tabs">
            @foreach(Item::typeTable() as $id => $type)
				@if($id != 6)
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
    @if(!$data->currentList)
        <div class="row">
            <div class='not-found'>
                <i class="icon-heart"></i>
                <span> We were not able to find any items for you in this category :( </span>
				@if(Session::get('listType') == "inventory")
					<br />
					<span><a href="https://support.steampowered.com/kb_article.php?ref=4113-YUDH-6401">You might need to set your steam profile to public</a></span>
				@endif
			</div>
        </div>
	@endif
    @else
	<a href="http://steamcommunity.com/profiles/{{$profile->steamid}}" target="_blank"><h1 id="h1-username"></h1></a>
        <script type="text/javascript">
            $.ajax({url: '{{URL::to("user/name")}}/{{$profile->steamid}}'}).done(function(result){
                $('#h1-username').html(result.split('\\')[0]);
            });
		</script>
	@endif
	@if(($profile || Session::get('listType') == "selected" || Session::get('listType') == "inventory") && $data->currentList)
		<div class="row"><div class="pull-right">
           <span>Show: </span>
		   <div class="btn-group" data-toggle="buttons">
				@if(Session::get('listType')=="inventory")
					<button type="button" class="btn filterInventory" data-default="remove" data-filter="hasDuplicate">Only Duplicates</button>
				@endif
               @foreach(Item::typeTable() as $id => $type)
                  @if($id != 1 && $id != 6)
                	  <button type="button" class="btn active filterInventory" data-filter="{{$type['css']}}">{{$type['name']}}</button>
             	  @endif
        	   @endforeach
     	   </div>
	    </div></div>
    @endif
	<div class="filterGrid">
	@if($data->currentList)
    <?php $have = array(); $want = array(); ?>
    @foreach($data->currentList as $listRow)
    <?php
        if ($listRow->items[0]->type==1 && count($listRow->items)>5) {
            $cssClass = '';
        } else {
            $cssClass = Item::typeCSS($listRow->items[0]->type);
        }
    ?>
    <div class="row list-row {{$cssClass}}" data-game='{{$listRow->items[0]->appid}}' data-type='{{$listRow->items[0]->type}}'>
        <div class="game-banner">
            @if(isset($listRow->custombanner))
            <img src="{{URL::asset('res/img/cat_banners/' . $listRow->custombanner . $listRow->items[0]->appid) }}.png" />
            @elseif($listRow->items[0]->appid == 245070)
            <img src="{{URL::asset('res/img/cat_banners/245070.png')}}" />
	        @elseif($listRow->items[0]->appid == 18)
            <img src="{{URL::asset('res/img/cat_banners/diretide/18.png')}}" />
            @elseif($listRow->items[0]->appid == 267420  || $listRow->items[0]->appid == 335590)
            <img src="{{URL::asset('res/img/cat_banners/267420.png')}}" />
	        @elseif($listRow->items[0]->appid == 753)
            <img src="{{URL::asset('res/img/cat_banners/753.png')}}" />
            @elseif($listRow->items[0]->appid == 303700)
            <img src="{{URL::asset('res/img/cat_banners/303700.png')}}" />
            @else
            <img width="292" height="136" src="http://cdn.akamai.steamstatic.com/steam/apps/{{$listRow->items[0]->appid}}/header_292x136.jpg"  alt="{{$listRow->name}}"/>
            @endif
        </div>
        <div class="game-items">
            @foreach($listRow->items as $item)
<?php
	$tempName = $item->name;
	if($item->type == 3){
		$tempName = $tempName . " (Foil)";
	}elseif($item->type == 5){
		$tempName = $tempName . " (Background)";
	}
	($item->need ? $want[$listRow->name][] = $tempName : '');
	($item->have ? $have[$listRow->name][] = $tempName : ''); 
?>
            <div data-item='{{$item->id}}' class="item @if(isset($item->count) && $item->count > 1) hasDuplicate @endif @if($item->need) need @elseif($item->have) have @endif @if(isset($item->in_inventory))inventory @endif">
				@if($item->appid <= 19)
                    <img src="{{URL::asset('res/img/pc2014/' . $item->appid . '.jpg')}}" alt='{{htmlentities($item->name, ENT_QUOTES)}}'/>
                @elseif(strpos($item->image_base64, "http") === 0)
                    <img src="{{$item->image_base64}}" alt='{{htmlentities($item->name, ENT_QUOTES)}}'/>
                @else
                    <img src="http://cdn.steamcommunity.com/economy/image/{{$item->image_base64}}/96x96" alt='{{htmlentities($item->name, ENT_QUOTES)}}'/>
                @endif
                <span>{{$item->name}} @if(isset($item->count) && $item->count > 1) ({{$item->count}}) @endif</span>
            </div>
            @endforeach
        </div>
	</div>
	@endforeach
	</div>
    <script type='text/javascript'>
        var pvar = <?php echo json_encode($pvar) ?>;
    </script>
	@if($profile && Auth::check() && Auth::user()->steamid==$profile->steamid)
	{{ HTML::script('res/js/list.js') }}
<p class="soutput"><a href='#soutput' onclick="javascript: $('#steamOutput').show(); $('#redditOutput').hide();">Steam output</a> | <a href='#soutput' onclick="javascript: $('#steamOutput').hide(); $('#redditOutput').show();">Reddit output</a></p>
<textarea width="100%" height="100%" id="steamOutput">[H]
@foreach($have as $k=>$cards)
[h1]{{$k}}[/h1][list]
@foreach($cards as $card)
[*]{{$card}}
@endforeach
[/list]
@endforeach
[W]
@foreach($want as $k=>$cards)
[h1]{{$k}}[/h1][list]
@foreach($cards as $card)
[*]{{$card}}
@endforeach
[/list]
@endforeach
</textarea>
<textarea style="display:none;" width="100%" height="100%" id="redditOutput">
|Game|Cards I have|Cards I want
|:-|:-|:-|
@foreach($have as $k=>$cards)
|{{$k}}|{{implode(", ", $cards)}}|<?php if(isset($want[$k])){ echo implode(", ", $want[$k]); unset($want[$k]); } ?>|
@endforeach
@foreach($want as $k=>$cards)
|{{$k}}|<?php if(isset($have[$k])){ echo implode(", ", $have[$k]); } ?>|{{implode(", ", $cards)}}|
@endforeach
</textarea>
    @endif
    @endif
@if($count!=85 && !$profile)
	<div class="pull-right" style="margin-top: 10px">
		{{ $paginator->links() }}
	</div>
@endif
@stop
