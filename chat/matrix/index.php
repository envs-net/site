<?php
  $title = "envs.net | matrix chat";
  $desc = "envs.net | matrix chat";

include '../../header.php';
?>

  <body id="body" class="dark-mode">
    <div class="clear" style="min-width: 1280px;">

      <div class="button_back">
        <pre class="clean"><strong><a href="/chat">&lt; back</a></strong></pre>
      </div>

      <div id="main">
<div class="block">
<h1><em>matrix chat</em></h1>
<pre>
our self-hosted matrix instance with riot-web client.

<a href="https://matrix.envs.net/" target="_blank">matrix.envs.net</a>
</pre>
<p></p>
</div>

<pre>
an open network for secure, decentralized communication.

<a href="https://matrix.envs.net/_matrix/client/#/register" target="_blank"><strong>&gt; create your matrix account on envs. &lt;</strong></a>

</pre>

<h2>&#35; identifiers/entities</h2>
<pre>
schema:
&nbsp;&nbsp;<code>&lt;tag&gt;&lt;id&gt;:&lt;instance&gt;</code>
</pre>
<br />
<ul>
  <li>user, which begin with tag <code>@</code></li>
  <li>chat-rooms (channel), which begin with tag <code>#</code></li>
  <li>communities (aka teams and groups), which begin with tag <code>+</code></li>
</ul>
<pre>example:</pre>
<ul>
  <li>user creme: <code>@creme:envs.net</code></li>
  <li>envs room: <code>#envs:envs.net</code></li>
  <li>envs group: <code>+envs:envs.net</code></li>
</ul>
<pre>
there's a convenient site, <a href="https://matrix.to" target="_blank">matrix.to</a>, which will
allow you to create urls linking to specific entities. when you visit these pages, you'll see links
to communicate with those entities. If you enter <code>+envs:envs.net</code> on <code>matrix.to</code>, you'll
get the URL <a href="https://matrix.to/#/+envs:envs.net" target="_blank">https://matrix.to/#/+envs:envs.net</a>.
</pre>
<br />

<h2>&#35; matrix client software</h2>
<pre>
<a href="https://about.riot.im/downloads" target="_blank">check out the official riot client!</a>

an extended client overview can be found at: <a href="https://matrix.org/clients/" target="_blank">https://matrix.org/clients/</a>

</pre>

<h2>&#35; links</h2>
<pre>
<a href="https://matrix.org/" target="_blank">matrix.org</a>
<a href="https://riot.im/" target="_blank">riot.im</a>
</pre>

      </div>

      <div id="sidebar">

<div class="block">
<pre>group
<code>+envs:envs.net</code>

channel
<code>#envs:envs.net</code>
</pre>
<p></p>
</div>

<div class="block">
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='https://riot.envs.net/';">
    <td><i class="fa fa-matrix-org fa-fw" aria-hidden="true"></i></td> <td><a href="https://riot.envs.net/">riot-web client</a></td>
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr onclick="window.location='https://dimension.envs.net/';">
    <td><i class="fa fa-object-ungroup fa-fw" aria-hidden="true"></i></td> <td><a href="https://dimension.envs.net/">dimension -<br />integrations manager</a></td>
  </tr>
</table>
</div>

<div class="block">
<pre>
<strong>rules / guidelines</strong>
<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
</pre>
<p></p>
</div>

      </div>

<?php include '../../footer.php'; ?>
