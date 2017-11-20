<?php

function ntd_securemessage_settings_page()
{
    if(session_id() == '') {
        session_start();
    }

    $isRead = false;
    if( isset($_SESSION['ntd_securemessage_id'] ) ) {
        global $wp;
        $messageid = $_SESSION['ntd_securemessage_id'];
        $ssms = new NTD_SecureMessage();
        $result = $ssms->getMessageById($messageid);
        if($result['viewed'] == 1) {
            $isRead = true;
        }
    }
    ?>
    <div class="wrap">
        <h1>Secure Message</h1>
        <form method="post" action="">
            <?php
                settings_fields("section");
                do_settings_sections("theme-options");
            ?> 
    <?php

    if($isRead) {
        $ipaddress = $result['ipaddress'];
        $timestamp = $result['timestamp'];
        $params = '?ntd_securemessage_action=refreshssms';

        if($_SERVER['QUERY_STRING']) {
            $params = '?'.$_SERVER['QUERY_STRING'].'&ntd_securemessage_action=refreshssms';
        }
    ?> 
            <p class="highlight">This message was read on <?php echo $timestamp ?> from computer ip address <?php echo $ipaddress ?>.</p>
            <p>To create new single-use message, click <a href="<?php echo $params ?>">here</a>.</p>
        </div>
    <?php
    } else {
        submit_button(); 
	?>        
	    </form>
	</div>
	<?php
    }
}

function ntd_securemessage_display_shortcode_element()
{
    if(session_id() == '') {
        session_start();
    }

    if( isset($_SESSION['ntd_securemessage_id']) ) {
        global $wp;
        $messageid = $_SESSION['ntd_securemessage_id'];
        $ssms = new NTD_SecureMessage();
        $result = $ssms->getMessageById($messageid);
        if($result && $result['viewed'] == 0) {
	?>
    	<p>[securemessage id="<?php echo $_SESSION['ntd_securemessage_id'] ?>"]</p>
    <?php
        } else {
    ?>
        <p>You need to create new message to get shortcode</p>
    <?php
        }
    } else {
    ?>
        <p>You need to create new message to get shortcode</p>
    <?php
    }
}

function ntd_securemessage_display_message_element()
{
    if(session_id() == '') {
        session_start();
    }

    $message = '';
    if( isset($_SESSION['ntd_securemessage_id'] ) ) {
        global $wp;
        $messageid = $_SESSION['ntd_securemessage_id'];
        $ssms = new NTD_SecureMessage();
        $result = $ssms->getMessageById($messageid);
        $message = base64_decode($result['message']);
    }
	?>
    	<textarea cols="30" rows="10" name="ntd_secure_message" id="ntd_secure_message" placeholder="The contents of this message can only be viewed once. If you came here, and the message is not viewable, then this message has already been read. Contents are destroyed on the server as soon as the message has been read." ><?php echo $message; ?></textarea>
    <?php
}

function ntd_securemessage_display_theme_panel_fields()
{
    add_settings_section("section", "All Settings", null, "theme-options");
    add_settings_field("ssmsshortcode", "Shortcode", "ntd_securemessage_display_shortcode_element", "theme-options", "section");
    add_settings_field("ntd_secure_message", "Message", "ntd_securemessage_display_message_element", "theme-options", "section");

    register_setting("section", "ntd_secure_message");
}

add_action("admin_init", "ntd_securemessage_display_theme_panel_fields");

function ntd_securemessage_add_menu_item()
{
	add_menu_page("Secure Message", "Secure Message", "manage_options", "theme-panel", "ntd_securemessage_settings_page", null, 99);
}

add_action("admin_menu", "ntd_securemessage_add_menu_item", 10);