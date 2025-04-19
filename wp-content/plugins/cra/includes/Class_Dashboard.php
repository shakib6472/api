<?php
namespace CRA;

defined('ABSPATH') || exit;

class Class_Dashboard {

    public function __construct() {
        add_shortcode('cra_dashboard', [$this, 'render_dashboard']);
    }

    public function render_dashboard() {
        if (!is_user_logged_in()) {
            return '<p class="text-red-500">Please log in to view your credit report analysis.</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'credit_reports';

        $report = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d ORDER BY uploaded_at DESC LIMIT 1",
                $user_id
            )
        );

        if (!$report) {
            return '<p class="text-gray-600">No credit report uploaded yet.</p>';
        }

        $data = json_decode($report->report_data, true);

        ob_start();
        ?>
        <div class="p-6 bg-white rounded-xl shadow space-y-4">
            <h2 class="text-2xl font-bold mb-4">Credit Report Overview</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-500">Credit Score</p>
                    <p class="text-3xl font-bold text-blue-700"><?php echo esc_html($data['credit_score'] ?? 'N/A'); ?></p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-gray-500">Accounts</p>
                    <p class="text-3xl font-bold text-green-700"><?php echo esc_html($data['account_count'] ?? 'N/A'); ?></p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <p class="text-sm text-gray-500">Negative Marks</p>
                    <p class="text-3xl font-bold text-red-700"><?php echo esc_html($data['negative_marks'] ?? 'N/A'); ?></p>
                </div>
            </div>

            <div class="mt-4">
                <a href="<?php echo esc_url(admin_url('admin-post.php?action=cra_download_pdf&report_id=' . $report->id)); ?>" 
                   class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Download as PDF
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function generate_pdf() {
        if (!is_user_logged_in() || !isset($_GET['report_id'])) {
            wp_die('Access denied.');
        }
    
        global $wpdb;
        $user_id = get_current_user_id();
        $report_id = intval($_GET['report_id']);
        $table_name = $wpdb->prefix . 'credit_reports';
    
        $report = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d AND user_id = %d", $report_id, $user_id)
        );
    
        if (!$report) {
            wp_die('Report not found.');
        }
    
        $data = json_decode($report->report_data, true);
    
        // Use Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml("
            <h1>Credit Report Summary</h1>
            <p><strong>Credit Score:</strong> {$data['credit_score']}</p>
            <p><strong>Account Count:</strong> {$data['account_count']}</p>
            <p><strong>Negative Marks:</strong> {$data['negative_marks']}</p>
        ");
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("credit-report.pdf", ["Attachment" => 1]);
        exit;
    }

    
    
}
