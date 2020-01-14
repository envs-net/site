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
<pre>
<h1><em>chat</em></h1>

envs.net has two chat options. we can be reached via irc and matrix.

<strong>rules / guidelines</strong>
<em>please see the <a href="/chat/etiquette" target="_blank">etiquette guide</a>.</em>
</pre>
</div>

<pre>

<h3>&#35; more informations about</h3>
&nbsp;&nbsp;<strong><a href="/chat/matrix">&gt; matrix chat</a></strong>
&nbsp;&nbsp;<strong><a href="/chat/irc">&gt; irc chat</a></strong>
</pre>

      </div>

      <div id="sidebar">

<div class="block">
<pre>
<h4>&#35; matrix</h4>
channel
<code>#envs:envs.net</code>

<table>
  <tr onclick="window.location='https://matrix.envs.net/';">
    <td width="25px"><i class="fa fa-commenting-o fa-fw" aria-hidden="true"></i></td> <td><a href="https://matrix.envs.net/">riot-web client</a></td>
  </tr>
</table></pre>
</div>

<div class="block">
<pre>
<h4>&#35; irc</h4>
channel <strong>#envs</strong> on
<code>irc.tilde.chat:6697</code>

<table>
  <tr onclick="window.location='https://webirc.envs.net/';">
    <td width="25px"><i class="fa fa-commenting-o fa-fw" aria-hidden="true"></i></td> <td><a href="https://webirc.envs.net/">webirc-client</a></td>
  </tr>
  <tr onclick="window.location='https://znc.envs.net/';">
    <td><i class="fa fa-compress fa-fw" aria-hidden="true"></i></td> <td><a href="https://znc.envs.net/">znc interface</a></td>
  </tr>
</table></pre>
</div>

      </div>

<?php include '../footer.php'; ?>
