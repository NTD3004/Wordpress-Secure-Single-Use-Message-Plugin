<?php

function ssms_settings_page()
{
    $isRead = false;
    if( isset($_SESSION['message_id'] ) ) {
        global $wp;
        $messageid = $_SESSION['message_id'];
        $ssms = new SSMS();
        $result = $ssms->getMessageById($messageid);
        if($result['viewed'] == 1) {
            $isRead = true;
        }
    }

    if($isRead) {
        $ipaddress = $result['ipaddress'];
        $timestamp = $result['timestamp'];
        $params = '?ssmsaction=refreshssms';

        if($_SERVER['QUERY_STRING']) {
            $params = '?'.$_SERVER['QUERY_STRING'].'&ssmsaction=refreshssms';
        }
    ?> 
        <div class="wrap">
            <h1>Secure Message</h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields("section");
                    do_settings_sections("theme-options");
                ?>          
            </form>
            <p class="highlight">This message was read on <?php echo $timestamp ?> from computer ip address <?php echo $ipaddress ?>.</p>
            <p>To create new single-use message, click <a href="<?php echo $params ?>">here</a>.</p>
        </div>
    <?php
    } else {
	?>
	    <div class="wrap">
	    <h1>Secure Message</h1>
	    <form method="post" action="options.php">
	        <?php
	            settings_fields("section");
	            do_settings_sections("theme-options");      
	            submit_button(); 
	        ?>          
	    </form>
		</div>
	<?php
    }
}

function display_shortcode_element()
{
    if( isset($_SESSION['message_id']) ) {
        global $wp;
        $messageid = $_SESSION['message_id'];
        $ssms = new SSMS();
        $result = $ssms->getMessageById($messageid);
        if($result && $result['viewed'] == 0) {
	?>
    	<p>[securemessage id="<?php echo $_SESSION['message_id'] ?>"]</p>
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

function display_message_element()
{
    $message = '';
    if( isset($_SESSION['message_id'] ) ) {
        global $wp;
        $messageid = $_SESSION['message_id'];
        $ssms = new SSMS();
        $result = $ssms->getMessageById($messageid);
        $message = base64_decode($result['message']);
    }
	?>
    	<textarea cols="30" rows="10" name="ssmsmessage" id="ssmsmessage" placeholder="The contents of this message can only be viewed once. If you came here, and the message is not viewable, then this message has already been read. Contents are destroyed on the server as soon as the message has been read." ><?php echo $message; ?></textarea>
    <?php
}

function display_theme_panel_fields()
{
    add_settings_section("section", "All Settings", null, "theme-options");
    add_settings_field("ssmsshortcode", "Shortcode", "display_shortcode_element", "theme-options", "section");
    add_settings_field("ssmsmessage", "Message", "display_message_element", "theme-options", "section");

    register_setting("section", "ssmsmessage");
}

add_action("admin_init", "display_theme_panel_fields");

function add_ssms_menu_item()
{
	add_menu_page("Secure Message", "Secure Message", "manage_options", "theme-panel", "ssms_settings_page", null, 99);
}

add_action("admin_menu", "add_ssms_menu_item", 10);