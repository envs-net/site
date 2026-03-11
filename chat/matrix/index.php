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
<main class="content">
	<div class="block">
		<h1>matrix chat</h1>
		<p>envs does NOT operate its own matrix server.</p>
		<p>
			channel: <a href="https://matrix.to/#/#envs:matrix.org" target="_blank"><code>#envs:matrix.org</code></a> &#124;
			space: <a href="https://matrix.to/#/#envs-space:matrix.org" target="_blank"><code>#envs-space:matrix.org</code></a>
		</p>
	</div>

	<p>matrix is an open network for secure, decentralized communication.</p>

	<h2>identifiers/entities</h2>

	<p>schema:<br />
	&nbsp;&nbsp;<code>&lt;tag&gt;&lt;id&gt;:&lt;instance&gt;</code></p>

	<ul>
	  <li>user, which begin with tag <code>@</code></li>
	  <li>chat-rooms (channel), which begin with tag <code>#</code></li>
	  <li>spaces - collection of rooms and other spaces, begin also with tag <code>#</code></li>
	</ul>

	<p>example:</p>
	<ul>
	  <li>user creme: <a href="https://matrix.to/#/@creme:matrix.org" target="_blank"><code>@creme:matrix.org</code></a></li>
	  <li>envs room: <a href="https://matrix.to/#/#envs:matrix.org" target="_blank"><code>#envs:matrix.org</code></a></li>
	  <li>envs space: <a href="https://matrix.to/#/#envs-space:matrix.org" target="_blank"><code>#envs-space:matrix.org</code></a></li>
	</ul>

	<p>there's a convenient site, <a href="https://matrix.to" target="_blank">matrix.to</a>, which will allow you to create urls linking to specific entities. when you visit these pages, you'll see links to communicate with those entities. If you enter <code>#envs:matrix.org</code> on <code>matrix.to</code>, you'll get the URL <a href="https://matrix.to/#/#envs:matrix.org" target="_blank">https://matrix.to/#/#envs:matrix.org</a>.</p>


	<h2>matrix client software</h2>

	<p><a href="https://element.io/get-started" target="_blank">check out the official element client!</a></p>

	<p>an extended client overview can be found at: <a href="https://matrix.org/clients/" target="_blank">https://matrix.org/clients/</a></p>

	<h2>links</h2>

	<ul>
		<li><a href="https://matrix.org/" target="_blank">matrix.org</a></li>
		<li><a href="https://element.io/" target="_blank">element.io</a></li>
	</ul>

</main>


<div id="sidebar">

	<div class="block">
		<h3>matrix</h3>
		<p>
			channel:<br />
			<code>#envs:matrix.org</code>
		</p>
		<p>
			space:<br />
			<code>#envs-space:matrix.org</code>
		</p>
	</div>

	<div class="block">
		<p>
			<strong>rules &sol; guidelines</strong><br />
			<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
		</p>
	</div>

</div>

<?php include '../../neoenvs_footer.php'; ?>
