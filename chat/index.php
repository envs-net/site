<?php
	$title = "envs.net | chat";
	$desc = "envs.net | join the envs.net community chat via XMPP, IRC or Matrix.";

include '../neoenvs_header.php';
?>

<body id="body">

<!-- Back button -->
<nav class="sidenav">
	<a href="/">
		<img src="https://envs.net/img/envs_logo_200x200.png" class="site-icon" title="Back to the envs.net homepage">
	</a>
</nav>

<!-- main panel -->
<main class="content">
	<div class="block">
		<h1>community chat</h1>
		<p>
			The envs.net community communicates using open chat protocols.<br />
			Our main chat is available via <strong>XMPP</strong> (also known as Jabber),<br />
			with additional rooms on <strong>IRC</strong> and <strong>Matrix</strong>.
			<br /><br />
			<strong>rules / guidelines:</strong><br />
			please see the <a href="/chat/etiquette">chat etiquette guide</a>.
		</p>
	</div>

	<p>
		Everyone is welcome. You can use the chat to ask questions about envs.net,<br />
		talk about Linux / Unix, programming, selfhosting and homelabbing or just hang out with other users.
	</p>
	<p><strong>XMPP</strong> is our primary chat network and the best place to reach the communiy.</p>
	<p>
		If you're new, the easiest way to join is using the web client<br />
		or an XMPP client (e.g. Gajim or profanity).
	</p>

	<h2>more information about our</h2>
	<ul>
		<li><strong><a href="/chat/xmpp">xmpp / jabber chat</a></strong> <small>(main)</small></li>
		<li><strong><a href="/chat/irc">irc chat</a></strong></li>
		<li><strong><a href="/chat/matrix">matrix chat</a></strong></li>
	</ul>
</main>

<!-- sidebar -->
<div id="sidebar">

	<div class="block">
		<h3>xmpp / jabber</h3>
		<p>
			muc:<br>
			<code>envs@conference.envs.net</code>
		</p>
		<ul class="icon-list">
			<li><a href="https://webchat.envs.net/"><i class="fa-xmpp"></i>webclient</a></li>
		</ul>
	</div>

	<div class="block">
		<h3>irc</h3>
		<p>
			channel:<br>
			<code>#envs</code> on tilde.chat
		</p>
		<ul class="icon-list">
			<li><a href="https://webirc.envs.net/"><i class="fa-commenting-o"></i>webirc-client</a></li>
			<li><a href="https://znc.envs.net/"><i class="fa-compress"></i>znc interface</a></li>
		</ul>
	</div>

	<div class="block">
		<h3>matrix</h3>
		<p>
			channel:<br>
			<code>#envs:matrix.org</code>
		</p>
		<p>
			space:<br>
			<code>#envs-space:matrix.org</code>
		</p>
	</div>
</div>

<?php include '../neoenvs_footer.php'; ?>
