<?php
  $title = "envs.net | matrix chat";
  $desc = "envs.net | matrix chat";

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
<main>
	<div class="block">
		<h1>matrix chat</h1>
		<p>our self-hosted matrix instance with web-clients.</p>

		<p><i class="fa fa-matrix-org fa-fw" aria-hidden="true"></i> <a href="https://matrix.envs.net/" target="_blank">matrix.envs.net</a></p>
	</div>

	<p>an open network for secure, decentralized communication.</p>

	<p><a href="https://element.envs.net/#/register" target="_blank"><strong>&gt; create your matrix account on envs. &lt;</strong></a></p>

	<h2>identifiers/entities</h2>

	<p>schema:<br>
	&nbsp;&nbsp;<code>&lt;tag&gt;&lt;id&gt;:&lt;instance&gt;</code></p>

	<ul>
	  <li>user, which begin with tag <code>@</code></li>
	  <li>chat-rooms (channel), which begin with tag <code>#</code></li>
	  <li>spaces - collection of rooms and other spaces, begin also with tag <code>#</code></li>
	</ul>

	<p>example:</p>
	<ul>
	  <li>user creme: <a href="https://matrix.to/#/@creme:envs.net" target="_blank"><code>@creme:envs.net</code></a></li>
	  <li>envs room: <a href="https://matrix.to/#/#envs:envs.net" target="_blank"><code>#envs:envs.net</code></a></li>
	  <li>envs space: <a href="https://matrix.to/#/#space:envs.net" target="_blank"><code>#space:envs.net</code></a></li>
	</ul>

	<p>there's a convenient site, <a href="https://matrix.to" target="_blank">matrix.to</a>, which will allow you to create urls linking to specific entities. when you visit these pages, you'll see links to communicate with those entities. If you enter <code>#envs:envs.net</code> on <code>matrix.to</code>, you'll get the URL <a href="https://matrix.to/#/#envs:envs.net" target="_blank">https://matrix.to/#/#envs:envs.net</a>.</p>


	<h2>matrix&sol;element help &amp; documentation</h2>

	<p><a href="https://matrix-help.envs.net/" target="_blank">check out our matrix&sol;element help page.</a></p>


	<h2>matrix client software</h2>

	<p><a href="https://element.io/get-started" target="_blank">check out the official element client!</a></p>

	<p>an extended client overview can be found at: <a href="https://matrix.org/clients/" target="_blank">https://matrix.org/clients/</a></p>


	<h2> privacy policy</h2>

	<p>The privacy policy for the envs.net homeserver is available at <a href="https://matrix.envs.net/_matrix/consent" target="_blank">https://matrix.envs.net/_matrix/consent</a>.</p>

	<h2>links</h2>

	<ul>
		<li><a href="https://matrix.org/" target="_blank">matrix.org</a></li>
		<li><a href="https://element.io/" target="_blank">element.io</a></li>
	</ul>

</main>


<div id="sidebar">

	<div class="block">
		<p>server admin<br>
		<a href="https://matrix.to/#/@creme:envs.net" target="_blank"><code>@creme:envs.net</code></a></p>

		<p>space<br>
		<a href="https://matrix.to/#/#space:envs.net" target="_blank"><code>#space:envs.net</code></a></p>

		<p>channel<br>
		<a href="https://matrix.to/#/#envs:envs.net" target="_blank"><code>#envs:envs.net</code></a><br>
		<a href="https://matrix.to/#/#lounge:envs.net" target="_blank"><code>#lounge:envs.net</code></a></p>

		<p>abuse management<br>
		<a href="https://matrix.to/#/#abuse:envs.net" target="_blank"><code>#abuse:envs.net</code></a></p>
	</div>

	<div class="block">

		<ul class="icon-list">
			<li><a href="https://element.envs.net/"><i class="fa-hand-o-right"></i>element-web client</a></li>
			<li><a href="https://cinny.envs.net"><i class="fa-hand-o-right"></i>cinny webclient</a></li>
			<li><a href="https://hydrogen.envs.net/"><i class="fa-hand-o-right"></i>hydrogen-web client</a></li>
		</ul>

		<ul class="icon-list">
			<li><a href="https://lag.envs.net/"><i class="fa-arrows-h"></i>latency monitor</a></li>
			<li><a href="https://fed.envs.net"><i class="fa-refresh"></i>federation tester</a></li>
		</ul>

	</div>

	<div class="block">
		<p>
			<strong>rules &sol; guidelines</strong></b>
			<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
		</p>
	</div>

</div>

<?php include '../../neoenvs_footer.php'; ?>
