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
our self-hosted matrix instance with web-clients.

<i class="fa fa-matrix-org fa-fw" aria-hidden="true"></i> <a href="https://matrix.envs.net/" target="_blank">matrix.envs.net</a>
</pre>
<p></p>
</div>

<pre>
an open network for secure, decentralized communication.

<a href="https://element.envs.net/#/register" target="_blank"><strong>&gt; create your matrix account on envs. &lt;</strong></a>

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
  <li>spaces - collection of rooms and other spaces, begin also with tag <code>#</code></li>
</ul>
<pre>example:</pre>
<ul>
  <li>user creme: <a href="https://matrix.to/#/@creme:envs.net" target="_blank"><code>@creme:envs.net</code></a></li>
  <li>envs room: <a href="https://matrix.to/#/#envs:envs.net" target="_blank"><code>#envs:envs.net</code></a></li>
  <li>envs space: <a href="https://matrix.to/#/#space:envs.net" target="_blank"><code>#space:envs.net</code></a></li>
</ul>
<pre>
there's a convenient site, <a href="https://matrix.to" target="_blank">matrix.to</a>, which will
allow you to create urls linking to specific entities. when you visit these pages, you'll see links
to communicate with those entities. If you enter <code>#envs:envs.net</code> on <code>matrix.to</code>, you'll
get the URL <a href="https://matrix.to/#/#envs:envs.net" target="_blank">https://matrix.to/#/#envs:envs.net</a>.
</pre>
<br />

<h2>&#35; matrix&sol;element help &amp; documentation</h2>
<pre>
<a href="https://matrix-help.envs.net/" target="_blank">check out our matrix&sol;element help page.</a>
</pre>
<br />

<h2>&#35; matrix client software</h2>
<pre>
<a href="https://element.io/get-started" target="_blank">check out the official element client!</a>
an extended client overview can be found at: <a href="https://matrix.org/clients/" target="_blank">https://matrix.org/clients/</a>
</pre>
<br />

<h2>&#35; privacy policy</h2>
<pre>
The privacy policy for the envs.net homeserver is available at <a href="https://matrix.envs.net/_matrix/consent" target="_blank">https://matrix.envs.net/_matrix/consent</a>.
</pre>
<br />

<h2>&#35; links</h2>
<pre>
<a href="https://matrix.org/" target="_blank">matrix.org</a>
<a href="https://element.io/" target="_blank">element.io</a>
</pre>

      </div>

      <div id="sidebar">

<div class="block">
<pre>server admin
<a href="https://matrix.to/#/@creme:envs.net" target="_blank"><code>@creme:envs.net</code></a>

space
<a href="https://matrix.to/#/#space:envs.net" target="_blank"><code>#space:envs.net</code></a>

channel
<a href="https://matrix.to/#/#envs:envs.net" target="_blank"><code>#envs:envs.net</code></a>
<a href="https://matrix.to/#/#lounge:envs.net" target="_blank"><code>#lounge:envs.net</code></a>

abuse management
<a href="https://matrix.to/#/#abuse:envs.net" target="_blank"><code>#abuse:envs.net</code></a>
</pre>
<p></p>
</div>

<div class="block">
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='https://element.envs.net/';">
    <td><i class="fa fa-hand-o-right fa-fw" aria-hidden="true"></i></td> <td><a href="https://element.envs.net/">element-web client</a></td>
  </tr>
  <tr onclick="window.location='https://cinny.envs.net/';">
    <td><i class="fa fa-hand-o-right fa-fw" aria-hidden="true"></i></td> <td><a href="https://cinny.envs.net/">cinny webclient</a></td>
  </tr>
  <tr onclick="window.location='https://hydrogen.envs.net/';">
    <td><i class="fa fa-hand-o-right fa-fw" aria-hidden="true"></i></td> <td><a href="https://hydrogen.envs.net/">hydrogen-web client<br />(lightweight)</a></td>
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr onclick="window.location='https://lag.envs.net/';">
    <td><i class="fa fa-arrows-h fa-fw" aria-hidden="true"></i></td> <td><a href="https://lag.envs.net/">latency monitor</a></td>
  </tr>
  <tr onclick="window.location='https://fed.envs.net/';">
    <td><i class="fa fa-refresh fa-fw" aria-hidden="true"></i></td> <td><a href="https://fed.envs.net/">federation tester</a></td>
  </tr>
</table>
</div>

<div class="block">
<pre>
<strong>rules &sol; guidelines</strong>
<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
</pre>
<p></p>
</div>

      </div>

<?php include '../../footer.php'; ?>
