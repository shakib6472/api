<?php

namespace CRA;

use WP_User;

class Class_Admin_Dashboard
{

    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    public static function enqueue_admin_assets()
    {
        wp_enqueue_style('cra-admin-style', plugin_dir_url(__FILE__) . '../assets/css/style.css');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    }

    public static function register_menu()
    {
        add_menu_page(
            'CRA Admin Dashboard',
            'CRA Admin',
            'manage_options',
            'cra_admin_dashboard',
            [__CLASS__, 'render_users_list'],
            'dashicons-chart-area',
            30
        );
    }

    public static function render_users_list()
    {
        if (isset($_GET['user_id'])) {
            self::render_user_detail_view(intval($_GET['user_id']));
            return;
        }

        global $wpdb;

        $user_reports = $wpdb->get_results("SELECT r.user_id, MAX(r.uploaded_at) as last_upload, r.report_data FROM {$wpdb->prefix}credit_reports r GROUP BY r.user_id ORDER BY last_upload DESC");

        echo '<div class="wrap">';
        echo '<h1 class="text-2xl font-bold mb-4">Users with Uploaded Reports</h1>';

        if (empty($user_reports)) {
            echo '<p class="text-gray-600">No reports uploaded yet.</p></div>';
            return;
        }

        echo '<div class="overflow-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm uppercase text-gray-600 tracking-wider">
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Last Upload</th>
                        <th class="px-6 py-3">Last Credit Score</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">';

        foreach ($user_reports as $report) {
            $user = get_userdata($report->user_id);
            $data = json_decode($report->report_data, true);
            $credit_score = $data['credit_score'] ?? 'N/A';

            echo '<tr class="border-t">
                    <td class="px-6 py-4 font-medium">' . esc_html($user->display_name) . '</td>
                    <td class="px-6 py-4">' . esc_html($user->user_email) . '</td>
                    <td class="px-6 py-4">' . esc_html(date('M d, Y', strtotime($report->last_upload))) . '</td>
                    <td class="px-6 py-4">' . esc_html($credit_score) . '</td>
                    <td class="px-6 py-4">
                        <a href="' . esc_url(admin_url('admin.php?page=cra_admin_dashboard&user_id=' . $user->ID)) . '" class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>';
        }

        echo '</tbody></table></div></div>';
    }

    public static function render_user_detail_view($user_id)
    {
        global $wpdb;
        $user = get_userdata($user_id);

        if (!$user) {
            echo '<p class="text-red-500">Invalid user ID</p>';
            return;
        }

        $reports = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}credit_reports WHERE user_id = %d ORDER BY uploaded_at ASC", $user_id));

        if (empty($reports)) {
            echo '<p class="text-gray-600">No reports found for this user.</p>';
            return;
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

        echo '<div class="wrap">';
        echo '<h2 class="text-xl font-bold mb-2">User: ' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</h2>';

        echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">';
        echo '<div class="bg-white p-4 rounded shadow">
                <h3 class="text-sm font-medium text-gray-500">Credit Score</h3>
                <p class="text-2xl font-bold">' . (is_numeric($scores[count($scores) - 1]) ? esc_html($scores[count($scores) - 1]) : 'N/A') . '</p>
            </div>';
        echo '<div class="bg-white p-4 rounded shadow">
                <h3 class="text-sm font-medium text-gray-500">Credit Utilization</h3>
                <p class="text-2xl font-bold">' . (is_numeric($utilizations[count($utilizations) - 1]) ? esc_html($utilizations[count($utilizations) - 1]) . '%' : 'N/A') . '</p>
            </div>';
        echo '<div class="bg-white p-4 rounded shadow">
                <h3 class="text-sm font-medium text-gray-500">Debt-to-Income (DTI)</h3>
                <p class="text-2xl font-bold">' . (is_numeric($dti[count($dti) - 1]) ? esc_html($dti[count($dti) - 1]) . '%' : 'N/A') . '</p>
            </div>';
        echo '</div>';


        echo '<div class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="chart-container">
                        <canvas id="scoreChart" height="120"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="utilizationChart" height="120"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="dtiChart" height="120"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="negativesChart" height="120"></canvas>
                    </div>
                </div>
            </div>';

        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const labels = ' . json_encode($labels) . ';
                const scores = ' . json_encode($scores) . ';
                const utilizations = ' . json_encode($utilizations) . ';
                const dti = ' . json_encode($dti) . ';
                const negatives = ' . json_encode($negatives) . ';

                new Chart(document.getElementById("scoreChart"), {
                    type: "line",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Credit Score",
                            data: scores,
                            borderColor: "#4F46E5",
                            fill: false
                        }]
                    }
                });

                new Chart(document.getElementById("utilizationChart"), {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Credit Utilization (%)",
                            data: utilizations,
                            backgroundColor: "#10B981"
                        }]
                    }
                });

                new Chart(document.getElementById("dtiChart"), {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Debt-to-Income Ratio (%)",
                            data: dti,
                            backgroundColor: "#F59E0B"
                        }]
                    }
                });

                new Chart(document.getElementById("negativesChart"), {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Negative Marks",
                            data: negatives,
                            backgroundColor: "#EF4444"
                        }]
                    }
                });
            });
        </script>';

        echo '<div class="mt-4">
            <a href="' . admin_url('admin.php?page=cra_admin_dashboard') . '" class="text-blue-600 hover:underline">← Back to user list</a>
        </div>';
        echo '</div>';
    }
}

Class_Admin_Dashboard::init();
