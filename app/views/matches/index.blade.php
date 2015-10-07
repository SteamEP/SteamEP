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
		@if($type['enabled'])
        <li data-type="{{$id}}" {{Session::get('matchType') == $id ? 'class="active"' : ''}}>
            <a href="{{URL::to('matches/' . $id)}}">{{$type['name']}}</a>
		</li>
		@endif
        @endforeach
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
@else
<div class="row">
    @foreach ($data->matches as $match)
	<?php $userData = explode('\\', Cache::section('steamUserMap')->get($match->user->steamid)); ?>
    <div class='well' id="{{$match->user->steamid}}">
        <span class="steam-name" data-steamid="{{$match->user->steamid}}">{{ Cache::section('steamUserMap')->has($match->user->steamid) ?  $userData[0] : "...loading"}}</span>
        <span class="pull-right">
            @if($match->sameGameOnly == 1)
            <span style="font-weight:900">Same game only!</span>
            @endif
            <a href='http://steamcommunity.com/profiles/{{$match->user->steamid}}' target='_blank'>Steam profile</a> -  
            <a href='http://steamep.com/{{$match->user->steamid}}' target='_blank'>SteamEP profile</a> - 
		@if($match->user->settings->tradeoffer_url == "" || $match->user->settings->hide_friend_button != 1)	
            <a class="add-friend" onclick="javascript: addFriend('{{$match->user->steamid}}');" style="cursor: pointer;">Add friend</a> - 
		@endif
	    @if($match->user->settings->tradeoffer_url != "")
	    	<a href='{{$match->user->settings->tradeoffer_url}}' rel='noreferrer' target='_blank'>Offer on Steam</a> -
		@endif
            <a class="add-ignore" data-user="{{$match->user->id}}" data-steamid="{{$match->user->steamid}}" style="cursor: pointer;">Ignore</a>
        </span>
        <br /><b>Status:</b> <span id="{{$match->user->steamid}}-status" class='status'>{{ Cache::section('steamUserMap')->has($match->user->steamid) ?  $userData[1] : "...loading"}}</span><br />
        <p><b>Last updated</b> {{ Shared::timespan($match->user->last_list_update) }} ago</p>
		<?php $needs = array(); $has = array(); ?>
        <p><b>Needs:</b> @foreach($match->need as $need) <?php $needs[] = $need->name; ?> @endforeach {{implode('; ', $needs)}}</p>
        <p><b>Has:</b> @foreach($match->have as $have) <?php $has[] = $have->name; ?> @endforeach {{implode('; ', $has)}}</p>
    </div>
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
