# whmcs-slack-notif

WHMCS hook for ticket open and reply from owner. Also it send push notification into Slack channel

# Installation

Copy needed PHP file into WHMCS directory ```$WHMC_ROOT/includes/hooks```.

# Setting

Each PHP file contations function ```get_options```. You have to fill it with your private data.

Example:
```
function get_options() {
	return array(
      'url' => 'https://slack.com/api/chat.postMessage',
      'channel_tickets' => '000000000',
      'token' => 'xxxx-xxxxxxxxx-xxxx',
      'admin_user' => 'admin',
      'whmcs_host' => 'http://mywhmcs.com/'
  );
}
```

