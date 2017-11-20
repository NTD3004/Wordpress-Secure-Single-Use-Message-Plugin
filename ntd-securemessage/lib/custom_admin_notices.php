<?php

class NTD_CustomAdminNotices {

    private $_message;

    public function __construct( $message, $isSuccess = true ) {
        $this->_message = $message;

        if($isSuccess) {
        	add_action( 'admin_notices', array( $this, 'renderSuccess' ) );
        } else {
        	add_action( 'admin_notices', array( $this, 'renderError' ) );
        }
    }

    public function renderSuccess() {
        printf( '<div class="notice notice-success is-dismissible">%s</div>', $this->_message );
    }

    public function renderError() {
        printf( '<div class="notice notice-error is-dismissible">%s</div>', $this->_message );
    }

}