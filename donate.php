<?php
$title = "envs.net | Donate";
$desc = "Support envs.net by donating. Your contribution helps cover hosting costs and future upgrades.";

include 'neoenvs_header.php';

$monthly_costs = [
    'Server core' => 65.45,
    'Server ext' => 17.94,
    'Additional IPs' => 3*2.02,
    'Backup' => 3.84,
    'Domain' => 24.84/12
];

$total_costs = array_sum($monthly_costs);

$incomeFile = 'income.json';
$incomeData = file_exists($incomeFile) ? json_decode(file_get_contents($incomeFile), true) ?? [] : [];

$current_month = date('Y-m');
$donations_total = $incomeData[$current_month] ?? 0;

$balance = $donations_total - $total_costs;
$progress_percent = min(100, ($donations_total / $total_costs) * 100);
$progress_color = $balance >= 0 ? '#4caf50' : '#f44336';
$min_width = 2;
$display_width = max($progress_percent, $min_width);

// Days left in month
$days_in_month = date('t');
$today = date('j');
$days_left = $days_in_month - $today;
$days_percent = ($today / $days_in_month) * 100;
$days_color = '#2196f3';
?>

<!-- CSS einbinden -->
<link rel="stylesheet" href="/css/donate.css">

<body id="body">

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

    <section id="donation-methods">
        <h2>Online Donations</h2>
        <ul class="icon-list">
            <li><a href="https://en.liberapay.com/envs.net" target="_blank" aria-label="Support envs.net on Liberapay"><i class="fa-liberapay" aria-hidden="true"></i> Liberapay</a></li>
            <li><a href="https://www.patreon.com/envs" target="_blank" aria-label="Support envs.net on Patreon"><i class="fa-patreon" aria-hidden="true"></i> Patreon</a></li>
            <li><a href="https://paypal.me/envsk" target="_blank" aria-label="Support envs.net on PayPal"><i class="fa-paypal" aria-hidden="true"></i> PayPal</a></li>
        </ul>
    </section>

    <section id="crypto">
        <h2>Crypto Donations</h2>
        <table>
            <tr><td>BTC:</td> <td><code>bc1qxtljvxjjcrqt3kn8kl3pnwazny7k34kjxsyy7s</code></td></tr>
            <tr><td>ETH:</td> <td><code>0xF481f6a7d9b22B3BE5d40A54C833A1C6eEdcdf69</code></td></tr>
        </table>
    </section>

    <section id="bank">
        <h2>Bank Transfer</h2>
        <table>
            <tr><th scope="col">Field</th> <th scope="col">Details</th></tr>
            <tr><td>Recipient:</td> <td>Sven Kinne</td></tr>
            <tr><td>IBAN:</td> <td>DE59 8505 0300 1225 5661 06</td></tr>
            <tr><td>BIC:</td> <td>OSDDDE81XXX</td></tr>
            <tr><td>Bank:</td> <td>Ostsächsische Sparkasse Dresden</td></tr>
        </table>
    </section>

    <section id="financial-overview">
        <h2>Monthly Financial Overview</h2>

        <h3>Monthly Costs</h3>
        <ul>
            <?php foreach($monthly_costs as $item => $amount): ?>
                <li><?php echo $item ?>: <?php echo number_format($amount,2,',','.'); ?> €</li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Total Costs:</strong> <?php echo number_format($total_costs,2,',','.'); ?> €</p>

        <h3>Donations Received (This Month)</h3>
        <p><?php echo number_format($donations_total,2,',','.'); ?> €</p>

        <h3>Balance</h3>
        <div class="progress-bar-container" role="progressbar" aria-valuenow="<?php echo round($progress_percent); ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Progress towards monthly costs">
            <div class="progress-bar-fill" style="background:<?php echo $progress_color; ?>; width:<?php echo $display_width; ?>%;"></div>
            <span class="progress-bar-text"><?php echo round($progress_percent); ?>%</span>
        </div>
        <p><?php echo $progress_percent>=100 ? 'Goal reached!' : 'Progress towards monthly costs' ?></p>

        <h3>Days Left This Month</h3>
        <div class="progress-bar-container small" role="progressbar" aria-valuenow="<?php echo $today; ?>" aria-valuemin="0" aria-valuemax="<?php echo $days_in_month; ?>" aria-label="Days passed this month">
            <div class="progress-bar-fill" style="background:<?php echo $days_color; ?>; width:<?php echo $days_percent; ?>%;"></div>
            <span class="progress-bar-text"><?php echo $days_left; ?> day<?php echo $days_left != 1 ? 's' : ''; ?> left</span>
        </div>
    </section>

    <section id="income-chart">
        <h2>Monthly Income Chart</h2>
        <div style="width:100%; max-width:600px; height:300px;">
            <canvas id="incomeChart"></canvas>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            const ctx = document.getElementById('incomeChart').getContext('2d');
            const labels = <?php echo json_encode(array_keys($incomeData)); ?>;
            const data = <?php echo json_encode(array_values($incomeData)); ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Monthly Income (€)',
                        data: data,
                        fill: false,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.3,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true }, tooltip: { enabled: true } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 50 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { color: 'rgba(0,0,0,0.02)' } }
                    }
                }
            });
        </script>
    </section>

    <p><strong>Thank you for supporting envs.net!</strong></p>
</main>

<!-- Smooth progress bar animation -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const progressBars = document.querySelectorAll('.progress-bar-fill');
    progressBars.forEach(bar => {
        const finalWidth = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = finalWidth; }, 100);
    });
});
</script>

<?php include 'neoenvs_footer.php'; ?>
