<?php
  $title = "envs.net | irc chat";
  $desc = "envs.net | irc chat";

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
		<h1>envs.net | irc chat</h1>
		<p>	envs.net does not have its own irc server. but feel free to use the <a href="https://tildeverse.org/" target="_blank">tildeverse</a> irc server.</p>

		<p>envs channel: <code>#envs</code></p>

		<p>&gt; <a href="https://tilde.chat/stats/" target="_blank">channel list and server stats</a></p>
	</div>

	<p>the tilde.chat irc network is available at <code>irc.tilde.chat:6697</code> (with ssl) as a round robin of <a href="https://tilde.chat/wiki/?page=servers" target="_blank">available nodes</a>.</p>

	<p>tilde.chat is meant to be a space for all tilde members to hang out, share, teach, and learn.</p>

	<p>Hang out in <b>#meta</b> the tilde general channel. =)</p>

	<p><b>note</b> you must register with <b>nickserv</b> to talk in <code>#meta</code>:<br>
	<code>/msg nickserv register &lt;password&gt; &lt;email&gt;</code></p>


	<h2>here are some options to connect</h2>

	<ul>
		<li><code>weechat</code> (<em>when logged in with ssh</em>) / <code>irssi</code> (<em>needs to configure</em>)</li>
		<li><code><a href="https://znc.envs.net/" target="_blank">znc</a></code> - please contact <a href="https://envs.net/~creme/">creme</a> (via email) to request an account,<br />then log in with your email password and configure as needed.</li>
		<li>run <code>webirc</code> to register your thelounge account for <a href="https://webirc.envs.net/" target="_blank">our webchat</a><br /><em>note that thelounge does not authenticate with your shell/mail password.</em></li>
		<li>any other client that you like: connect to our node at <code>irc.tilde.chat:6697</code> with ssl</li>
	</ul>


	<h3>znc (irc bouncer)</h3>

	<p><small>please contact <a href="https://envs.net/~creme/">creme</a> (via email) to request an account.</small></p>

	<p>the system znc authenticates using your email credantials (<em>user: username</em> | <em>password: your email pw</em>).</p>

	<p>the znc interface is available at <a href="https://znc.envs.net/" target="_blank">znc.envs.net</a>.</p>

	<p>client connections are on port <code>6667</code> and <code>6697</code> (both with ssl).</p>

	<p>you can add network connections (up to 10) via webadmin or via a direct client connection.</p>

	<p>see the <a href="https://wiki.znc.in/Connecting_to_ZNC" target="_blank">znc wiki</a> for more information.</p>


	<h3>weechat relays</h3>

	<p>weechat introduced <a href="https://weechat.org/files/doc/stable/weechat_user.en.html#relay_unix_socket" target="_blank">unix socket relays</a> in version 2.5 which is a much easier way to offer per-user relay access.</p>

	<p><code><em>username.envs.net/weechat</em></code> is configured to proxy to the default unix relay socket location (<code>~/.weechat/relay_socket</code>). to get started using it, follow these steps:</p>

	<ol>
	  <li>in weechat:
	    <ul>
	      <li><code>/relay add unix.weechat %h/relay_socket</code></li>
	      <li><code>/set relay.network.password mysupersecretpassword</code></li>
	    </ul>
	  </li>
	  <li>at your shell:
	    <ul>
	      <li><code>chmod o+rw ~/.weechat/relay_socket</code> - <em>this allows nginx to connect to your socket on your behalf</em></li>
	    </ul>
	  </li>
	  <li>in your relay client:
	    <ul>
	      <li>weechat-android:
	        <ul>
	          <li>connection type: websocket (ssl)</li>
	          <li>websocket path: weechat</li>
	          <li>relay host: username.envs.net</li>
	          <li>relay port: 443</li>
	          <li>your relay supersecretpassword</li>
	        </ul>
	      </li>
	    </ul>
	  </li>
	</ol>

</main>

<div id="sidebar">

	<nav class="block">
		<ul class="icon-list">
			<li><a href="https://webirc.envs.net/"><i class="fa-commenting-o"></i>webirc-client</a></li>
			<li><a href="https://znc.envs.net/"><i class="fa-compress"></i>znc interface</a></li>
		</ul>
	</nav>

	<div class="block">
		<p>
			<strong>rules &sol; guidelines</strong><br />
			<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
		</p>

		<p><strong>wiki</strong><br />
		see the <a href="https://tilde.chat/wiki/" target="_blank">tilde.chat wiki</a> for specifics.</p>
	</div>

</div>

<?php include '../neoenvs_footer.php'; ?>
