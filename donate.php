<?php
	$title = "envs.net | Donate";
	$desc = "envs.net | Donate";

	include 'neoenvs_header.php';

	$monthly_costs = [
			'Server core' => 65.45,
			'Server ext' => 17.94,
			'Additional IPs' => 3*2.02,
			'Backup' => 3.84,
			'Domain' => 24.84/12
	];

	$donations_total = 204.69;

	$total_costs = array_sum($monthly_costs);
	$balance = $donations_total - $total_costs;

	$progress_percent = min(100, ($donations_total / $total_costs) * 100);
	$progress_color = $balance >= 0 ? '#4caf50' : '#f44336'; // green if surplus, red if deficit
?>

<body id="body">

<!-- Back button -->
<nav class="sidenav">
	<a href="/" aria-label="Back to envs.net homepage">
		<img src="https://envs.net/img/envs_logo_200x200.png" class="site-icon" title="Back to envs.net homepage">
	</a>
</nav>

<main class="content">
	<div class="block">
		<h1>Support envs.net</h1>
		<h2>Help keep your project and servers running!</h2>
	</div>

	<p>Your donation helps cover monthly server costs and future upgrades. Thank you for keeping envs.net alive!</p>

	<!-- Donation Buttons -->
	<section id="donation-methods">
		<h2>Online Donations</h2>
		<ul class="icon-list">
			<li><a href="https://en.liberapay.com/envs.net" target="_blank" aria-label="Support envs.net on Liberapay"><i class="fa-liberapay" aria-hidden="true"></i>Liberapay</a></li>
			<li><a href="https://www.patreon.com/envs" target="_blank" aria-label="Support envs.net on Patreon"><i class="fa-patreon" aria-hidden="true"></i>Patreon</a></li>
			<li><a href="https://paypal.me/envsk" target="_blank" aria-label="Support envs.net on PayPal"><i class="fa-paypal" aria-hidden="true"></i>PayPal</a></li>
		</ul>
	</section>

	<!-- Crypto Section -->
	<section id="crypto">
		<h2>Crypto Donations</h2>
		<table>
			<tr><th></th> <th></th></tr>
			<tr><td>BTC:</td> <td><code>bc1qxtljvxjjcrqt3kn8kl3pnwazny7k34kjxsyy7s</code></td></tr>
			<tr><td>ETH:</td> <td><code>0xF481f6a7d9b22B3BE5d40A54C833A1C6eEdcdf69</code></td></tr>
		</table>
	</section>

	<!-- Bank Section -->
	<section id="bank">
		<h2>old but gold - Bank2Bank</h2>
		<table>
			<tr><td>Recipient:</td> <td>Sven Kinne</td></tr>
			<tr><td>IBAN:</td> <td>DE59 8505 0300 1225 5661 06</td></tr>
			<tr><td>BIC:</td> <td>OSDDDE81XXX</td></tr>
			<tr><td>Bank:</td> <td>Ostsächsische Sparkasse Dresden</td></tr>
		</table>
	</section>

	<!-- Financial Overview -->
	<section id="financial-overview">
		<h2>Monthly Financial Overview</h2>

		<h3>Monthly Costs</h3>
		<ul>
			<?php foreach($monthly_costs as $item => $amount): ?>
				<li><?php echo $item ?>: <?php echo number_format($amount,2); ?> €</li>
			<?php endforeach; ?>
		</ul>
		<p><strong>Total Costs:</strong> <?php echo number_format($total_costs,2); ?> €</p>

		<h3>Donations Received</h3>
		<p><?php echo number_format($donations_total,2); ?> € (01.2026)</p>

		<h3>Balance</h3>
		<p style="color:<?php echo $balance>=0?'#4caf50':'#f44336'; ?>"><strong><?php echo number_format($balance,2); ?> €</strong> (<?php echo $balance>=0?'surplus':'deficit'; ?>)</p>

		<!-- Progress Bar -->
		<div style="border:1px solid #ccc; width:100%; max-width:400px; height:30px; border-radius:5px; overflow:hidden; position:relative; margin-top:10px;">
			<div style="background:<?php echo $progress_color; ?>; width:<?php echo $progress_percent; ?>%; height:100%; transition: width 0.5s;"></div>
			<span style="position:absolute; width:100%; text-align:center; line-height:30px; font-weight:bold; color:#fff;"><?php echo round($progress_percent); ?>%</span>
		</div>
		<p><?php echo $progress_percent>=100 ? 'Goal reached!' : 'Progress towards monthly costs' ?></p>
	</section>

	<p><strong>Thank you for supporting envs.net!</strong></p>
</main>

<?php include 'neoenvs_footer.php'; ?>
