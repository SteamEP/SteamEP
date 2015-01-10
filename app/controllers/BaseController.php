<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 1.0
 */
class BaseController extends Controller {

	public function __construct() {
    	SteamAPI::$apiKey = Config::get('app.steam_api_key');
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (!is_null($this->layout))
        {
            $this->layout = View::make($this->layout);
        }
    }

}

