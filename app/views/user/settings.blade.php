@extends('main.main')
@section('content')
<div class="row">
    <ul class="nav nav-tabs">
        <li {{Session::get('settingsType') == 1 ? 'class="active"' : ''}}>
            <a href="{{URL::to('user/settings/1')}}">General Settings</a>
        </li>
        <li {{Session::get('settingsType') == 2 ? 'class="active"' : ''}}>
            <a href="{{URL::to('user/settings/2')}}">Ignore List</a>
        </li>
    </ul>
</div>
<div class="row">
    <br />
    @if(isset($settings['error']))
	<div class="alert alert-danger col-lg-12">{{$settings['error']}}</div>
    @endif
    <form method="post">
        @if (Session::get('settingsType') == 1)
			<div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="dota2_player_cards_ti3_hide" id="dota2_player_cards_ti3_hide" @if ($settings['dota2_player_cards_ti3_hide']==1) checked='checked' @endif />
                </span>
                <span class="form-control"><label for="dota2_player_cards_ti3_hide">Hide me from Dota 2 Player Card matches.</label></span>
			</div>
            <div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="steam_trading_cards_hide" id="steam_trading_cards_hide" @if ($settings['steam_trading_cards_hide']==1) checked='checked' @endif />
                </span>
                <span class="form-control"><label for="steam_trading_cards_hide">Hide me from Steam Trading Card matches.</label></span>
            </div>  
            <div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="steam_trading_cards_foil_hide" id="steam_trading_cards_foil_hide" @if ($settings['steam_trading_cards_foil_hide']==1) checked='checked' @endif />
                </span>
                <span class="form-control"><label for="steam_trading_cards_foil_hide">Hide me from Steam Foil Trading Card matches.</label></span>
            </div>  
            <div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="steam_emoticons_hide" id="steam_emoticons_hide" @if ($settings['steam_emoticons_hide']==1) checked='checked' @endif /> 
                </span>
                <span class="form-control"><label for="steam_emoticons_hide">Hide me from Steam Emoticon matches.</label></span>
            </div>
            <div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="steam_backgrounds_hide" id="steam_backgrounds_hide" @if ($settings['steam_backgrounds_hide']==1) checked='checked' @endif />
                </span>
                <span class="form-control"><label for="steam_backgrounds_hide">Hide me from Steam Background matches.</label></span>
	        </div>
        <?php /*<div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="dota2_diretide_hide" id="dota2_diretide_hide" @if ($settings['dota2_diretide_hide']==1) checked='checked' @endif />
                </span>
                <span class="form-control"><label for="dota2_diretide_hide">Hide me from Dota 2 Diretide matches.</label></span>
			</div>*/ ?> 
            <div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="same_game_only" id="same_game_only" @if ($settings['same_game_only']==1) checked='checked' @endif /> 
                </span>
                <span class="form-control"><label for="same_game_only">I only want to trade items from the same game.</label></span>
            </div>
			<div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="hide_profile" id="hide_profile" @if ($settings['hide_profile']==1) checked='checked' @endif /> 
                </span>
                <span class="form-control"><label for="hide_profile">Hide my <a href="{{URL::to(Auth::user()->steamid)}}">profile</a>.</label></span>
            </div>
            <div class="input-group settings col-lg-6">
                <span class="input-group-addon">
                    <input type="checkbox" name="only_trade_offers" id="only_trade_offers" @if ($settings['only_trade_offers']==1) checked='checked' @endif />
                </span>
                <span class="form-control"><label for="only_trade_offers">Only show matches with a Steam Trade Offer URL.</label></span>
            </div>
	    <div class="input-group settings col-lg-12">
	       <span class="input-group-addon" id="tradeoffer_url_label">Steam Trade Offer URL<span class="pull-right"><a href="http://steamcommunity.com/my/tradeoffers/privacy" target="_blank">?</a></span></span>
			<div>
				<div class="form-control" style="padding:0;border:0;">
					<input type="text" placeholder="Steam Trade Offer URL" class="form-control" name="tradeoffer_url" id="tradeoffer_url" @if($settings['tradeoffer_url'] != "") value="{{$settings['tradeoffer_url']}}" @endif/>
				</div>
				<span class="input-group-addon" style=border-left:0;">
					Disable Add Friend button
				</span>
				<span class="input-group-addon">
                    <input type="checkbox" name="hide_friend_button" id="hide_friend_button" @if ($settings['hide_friend_button']==1) checked='checked' @endif /> 
				</span>
			</div>
		</div>   			
            <div class="col-lg-6 pull-right">
                <input class="pull-right btn" type="submit" value="Save" />
            </div>
        @elseif (Session::get('settingsType') == 2)
            <?php $ignoreList = json_decode($settings['ignore_list'], true); ?>
            @if (count($ignoreList)<1)
                Your ignore list is empty.
            @else
                <script type="text/javascript">
                    function removeIgnore(id) {
                        $.ajax({url: '{{ Url::to('user/removeignore') }}/'+id}).done(function(){
                            $('#'+id).fadeTo(1000, 0.10);
                        });
                    }
                </script>
                <table class="table" style="margin-bottom: 0px !important;">
                    <tr>
                        <th width="80%">Name</th>
                        <th>Remove</th>
                    </tr>
                </table>
                <div style="overflow: auto; height: 250px">
                    <table class="table table-striped">
                        @foreach($ignoreList as $i)
                            <? $i = User::find($i); ?>
                            <tr id='{{ $i->id }}'>
                                <td width="80%"><img width="30" height="30" src="{{ URL::to('res/img/default_avatar.jpg') }}" id="{{ $i->steamid }}-avatar" /> 
                                <a href='http://steamcommunity.com/profiles/{{ $i->steamid }}' id='{{ $i->steamid }}' target='_blank'>..loading</a>
                                <script type="text/javascript">
                                $.ajax({url: "{{URL::to('user/name')}}/{{ $i->steamid }}"}).done(function(data) {
                                    $("#{{ $i->steamid }}").html(data.split('\\')[0]);
                                    $("#{{ $i->steamid }}-avatar").attr('src', data.split('\\')[2]);
                                });
                                </script></td>
                                <td><button type="button" class="btn btn-primary btn-small" onclick="javascript: removeIgnore('{{ $i->id }}');">Remove</button></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif
        @endif
    </form>
</div>
@stop
