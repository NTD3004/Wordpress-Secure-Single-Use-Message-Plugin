<?php

function securemessage($atts,$content='')
{
	extract(shortcode_atts(array(
		'id' => '',
		'description'  => 'Secure single-use message system',
		'header_text' => 'Use this form to generate a secure single-use message that can only be read ONCE by the recipient. When you are done composing your message, click the button to generate a unique URL that you can send to your intended recipient. Contents of the message are destroyed on the server as soon as the message has been retrieved.',
		'header_text2' => 'The contents of this message can only be viewed once. If you came here, and the message is not viewable, then this message has already been read. Contents are destroyed on the server as soon as the message has been read.'

	),$atts));

	$html = '';

	if( get_option('ssmstitle') and get_option('ssmstitle') != '' ) {
		$title = get_option('ssmstitle');
	}
	if( get_option('ssmsdescription') and get_option('ssmsdescription') != '' ) {
		$description = get_option('ssmsdescription');
	}
	if( get_option('ssmsheadertext') and get_option('ssmsheadertext') != '' ) {
		$header_text = get_option('ssmsheadertext');
	}
	if( get_option('ssmsheadertext2') and get_option('ssmsheadertext2') != '' ) {
		$header_text2 = get_option('ssmsheadertext2');
	}

	$html .= '
	<style>
	label {
		font-family: Roboto, sans-serif;
		font-weight: bold;
	}
	#container {
		max-width: 800px;
		margin: 0 auto;
		padding: 1em;
	}
	#msg {
		width: 100%;
		background: #f0ffff;
	}
	#playmsg {
		margin: 1em 0;
		font-size: 1.2em;
		background: #f0ffff;
	}
	.highlight {
		background: yellow;
	}
	input[type=submit] {
		margin-top: 1em;
		padding: 0.8em;
		background: #000;
		color: #fff;
	}
	</style>
	';

	$html .= '
		<h3>'.$description.'</h3>
	';

	if( isset($_SESSION['message_id'] ) ) {
		$html .= '<p>'.$header_text2.'</p>';
	} else {
		$html .= '<p>'.$header_text.'</p>';
	}

	global $wp;
	$current_url = home_url(add_query_arg(array(),$wp->request));

	if( $id ) {

		$messageid = $id;
		$ssms = new SSMS();

		$result = $ssms->getMessageById($messageid);

		if ($result['viewed'] == 0) {
			$message = $result['message'];

			$ssms->updateMessageViewedById($messageid);
		}
		else {
			$ipaddress = $result['ipaddress'];
			$timestamp = $result['timestamp'];
		}

		if (isset($timestamp) && isset($ipaddress)) {
			$html .= '
				<p class="highlight">This message was read on '.$timestamp.' from computer ip address '.$ipaddress.'.</p>
			';
		}

		if (isset($message)) {
			$html .= '
				<button id="play">Play message</button>
				<div id="playmsg"></div>
			';
		}

	}

	$html .= '
			<script type="text/javascript">
				function base64_decode(a){var b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var c,d,e,f,g,h,i,j,k=0,l=0,m="",n=[];if(!a){return a}a+="";do{f=b.indexOf(a.charAt(k++));g=b.indexOf(a.charAt(k++));h=b.indexOf(a.charAt(k++));i=b.indexOf(a.charAt(k++));j=f<<18|g<<12|h<<6|i;c=j>>16&255;d=j>>8&255;e=j&255;if(h==64){n[l++]=String.fromCharCode(c)}else if(i==64){n[l++]=String.fromCharCode(c,d)}else{n[l++]=String.fromCharCode(c,d,e)}}while(k<a.length);m=n.join("");return m}function base64_encode(a){var b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var c,d,e,f,g,h,i,j,k=0,l=0,m="",n=[];if(!a){return a}do{c=a.charCodeAt(k++);d=a.charCodeAt(k++);e=a.charCodeAt(k++);j=c<<16|d<<8|e;f=j>>18&63;g=j>>12&63;h=j>>6&63;i=j&63;n[l++]=b.charAt(f)+b.charAt(g)+b.charAt(h)+b.charAt(i)}while(k<a.length);m=n.join("");var o=a.length%3;return(o?m.slice(0,o-3):m)+"===".slice(o||3)}
			';
		$html .= '(function($) {
					$(document).ready(function() {';

		if($id) {

			if ( isset($message) ) {
				$html .= 'var msg = "'.$message.'";';
				$html .= 'var msg_decoded = base64_decode(msg);';
				$html .= 'var sentences = msg_decoded.match(/[^\.!\?]+[\.!\?]+/g);';
				$html .= '
					if (!sentences){
						sentences = [msg_decoded];
					}
					$("#play").click(function(e){
						e.preventDefault();
						var queue = $.Deferred();
						queue.resolve();
						$.each(sentences, function(i, sentence){
							queue = queue.pipe(function(){
								return $("#playmsg").show().html(sentence).fadeOut(5000);
							});
						});
					});
				';
			}

		}	
	

		$html .= '});
				})(jQuery);';
		$html .= '
			</script>
		';

	return $html;
}
add_shortcode( 'securemessage', 'securemessage' );