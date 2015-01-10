@extends('main.main')
@section('content')
<div class="row">
    {{ HTML::script('res/js/matches.js') }}
    <div class="input-group match-settings col-lg-3">
        <span class="input-group-addon">
            <input type='checkbox' onclick="javascript: hideOffline();" />
        </span>
        <span class="form-control">
            <label>Hide offline users</label>
        </span>
    </div>
    <div class="pull-right">
        {{ $paginator->links() }}
    </div>
</div>
<div class="row">
    <ul class="nav nav-tabs">
		@foreach(Item::typeTable() as $id => $type)
		@if($id != 6)
        <li data-type="{{$id}}" {{Session::get('matchType') == $id ? 'class="active"' : ''}}>
            <a href="{{URL::to('matches/' . $id)}}">{{$type['name']}}</a>
		</li>
		@endif
        @endforeach
        <li class="pull-right {{Session::get('matchType') == 'recently' ? 'active' : ''}}">
            <a href="{{URL::to('matches/recently')}}">Recently Added</a>
        </li>
    </ul>
</div>
@if($data->totalMatches < 1)
<div class="row">
    <div class="not-found">
        <i class='icon-heart'></i>
        <span>We're sorry, but we can't find any matches for you.</span>
        <span>Maybe you could try again at a later time.</span>
    </div>
</div>
<div class="row">
    <div class="pull-right">{{$data->totalMatches}} matches have been found.</div>
</div>
@elseif(isset($history))
<div class="row">
    @foreach (json_decode(json_encode($data->matches), FALSE) as $match)
    <?php $user_b = User::find($match->userb_id); ?>
    <p class='well' id="{{$user_b->steamid}}">
		<?php $userData = explode('\\', Cache::section('steamUserMap')->get($user_b->steamid)); ?>
        <span class="steam-name" data-steamid="{{$user_b->steamid}}">{{ Cache::section('steamUserMap')->has($user_b->steamid) ?  $userData[0] : "...loading"}}</span>
        <span class="pull-right">
            @if($user_b->settings['same_game_only'] == 1)
            <span style="font-weight:900">Same game only!</span>
            @endif
            <a href='http://steamcommunity.com/profiles/{{$user_b->steamid}}' target='_blank'>Steam profile</a> -  
			<a href='https://steamep.com/{{$user_b->steamid}}' target='_blank'>SteamEP profile</a> -  
			@if($user_b->settings['tradeoffer_url'] == "" || $user_b->settings['hide_friend_button'] != 1)	
			<a href='steam://friends/add/{{$user_b->steamid}}'>Add friend</a> - 
			@endif
			@if(isset($user_b->settings['tradeoffer_url']) && $user_b->settings['tradeoffer_url'] != "")
            <a href='{{$user_b->settings->tradeoffer_url}}' target='_blank' rel='noreferrer'>Offer on Steam</a> -
            @endif
            <a data-user="{{$match->userb_id}}" data-steamid="{{$user_b->steamid}}" style="cursor: pointer;">Ignore</a>
        </span>
        <br />
        Status: <span class='status'>{{ Cache::section('steamUserMap')->has($user_b->steamid) ?  $userData[1] : "...loading"}}</span><br />
        Added {{ Shared::timespan(strtotime($match->updated_at)) }} ago<br />
		<?php $needs = array(); $has = array(); ?>
        Needs: @foreach(json_decode($match->jsonMatchSnapshot)->need as $need) <?php $needs[] = $need->name; ?> @endforeach {{implode('; ', $needs)}}<br />
        Has: @foreach(json_decode($match->jsonMatchSnapshot)->have as $have) <?php $has[] = $have->name; ?> @endforeach {{implode('; ', $has)}}
    </p>
    @endforeach
    <script type="text/javascript">
        $('.steam-name').each(function(i, e){
            if($(this).text() == "...loading"){
                var this2 = this;
                $.ajax({url: '{{URL::to("user/name")}}/' + $(this).data('steamid')}).done(function(result){
                    $(this2).html(result.split('\\')[0]);
                    $(this2).siblings('.status').html(result.split('\\')[1]);
                });
            }
       });
       $('.add-ignore').click(function(){
            addIgnore('{{URL::to("user/ignore")}}' + '/' + $(this).data('user'), $(this).data('steamid')+"");
       })
    </script>
</div> 
@else
<div class="row">
    @foreach ($data->matches as $match)
	<?php $userData = explode('\\', Cache::section('steamUserMap')->get($match->user->steamid)); ?>
    <p class='well' id="{{$match->user->steamid}}">
        <span class="steam-name" data-steamid="{{$match->user->steamid}}">{{ Cache::section('steamUserMap')->has($match->user->steamid) ?  $userData[0] : "...loading"}}</span>
        <span class="pull-right">
            @if($match->sameGameOnly == 1)
            <span style="font-weight:900">Same game only!</span>
            @endif
            <a href='http://steamcommunity.com/profiles/{{$match->user->steamid}}' target='_blank'>Steam profile</a> -  
            <a href='http://steamep.com/{{$match->user->steamid}}' target='_blank'>SteamEP profile</a> - 
		@if($match->user->settings->tradeoffer_url == "" || $match->user->settings->hide_friend_button != 1)	
            <a class="add-friend" onclick="javascript: addFriend('{{URL::to('user/add/'.$match->user->id.'/'.base64_encode(json_encode(array('need'=>$match->need, 'have'=>$match->have))))}}', '{{$match->user->steamid}}');" style="cursor: pointer;">Add friend</a> - 
		@endif
	    @if($match->user->settings->tradeoffer_url != "")
	    	<a href='{{$match->user->settings->tradeoffer_url}}' rel='noreferrer' target='_blank'>Offer on Steam</a> -
		@endif
            <a class="add-ignore" data-user="{{$match->user->id}}" data-steamid="{{$match->user->steamid}}" style="cursor: pointer;">Ignore</a>
        </span>
        <br />Status: <span id="{{$match->user->steamid}}-status" class='status'>{{ Cache::section('steamUserMap')->has($match->user->steamid) ?  $userData[1] : "...loading"}}</span><br />
        Last updated {{ Shared::timespan($match->user->last_list_update) }} ago<br />
		<?php $needs = array(); $has = array(); ?>
        Needs: @foreach($match->need as $need) <?php $needs[] = $need->name; ?> @endforeach {{implode('; ', $needs)}}<br />
        Has: @foreach($match->have as $have) <?php $has[] = $have->name; ?> @endforeach {{implode('; ', $has)}}
    </p>
    @endforeach
    <script type="text/javascript">
        $('.steam-name').each(function(i, e){
            if($(this).text() == "...loading"){
                var this2 = this;
                $.ajax({url: '{{URL::to("user/name")}}/' + $(this).data('steamid')}).done(function(result){
                    $(this2).html(result.split('\\')[0]);
                    $(this2).siblings('.status').html(result.split('\\')[1]);
                });
            }
       });
       $('.add-ignore').click(function(){
            addIgnore('{{URL::to("user/ignore")}}' + '/' + $(this).data('user'), $(this).data('steamid')+"");
       })
    </script>
</div>
@endif
<div class="pull-right">
	{{ $paginator->links() }}
</div>
@stop
