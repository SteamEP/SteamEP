@extends('main.main')
@section('content')
<div class="row">
	<h1>Donating</h1>
</div>
<div class="row">
	<p class="centered"> <br />
	<span style="font-size:18px">To donate dogecoin (such doge):</span> <br />
	<span>D5UGzFfSPcCpJPRjKhMoEHrNFwK7fHfix4</span>
	<br /><br />
	<span style="font-size:18px">To donate bitcoin:</span> <br />
	<span>1FWfjN9xNEA12Af8D6ghirw8xZccSRShNZ</span>
	</p><br /><br />
	<div class="centered">
		<span style="font-size:18px">Or if you want to use paypal instead:</span>
		<span><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="8JJWJCDCJV6XC">
		<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
		<img alt="" border="0" src="https://www.paypalobjects.com/nl_NL/i/scr/pixel.gif" width="1" height="1">
		</form></span>
	</div>
</div>
@stop
