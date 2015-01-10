<?php

/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 1.0
 */
class HomeController extends BaseController {

	public function __construct() {
		parent::__construct();
	}

    public function getIndex() {
        return View::make('index');
    }

    public function getDisclaimer() {
        return View::make('disclaimer')->with('title', 'Disclaimer');
	}

	public function getDonate() {
		return View::make('donate.index')->with('title', 'Donating');
	}

    public function getPolicy() {
		return View::make('policy')->with('title', 'Privacy Policy');
	}

}