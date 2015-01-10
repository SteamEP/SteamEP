<?php

/**
 * Website: https://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 2.0
 */
class UserController extends BaseController {

    public function __construct() {
    	parent::__construct();
        $this->beforeFilter('auth', array('except' => array('getLogin', 'getName')));
    }

    public function getName($steamid) {
        return Response::make(SteamAPI::getName($steamid, true));
    }

    public function getIgnore($ignore_id) {
        $settings = Settings::find(Auth::user()->id);
        $ignorelist = json_decode($settings->ignore_list, true);
        if ($ignorelist == null || !in_array($ignore_id, $ignorelist)) {
            $ignorelist[] = $ignore_id;
            $settings->ignore_list = json_encode($ignorelist);
            $settings->save();
        }
    }

    public function getRemoveignore($ignore_id) {
        $settings = Settings::find(Auth::user()->id);
        $ignorelist = json_decode($settings->ignore_list, true);
        if (($key = array_search($ignore_id, $ignorelist)) !== false) {
            unset($ignorelist[$key]);
            $settings->ignore_list = json_encode($ignorelist);
            $settings->save();
        }
    }

    public function getAdd($userb_id, $jsonMatchSnapshot) {
        $history = User::find(Auth::user()->id)->history()->where("userb_id", $userb_id)->first();
        if (!is_object($history)) {
            $history           = new History();
            $history->user_id  = Auth::user()->id;
            $history->userb_id = $userb_id;
        }
        $history->jsonMatchSnapshot = base64_decode($jsonMatchSnapshot);
        $history->save();
        $history = array_slice(User::find(Auth::user()->id)->history()->orderBy('id', 'DESC')->get()->toArray(), 20);
        if (count($history) > 0) {
            History::whereIn('id', array_column($history, 'id'))->delete();
        }
    }

    public function getLogin() {
        if (Config::get('app.debug')) {
            Auth::login(User::find(1), true);
        }

        $openId = new LightOpenID();
        if (!Auth::check()) {
            if (!$openId->mode) {
                $openId->identity = 'https://steamcommunity.com/openid';
                return Redirect::to($openId->authUrl());
            } elseif ($openId->validate()) {
                $steamid = explode("/", $openId->identity);
                $steamid = $steamid[count($steamid) - 1];

                $user = User::where('steamid', $steamid)->first();

                if (!$user) {
                    $user     = User::create(array('steamid' => $steamid));
                    $settings = Settings::create(array('user_id' => $user->id));
                    $user->settings()->save($settings);
                }
                
                if (!$user->settings()) {
                    $settings = Settings::create(array('user_id' => $user->id));
				}

				if ($user->settings()->first()->tradeoffer_url == "") {
					Session::put('offer_url_reminder', true);
				}
				Auth::login($user, true);
            }
        }
        return Redirect::to('/');
    }

    public function getSettings($settingsType = 1) {
        if (is_numeric($settingsType)) {
            Session::put('settingsType', $settingsType);
        }
        return View::make('user.settings')->with('settings', User::find(Auth::user()->id)->settings);
    }

    public function postSettings() {
        if ($settings = User::find(Auth::user()->id)->settings) {
            foreach (Item::typeTable() as $itemID => $item) {
                $settings->{$item['hide']} = Input::has($item['hide']) ? 1 : 0;
            }
            $settings->same_game_only = Input::has('same_game_only') ? 1 : 0;
            $settings->hide_profile = Input::has('hide_profile') ? 1 : 0;
			$settings->only_trade_offers = Input::has('only_trade_offers') ? 1 : 0;
            if (Input::has('tradeoffer_url')) {
				$url = str_replace('http://', '', Input::get('tradeoffer_url'));
				$url = str_replace('https://', '', $url);
				if (strpos($url, "steamcommunity.com/tradeoffer/new/") === 0) {
					$settings->tradeoffer_url = "http://" . $url;
					$settings->hide_friend_button = Input::has('hide_friend_button') ? 1 : 0;
					Session::forget('offer_url_reminder');
				} else {
					$error = "Invalid offer URL";
				}
			} else {
				$settings->hide_friend_button = 0;
				$settings->tradeoffer_url = "";
			}
			$settings->save();
		}
		$set = User::find(Auth::user()->id)->settings;
		if(isset($error)) $set['error'] = $error;
		return View::make('user.settings')->with('settings', $set);
	}

	public function getLogout() {
		Auth::logout();
		return Redirect::to('/');
	}

}

