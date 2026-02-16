<?php
$title = "envs.net | Donate";
$desc  = "Support envs.net by donating. Your contribution helps cover hosting costs and future upgrades.";

include 'neoenvs_header.php';

/* ===============================
   Monthly Costs
=================================*/
$monthly_costs = [
	'Server core'    => 65.45,
	'Server ext'     => 17.94,
	'Additional IPs' => 3 * 2.02,
	'Backup'         => 3.84,
	'Domain'         => 24.84 / 12
];

$total_costs = array_sum($monthly_costs);

/* ===============================
   Income JSON
=================================*/
$incomeFile = 'income.json';
$incomeData = file_exists($incomeFile)
	? json_decode(file_get_contents($incomeFile), true) ?? []
	: [];

$current_month_key   = date('Y-m');
$current_month_label = date('F Y');

$donations_total = $incomeData[$current_month_key] ?? 0;

/* ===============================
   Calculations
=================================*/
$balance = $donations_total - $total_costs;

$progress_percent = $total_costs > 0
	? min(100, ($donations_total / $total_costs) * 100)
	: 0;

$progress_color = $balance >= 0 ? '#4caf50' : '#f44336';
$display_width  = max($progress_percent, 2);
?>

<link rel="stylesheet" href="/css/donate.css">

<body id="body">

<nav class="sidenav">
	<a href="/" aria-label="Back to envs.net homepage">
		<img src="https://envs.net/img/envs_logo_200x200.png" class="site-icon" alt="envs.net logo">
	</a>
</nav>

<main class="content">

	<div class="block">
		<h1>Support envs.net</h1>
		<h2>Help keep your project and servers running!</h2>
	</div>

	<p>Your donation helps cover monthly server costs and future upgrades.
	   Thank you for keeping envs.net alive!</p>

	<!-- ================= Donation Methods ================= -->
	<section id="donation-methods">
		<h2>Online Donations</h2>
		<ul class="icon-list">
			<li>
				<a href="https://en.liberapay.com/envs.net" target="_blank">
					<i class="fa-liberapay"></i>Liberapay
				</a>
			</li>
			<li>
				<a href="https://www.patreon.com/envs" target="_blank">
					<i class="fa-patreon"></i>Patreon
				</a>
			</li>
			<li>
				<a href="https://paypal.me/envsk" target="_blank">
					<i class="fa-paypal"></i>PayPal
				</a>
			</li>
		</ul>
	</section>

	<!-- ================= Crypto ================= -->
	<section id="crypto">
		<h2>Crypto Donations</h2>
		<table>
			<tr><td>BTC:</td><td><code>bc1qxtljvxjjcrqt3kn8kl3pnwazny7k34kjxsyy7s</code></td></tr>
			<tr><td>ETH:</td><td><code>0xF481f6a7d9b22B3BE5d40A54C833A1C6eEdcdf69</code></td></tr>
		</table>
	</section>

	<!-- ================= Bank ================= -->
	<section id="bank">
		<h2>Bank Transfer</h2>
		<table>
			<tr><td>Recipient:</td><td>Sven Kinne</td></tr>
			<tr><td>IBAN:</td><td>DE59 8505 0300 1225 5661 06</td></tr>
			<tr><td>BIC:</td><td>OSDDDE81XXX</td></tr>
			<tr><td>Bank:</td><td>Ostsächsische Sparkasse Dresden</td></tr>
		</table>
	</section>

	<!-- ================= Financial Overview ================= -->
	<section id="financial-overview">

		<h2>Monthly Financial Overview</h2>

		<h3>Monthly Costs</h3>
		<ul class="cost-list">
			<?php foreach($monthly_costs as $item => $amount): ?>
				<li>
					<span><?php echo $item; ?></span>
					<span><?php echo number_format($amount,2,',','.'); ?> €</span>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="total-costs">
			<strong>Total Costs:</strong>
			<?php echo number_format($total_costs,2,',','.'); ?> €
		</p>

		<h3>Donations Received (<?php echo $current_month_label; ?>)</h3>
		<p><?php echo number_format($donations_total,2,',','.'); ?> €</p>

		<h3>Balance</h3>
		<p class="balance-text <?php echo $balance>=0?'positive':'negative'; ?>">
			<strong><?php echo number_format($balance,2,',','.'); ?> €</strong>
			(<?php echo $balance>=0?'surplus':'deficit'; ?>)
		</p>

		<div class="progress-bar-container"
			 role="progressbar"
			 aria-valuenow="<?php echo round($progress_percent); ?>"
			 aria-valuemin="0"
			 aria-valuemax="100"
			 aria-label="Progress towards monthly costs">
			<div class="progress-bar-fill"
				 style="background:<?php echo $progress_color; ?>;
						width:<?php echo $display_width; ?>%;">
			</div>
			<span class="progress-bar-text"><?php echo round($progress_percent); ?>%</span>
		</div>

		<p class="progress-label">
			<?php echo $progress_percent>=100 ? 'Goal reached!' : 'Progress towards monthly costs'; ?>
		</p>

	</section>

	<!-- ================= Income Chart ================= -->
	<section id="income-chart">
		<h2>Monthly Income Chart</h2>
		<div class="chart-wrapper">
			<canvas id="incomeChart"></canvas>
		</div>

		<script src="/js/chart.umd.min.js"></script>
		<script>
			const ctx = document.getElementById('incomeChart');

			new Chart(ctx, {
				type: 'line',
				data: {
					labels: <?php echo json_encode(array_keys($incomeData)); ?>,
					datasets: [{
						label: 'Monthly Income (€)',
						data: <?php echo json_encode(array_values($incomeData)); ?>,
						tension: 0.3,
						pointRadius: 4
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					scales: { y: { beginAtZero: true } }
				}
			});
		</script>
	</section>

	<p class="thanks"><strong>Thank you for supporting envs.net!</strong></p>

</main>

<?php include 'neoenvs_footer.php'; ?>
