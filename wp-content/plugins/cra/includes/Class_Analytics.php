<?php

namespace CRA;

class Class_Analytics
{
    public static function init()
    {
        add_shortcode('cra_analytics', [self::class, 'render']);
    }

    public static function render()
    {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to view this page.</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'credit_reports';

        $reports = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY uploaded_at ASC", $user_id)
        );

        if (empty($reports)) {
            return '<p>No data available for analytics.</p>';
        }

        $labels = [];
        $scores = [];
        $utilizations = [];
        $dti = [];
        $negatives = [];

        foreach ($reports as $report) {
            $data = json_decode($report->report_data, true);
            $labels[] = date('M j', strtotime($report->uploaded_at));
            $scores[] = floatval($data['credit_score']);
            $utilizations[] = floatval($data['utilization']);
            $dti[] = floatval($data['dti']);
            $negatives[] = floatval($data['negative_marks']);
        }

        ob_start();
?>
        <div class="p-6 bg-white rounded-lg shadow-md space-y-10">
            <h2 class="text-2xl font-bold mb-4">Analytics Overview</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <canvas id="scoreChart"></canvas>
                </div>
                <div>
                    <canvas id="utilizationChart"></canvas>
                </div>
                <div>
                    <canvas id="dtiChart"></canvas>
                </div>
                <div>
                    <canvas id="negativesChart"></canvas>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const labels = <?php echo json_encode($labels); ?>;
                const scores = <?php echo json_encode($scores); ?>;
                const utilizations = <?php echo json_encode($utilizations); ?>;
                const dti = <?php echo json_encode($dti); ?>;
                const negatives = <?php echo json_encode($negatives); ?>;

                new Chart(document.getElementById('scoreChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Credit Score',
                            data: scores,
                            borderColor: '#4F46E5',
                            fill: false
                        }]
                    }
                });

                new Chart(document.getElementById('utilizationChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Credit Utilization (%)',
                            data: utilizations,
                            backgroundColor: '#10B981'
                        }]
                    }
                });

                new Chart(document.getElementById('dtiChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Debt-to-Income Ratio (%)',
                            data: dti,
                            backgroundColor: '#F59E0B'
                        }]
                    }
                });

                new Chart(document.getElementById('negativesChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Negative Marks',
                            data: negatives,
                            backgroundColor: '#EF4444'
                        }]
                    }
                });
            });
        </script>
<?php
        return ob_get_clean();
    }
}
