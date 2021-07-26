<?php
  $title = "envs.net | chat";
  $desc = "envs.net | chat";

include '../header.php';
?>

  <body id="body" class="dark-mode">
    <div class="clear" style="min-width: 940px;">

      <div class="button_back">
        <pre class="clean"><strong><a href="/">&lt; back</a></strong></pre>
      </div>

      <div id="main">
<div class="block">
<h1><em>chat</em></h1>
<pre>
envs.net has two chat options. we can be reached via matrix and irc.

<strong>rules / guidelines</strong>
<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
</pre>
<p></p>
</div>

<h2>&#35; more informations about our</h2>
<pre>
&nbsp;&nbsp;<strong><a href="/chat/matrix">&gt; matrix chat</a></strong>
&nbsp;&nbsp;<strong><a href="/chat/irc">&gt; irc chat</a></strong>
</pre>

      </div>

      <div id="sidebar">

<div class="block">
<p></p>
<h3>&#35; matrix</h3>
<pre>
channel
<code>#envs:envs.net</code>
</pre>
<br />
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='https://element.envs.net/';">
    <td><i class="fa fa-matrix-org fa-fw" aria-hidden="true"></i></td> <td><a href="https://element.envs.net/">element-web client</a></td>
  </tr>
  <tr onclick="window.location='https://hydrogen.envs.net/';">
    <td><i class="fa fa-matrix-org fa-fw" aria-hidden="true"></i></td> <td><a href="https://hydrogen.envs.net/">hydrogen-web client (lightweight)</a></td>
  </tr>
</table>
<p></p>
</div>

<div class="block">
<p></p>
<h3>&#35; irc</h3>
<pre>
channel <strong>#envs</strong> on
<code>irc.tilde.chat:6697</code>
</pre>
<br />
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='https://webirc.envs.net/';">
    <td><i class="fa fa-commenting-o fa-fw" aria-hidden="true"></i></td> <td><a href="https://webirc.envs.net/">webirc-client</a></td>
  </tr>
  <tr onclick="window.location='https://znc.envs.net/';">
    <td><i class="fa fa-compress fa-fw" aria-hidden="true"></i></td> <td><a href="https://znc.envs.net/">znc interface</a></td>
  </tr>
</table>
<p></p>
</div>

      </div>

<?php include '../footer.php'; ?>
