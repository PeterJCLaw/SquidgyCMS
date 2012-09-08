<?php
#name = Email
#description = Provides a wrapper around PHP's email function
#package = Core - required
#type = system
###

class Email {
	function Email() {
		$this->__construct();
	}

	function __construct() {
		$this->to	= array();
		$this->cc	= array();
		$this->bcc	= array();
		$this->from	= array();
		$this->headers	= array();
	}

	/**
	 * Add a recipient.
	 */
	function add_to($email, $name=FALSE) {
		$this->to[$email] = $name;
	}

	/**
	 * Add a CC recipient.
	 */
	function add_cc($email, $name=FALSE) {
		$this->cc[$email] = $name;
	}

	/**
	 * Add a BCC recipient.
	 */
	function add_bcc($email, $name=FALSE) {
		$this->bcc[$email] = $name;
	}

	/**
	 * Set a From address.
	 */
	function set_from($email, $name=FALSE) {
		$this->from = array();	//only allow one from address, so clear any previous ones
		$this->from[$email] = $name;
	}

	/**
	 * Set a Reply-To address.
	 */
	function set_replyTo($email, $name=FALSE) {
		// only allow one reply-to address, so clear any previous ones
		$this->replyTo = array();
		$this->replyTo[$email] = $name;
	}

	/**
	 * Add a single header line.
	 */
	function add_header($header) {
		$this->headers[] = trim($header);
	}

	/**
	 * Set the subject.
	 */
	function set_subject($subject) {
		$this->subject = $subject;
	}

	/**
	 * Set the message body.
	 */
	function set_body($body) {
		$this->body = $body;
	}

	/**
	 * Convert an array of addresses into a comma separated string ready for sending.
	 */
	function addresses_to_string($arr) {
		$list = array();
		foreach($arr as $email => $name) {
			$list[] = (empty($name) ? '' : "$name ")."<$email>";
		}
		return implode(', ', $list);
	}

	/**
	 * Actually send the email.
	 */
	function send() {
		$to = $this->addresses_to_string($this->to);

		$parts = array(
			'cc'=>'CC',
			'bcc'=>'BCC',
			'from'=>'From',
			'replyTo'=>'Reply-To',
		);
		foreach($parts as $name => $label) {
			if(!empty($this->$name))
			{
				$this->add_header($label.': '.$this->addresses_to_string($this->$name));
			}
		}

		$headers = implode("\r\n", $this->headers);

		return @mail($to, $this->subject, $this->body, $headers);
	}
}

class EmailLink extends Email {
	function EmailLink() {
		$this->__construct();
	}

	function __construct() {
		parent::__construct();
	}

	/**
	 * Convert an array of addresses into a comma separated string ready for using in a mailto link.
	 */
	function addresses_to_string($arr) {
		return implode(',', array_keys($arr));
	}

	/**
	 * Generate a mailto link.
	 */
	function link($full=FALSE, $text='', $opts=array()) {
		foreach(array('cc', 'bcc', 'subject', 'body') as $name) {
			if(empty($this->$name))
				continue;
			if($name == 'cc' || $name == 'bcc')
				$value = $this->addresses_to_string($this->$name);
			else
				$value = $this->$name;
			$bits[] = "$name=".htmlspecialchars($value);
		}

		$mailto = empty($bits) ? '' : '?'.implode('&amp;', $bits);
		$mailto = 'mailto:'.$this->addresses_to_string($this->to).$mailto;

		if(!$full)
			return $mailto;

		if(empty($text))
			$text = 'Send them an email';

		$html = '';
		foreach(array('id', 'class', 'title') as $attribute) {
			if(!empty($opts[$attribute]))
				$html .= " $attribute=\"$opts[$attribute]\"";
		}

		return "<a href=\"$mailto\"$html>$text</a>";
	}
}
