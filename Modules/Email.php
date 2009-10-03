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
		$this->headers	= '';
	}

	/* add a recipient */
	function add_to($email, $name=FALSE) {
		$this->to[$email] = $name;
	}

	/* add a CC recipient */
	function add_cc($email, $name=FALSE) {
		$this->cc[$email] = $name;
	}

	/* add a BCC recipient */
	function add_bcc($email, $name=FALSE) {
		$this->bcc[$email] = $name;
	}

	/* add a From address */
	function add_from($email, $name=FALSE) {
		$this->from = array();	//only allow one from address, so clear any previous ones
		$this->from[$email] = $name;
	}

	/* add headers */
	function add_headers($headers) {
		$this->headers .= $headers;
	}

	/* add a subject */
	function add_subject($subject) {
		$this->subject = $subject;
	}

	/* add a body */
	function add_body($body) {
		$this->body = $body;
	}

	/* convert an array of addresses into a comma separated string ready for sending */
	function addresses_to_string($arr) {
		foreach($arr as $email => $name) {
			$list[] = (empty($name) ? '' : "$name ")."<$email>";
		}
		return implode(', ', $list);
	}

	/* generate a mailto link */
	function link($full=FALSE, $text='', $opts=array()) {
		$mailto = 'mailto:'.$this->addresses_to_string($this->to);

		foreach(array('cc'=>'CC', 'bcc'=>'BCC') as $name => $label) {
			if(!empty($this->$name)) {
				$mailto .= "&amp;$label: ".$this->addresses_to_string($this->$name);
		}

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

	/* actually send the email */
	function send() {
		$to = $this->addresses_to_string($this->to);

		foreach(array('cc'=>'CC', 'bcc'=>'BCC', 'from'=>'From') as $name => $label) {
			if(!empty($this->$name)) {
				$this->headers .= "$label: ".$this->addresses_to_string($this->$name)."\r\n";
		}

		return @mail($to, $this->subject, $this->body, $this->headers);
	}
}

?>