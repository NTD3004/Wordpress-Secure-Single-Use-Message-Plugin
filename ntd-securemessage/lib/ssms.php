<?php

class NTD_SecureMessage
{
	private $table_name;
	public $message_id;

	public function __construct()
	{
		global $wpdb;

		$this->table_name = 'ntd_securemessage';
	}

	/**
	 * Retrieve IP Address
	 * @static
	 * @return $ip
	 */
	public function getIpAddress() 
	{
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
						return $ip;
					}
				}
			}
		}
	}

	/**
	 * Save the message that was posted from the form
	 * @param $message
	 * @return void
	 */
	public function saveMessage($message)
	{
		global $wpdb;

		$wpdb->insert( 
			$this->table_name, 
			array(
				'message' => $message, 
				'viewed' => 0 
			)
		);

		$this->message_id = $wpdb->insert_id;
	}

	public function updateMessageById($message_id, $message) {
		global $wpdb;

		$wpdb->update( 
			$this->table_name, 
			array( 
				'message' => trim($message)
			), 
			array( 'id' => $message_id )
		);
	}

	/**
	 * Update viewed message
	 * @param $message_id
	 * @return void
	 */
	public function updateMessageViewedById($message_id)
	{
		global $wpdb;

		$wpdb->update( 
			$this->table_name, 
			array( 
				'viewed' => '1',	
				'timestamp' => date(DATE_RFC822),
				'ipaddress' => $this->getIpAddress()
			), 
			array( 'id' => $message_id )
		);
	}

	/**
	 * Retrieve message by ID
	 * @param $message_id
	 * @return void
	 */
	public function getMessageById($message_id)
	{
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM ".$this->table_name." WHERE `id` = '{$message_id}'", ARRAY_A );
	}

}