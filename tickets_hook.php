<?php
/**
 * @package	WHMCS Ticket notification
 * @author	Orlov Vitaliy
 * @copyright	Copyright (c)
 * @version	1.0
 */

if (!defined('WHMCS'))
	die('This file cannot be accessed directly');

function get_options() {
	return array(
		'url' => 'https://slack.com/api/chat.postMessage',
		'channel_tickets' => '<your channel id>',
		'token' => '<access token>',
		'admin_user' => '<admin login>',
		'whmcs_host' => '<whmcs host name'
  );
}

function write_log($message) {
	$command = 'logactivity';
	$values = array('description' => $message);
	$results = localAPI($command, $values, get_options()['admin_user']);
}

function send_mesage($channel, $message, $attachment) {
	$options = get_options();
	$ch = curl_init($options['url']);

	$json_data = array(
		'as_user' => 'true',
		'token' => $options['token'],
		'channel' => $channel,
		'text' => $message,
		'attachments' => json_encode($attachment)
	);

	$data_string = http_build_query($json_data);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($ch);

	if ($result === false) {
		write_log('Error curl: ' . curl_error($ch));
	}

	curl_close($ch);
}

function get_client_info($id) {
	$command = 'getclientsdetails';
	$values = array('clientid' => $id);
	$results = localAPI($command, $values, get_options()['admin_user']);
	if ($results['result'] != 'success') {
		write_log('An Error Occurred: ' . $results['message']);
		return false;
	} else {
		return $results;
	}
}

function get_ticket_info($id) {
	$command = 'getticket';
	$values = array('ticketid' => $id);
	$results = localAPI($command, $values, get_options()['admin_user']);
	if ($results['result'] != 'success') {
		write_log('An Error Occurred: ' . $results['message']);
		return false;
	} else {
		return $results;
	}
}

function common_ticket($vars, $pre) {
	$options = get_options();
	$ticketid = $vars['ticketid'];
	$userid = $vars['userid'];

	$user = get_client_info($userid);
	$ticket = get_ticket_info($ticketid);

	if (!$user || !$ticket) return;

	$msg = $pre . $ticket['tid'] . ' from ' . $user['lastname'] . ' ' . $user['firstname'] . '(' . $user['email'] . ')';
	$url = $options['whmcs_host'] . '/supporttickets.php?action=view&id=' . $ticketid;

	$attach = [array('title' => $ticket['subject'], 'text' => $url)];

    send_mesage($options['channel_tickets'], $msg, $attach);
}

function on_ticketopen($vars) {
	common_ticket($vars, 'New ticket ');
}

function on_ticketuserreply($vars) {
	common_ticket($vars, 'New reply in ticket ');
}

add_hook('TicketOpen', 1, 'on_ticketopen');
add_hook('TicketUserReply', 1, 'on_ticketuserreply');
