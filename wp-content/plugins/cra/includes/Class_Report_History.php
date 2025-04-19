<?php
namespace CRA;

defined('ABSPATH') || exit;

class Class_Report_History {
    
    public function __construct() {
        add_shortcode('cra_report_history', [$this, 'render_report_history']);
    }

    public function render_report_history() {
        if (!is_user_logged_in()) {
            return '<p class="text-red-500">Please log in to view your reports.</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'credit_reports';

        $reports = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d ORDER BY uploaded_at DESC",
                $user_id
            )
        );

        if (!$reports) {
            return '<p class="text-gray-600">No reports uploaded yet.</p>';
        }

        ob_start();
        ?>
        <div class="p-6 bg-white rounded-xl shadow space-y-4">
            <h2 class="text-2xl font-bold mb-4">Your Uploaded Credit Reports</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border">
                    <thead>
                        <tr class="bg-gray-100 text-sm">
                            <th class="px-4 py-2">Uploaded At</th>
                            <th class="px-4 py-2">Score</th>
                            <th class="px-4 py-2">Accounts</th>
                            <th class="px-4 py-2">Negative</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reports as $report): 
                        $data = json_decode($report->report_data, true);
                    ?>
                        <tr class="border-t text-sm">
                            <td class="px-4 py-2"><?php echo esc_html($report->uploaded_at); ?></td>
                            <td class="px-4 py-2"><?php echo esc_html($data['credit_score'] ?? 'N/A'); ?></td>
                            <td class="px-4 py-2"><?php echo esc_html($data['account_count'] ?? 'N/A'); ?></td>
                            <td class="px-4 py-2"><?php echo esc_html($data['negative_marks'] ?? 'N/A'); ?></td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="<?php echo esc_url(admin_url('admin-post.php?action=cra_download_pdf&report_id=' . $report->id)); ?>"
                                   class="inline-block px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                   Download PDF
                                </a>
                                <a href="?view_report_id=<?php echo $report->id; ?>"
                                   class="inline-block px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                                   View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
