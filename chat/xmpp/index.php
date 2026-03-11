<?php
	$title = "envs.net | xmpp chat";
	$desc = "envs.net | xmpp chat";

include '../../neoenvs_header.php';
?>

<body id="body">

<!-- Back button -->
<nav class="sidenav">
	<a href="/">
		<img src="https://envs.net/img/envs_logo_200x200.png" class="site-icon" title="Back to the envs.net homepage">
	</a>

	<a href=".." title="Back to the chat page">
		<i class="fa fa-comments-o" aria-hidden="true"></i>
	</a>

</nav>

<!-- main panel -->
<main class="content">
	<div class="block">
		<h1>xmpp (jabber) chat</h1>
		<p>
			<a href='https://compliance.conversations.im/server/envs.net' target="_blank"><img src='https://compliance.conversations.im/badge/envs.net'></a> 
			<a href="https://xmppnetwork.goodbytes.im/?focus=envs.net" target="_blank"><img alt="envs.net badge" src="https://xmppnetwork.goodbytes.im/badge/envs.net.svg"></a><br />
			our self-hosted xmpp instance for local envs users
		</p>

		<p>envs muc: <a href="xmpp:envs@conference.envs.net?join"><code>envs@conference.envs.net</code></a></p>
	</div>

	<p><strong>xmpp</strong> is our primary chat network and the best place to reach the community.</p>
	<p>we're running <strong>prosody</strong>, a modern xmpp communication server written in lua.</p>
	<p><strong>authentication</strong> is configured to use your <strong>shell password</strong>, so just connect with any xmpp client with username@envs.net and your password.</p>

	<h2>server settings</h2>
	<ul>
		<li>xmpp id: <code>username@envs.net</code></li>
		<li>Advanced settings: server/port</li>
		<ul>
			<li><code>envs.net/5222</code></li>
			<li>direct_tls: <code>envs.net/5223</code></li>
		</ul>
		<li>upload file size limit: 32 MB</li>
		<li>archived messages and files expire after 3 month</li>
	</ul>

	<h2>xmpp client software</h2>

	<p>an client overview can be found at: <a href="https://xmpp.org/software/clients.html" target="_blank">https://xmpp.org/software/clients.html</a></p>
	<p>
		envs also has a web client (converse.js) that you are welcome to use.<br />
		URL: <a href="https://webchat.envs.net/" target="_blank">https://webchat.envs.net/</a>
	</p>
</main>


<div id="sidebar">

	<div class="block">
		<h3>xmpp (jabber)</h3>
		<p>server admin<br />
		<code>creme@envs.net</code></p>

		<p>envs muc:<br />
		<code>envs@conference.envs.net</code></p>

		<p>offtopic muc:<br />
		<code>lounge@conference.envs.net</code></p>
	</div>

	<div class="block">
		<ul class="icon-list">
			<li><a href="https://webchat.envs.net/" target="_blank"><i class="fa-xmpp"></i>webclient</a></li>
		</ul>
	</div>

	<div class="block">
		<p>
			<strong>rules &sol; guidelines</strong><br />
			<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
		</p>
	</div>

</div>

<?php include '../../neoenvs_footer.php'; ?>
