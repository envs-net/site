<?php
  $title = "envs.net | chat";
  $desc = "envs.net | chat";

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
		<h1>chat</h1>
		<p>
			envs.net chat is available via xmpp (also known as jabber) and irc.<br />
			you can connect using your favorite chat client or directly in your browser.
		</p>

		<p><strong>rules / guidelines:</strong><br>
		<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em></p>
	</div>

	<h2>more information about our</h2>
	<ul>
		<li><strong><a href="/chat/xmpp">xmpp / jabber chat</a></strong> <small>(main)</small></li>
		<li><strong><a href="/chat/irc">irc chat</a></strong></li>
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
			<li><a href="https://webchat.envs.net/"><i class="fa-commenting-o"></i>webclient</a></li>
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
</div>

<?php include '../neoenvs_footer.php'; ?>
