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
<main>
	<div class="block">
		<h1>chat</h1>
		<p>envs.net can be reached only via matrix.</p>

		<p><strong>rules / guidelines:</strong><br>
		<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em></p>
	</div>

	<h2>more information about our</h2>
	<ul>
		<li><a href="/chat/matrix">matrix chat</a></li>
		<li><a href="/chat/irc">irc chat</a></li>
	</ul>
</main>

<!-- sidebar -->
<div id="sidebar">

	<div class="block">
		<h3>matrix</h3>
		<p>
			channel:<br>
			<code>#envs:envs.net</code>
		</p>

		<ul class="icon-list">
			<li><a href="https://element.envs.net/"><i class="fa-hand-o-right"></i>element-web client</a></li>
			<li><a href="https://cinny.envs.net"><i class="fa-hand-o-right"></i>cinny webclient</a></li>
			<li><a href="https://hydrogen.envs.net/"><i class="fa-hand-o-right"></i>hydrogen-web client</a></li>
		</ul>
	</div>

	<div class="block">
		<h3>irc</h3>
		<ul class="icon-list">
			<li><a href="https://webirc.envs.net/"><i class="fa-commenting-o"></i>webirc-client</a></li>
			<li><a href="https://znc.envs.net/"><i class="fa-compress"></i>znc interface</a></li>
		</ul>
	</div>
</div>

<?php include '../neoenvs_footer.php'; ?>
