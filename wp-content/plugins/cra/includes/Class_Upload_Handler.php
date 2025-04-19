<?php

namespace CRA;

defined('ABSPATH') || exit;

class Class_Upload_Handler
{

    public function __construct()
    {
        add_shortcode('cra_upload_form', [$this, 'render_upload_form']);
        add_action('init', [$this, 'handle_file_upload']);
    }

    // Render the file upload form
    public function render_upload_form()
    {
        ob_start(); ?>

        <div class="container py-6">
            <ul class="nav nav-tabs mb-3" id="uploadTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                        Upload PDF/CSV
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab">
                        Manual Entry
                    </button>
                </li>
            </ul>

            <div class="tab-content bg-white p-4 rounded shadow" id="uploadTabsContent">
                <!-- File Upload Tab -->
                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                    <form method="post" enctype="multipart/form-data" action="">
                        <input type="hidden" name="action" value="cra_upload_report">
                        <?php wp_nonce_field('cra_upload_report'); ?>

                        <div class="mb-3">
                            <label class="form-label">Select PDF/CSV File</label>
                            <input type="file" name="credit_file" accept=".pdf,.csv,.json" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>

                <!-- Manual Entry Tab -->
                <div class="tab-pane fade" id="manual" role="tabpanel">
                    <form method="post"  action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="cra_manual_entry">
                        <?php wp_nonce_field('cra_manual_entry'); ?>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Credit Score</label>
                                <input type="number" name="credit_score" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Count</label>
                                <input type="number" name="account_count" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Negative Marks</label>
                                <input type="number" name="negative_marks" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Credit Utilization (%)</label>
                                <input type="number" step="0.1" name="utilization" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Debt-to-Income Ratio (%)</label>
                                <input type="number" step="0.1" name="dti" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" rows="3" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">Save Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<?php
        return ob_get_clean();
    }


    // Handle the file upload and save to the server
    public function handle_file_upload()
    {
        if (isset($_FILES['cra_report'])) {
            $file = $_FILES['cra_report'];

            // Validate file type (only PDF, CSV, JSON)
            $allowed_types = ['application/pdf', 'text/csv', 'application/json'];
            if (!in_array($file['type'], $allowed_types)) {
                wp_die('Invalid file type. Please upload a PDF, CSV, or JSON file.');
            }

            // Check for errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                wp_die('File upload failed.');
            }

            // Move the uploaded file to a secure location
            $upload_dir = wp_upload_dir()['basedir'] . '/credit_reports/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_path = $upload_dir . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Save the file information in the database
                $this->save_uploaded_report($file_path);
                echo 'File uploaded successfully!';
            } else {
                wp_die('Failed to save the file.');
            }
        }
    }

// Handle manual entry of credit report data
public static function handle_manual_entry() {
    error_log('Manual entry handler triggered');

    check_admin_referer('cra_manual_entry');

    $user_id = get_current_user_id();
    if (!$user_id) {
        error_log('User not logged in');
        wp_die('You must be logged in.');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'credit_reports';

    $data = [
        'credit_score'   => sanitize_text_field($_POST['credit_score']),
        'account_count'  => sanitize_text_field($_POST['account_count']),
        'negative_marks' => sanitize_text_field($_POST['negative_marks']),
        'utilization'    => sanitize_text_field($_POST['utilization']),
        'dti'            => sanitize_text_field($_POST['dti']),
        'notes'          => sanitize_textarea_field($_POST['notes']),
    ];

    $result = $wpdb->insert($table, [
        'user_id'      => $user_id,
        'report_data'  => json_encode($data),
        'uploaded_at'  => current_time('mysql'),
    ]);

    if ($result === false) {
        error_log('DB insert failed: ' . $wpdb->last_error);
    } else {
        error_log('Manual report saved successfully!');
    }

    wp_redirect(home_url('/your-dashboard-or-history-page/'));
    exit;
}

    

    // Save file information in the database
    private function save_uploaded_report($file_path)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'credit_reports';
        $user_id = get_current_user_id();

        $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'file_name' => basename($file_path),
                'file_type' => mime_content_type($file_path),
                'report_data' => file_get_contents($file_path),
            ]
        );
    }
}
