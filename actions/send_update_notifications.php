<?php
/**
 * Email Notification System for School Updates
 * 
 * When a school posts an update about their progress, this script sends
 * email notifications to all donors who have contributed to that school.
 * It's a great way to keep donors engaged and show them the impact of
 * their contributions.
 * 
 * In production, you'd want to use a proper email service like SendGrid,
 * Mailgun, or AWS SES instead of PHP's built-in mail() function.
 */

require_once __DIR__ . '/../settings/db_class.php';

/**
 * Send email notifications to all donors of a school when an update is posted
 * 
 * @param int $update_id The ID of the school update to notify about
 * @return int|false Number of emails sent, or false if update not found
 */
function sendUpdateNotifications($update_id) {
    $db = new db_connection();
    
    // First, grab the update details so we know what we're notifying about
    $update_query = "
        SELECT su.*, s.school_name, s.image_url as school_image
        FROM school_updates su
        JOIN schools s ON su.school_id = s.school_id
        WHERE su.update_id = ?
    ";
    $update = $db->db_fetch_one($update_query, [$update_id]);
    
    if (!$update) {
        return false;
    }
    
    // Find everyone who has donated to this school - they're the ones who care!
    $donors_query = "
        SELECT DISTINCT u.user_id, u.user_name, u.user_email
        FROM users u
        JOIN donations d ON u.user_id = d.user_id
        WHERE d.school_id = ? 
        AND d.payment_status = 'completed'
        AND u.user_email IS NOT NULL
    ";
    $donors = $db->db_fetch_all($donors_query, [$update['school_id']]);
    
    // No donors? Nothing to do here
    if (empty($donors)) {
        return true;
    }
    
    // Set up the email content
    $subject = "New Update from " . $update['school_name'];
    $update_url = "https://yourdomain.com/school_updates.php?id=" . $update['school_id'];
    
    $sent_count = 0;
    
    // Send personalized emails to each donor
    foreach ($donors as $donor) {
        $message = getEmailTemplate($donor, $update, $update_url);
        
        // Set up email headers for HTML content
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: GiveToGrow <noreply@givetogrow.org>\r\n";
        
        if (mail($donor['user_email'], $subject, $message, $headers)) {
            $sent_count++;
        }
    }
    
    return $sent_count;
}

/**
 * Generate a nice HTML email template for update notifications
 * 
 * Creates a branded, mobile-friendly email that looks professional
 * and encourages donors to check out the full update.
 * 
 * @param array $donor The donor's info (user_name, user_email)
 * @param array $update The update details from the database
 * @param string $update_url Link to view the full update
 * @return string The complete HTML email content
 */
function getEmailTemplate($donor, $update, $update_url) {
    // Escape everything to prevent XSS in emails
    $donor_name = htmlspecialchars($donor['user_name']);
    $school_name = htmlspecialchars($update['school_name']);
    $update_title = htmlspecialchars($update['update_title']);
    $update_description = htmlspecialchars(substr($update['update_description'], 0, 200));
    
    // Human-readable labels for update types
    $update_type_text = [
        'general' => 'General Update',
        'milestone' => 'Milestone Achieved!',
        'progress' => 'Progress Report',
        'completion' => 'Project Completed!',
        'thank_you' => 'Thank You Message'
    ];
    $type_label = $update_type_text[$update['update_type']] ?? 'Update';
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7f7f7; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background-color: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #A4B8A4; padding: 30px 20px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 28px; }
        .content { padding: 30px; }
        .badge { display: inline-block; background-color: #f0f0f0; color: #666; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-bottom: 15px; }
        .update-title { font-size: 22px; font-weight: bold; color: #131514; margin-bottom: 15px; }
        .update-text { color: #666; line-height: 1.6; margin-bottom: 25px; }
        .cta-button { display: inline-block; background-color: #A4B8A4; color: white; padding: 14px 32px; text-decoration: none; border-radius: 25px; font-weight: bold; margin-top: 10px; }
        .footer { background-color: #f7f7f7; padding: 20px; text-align: center; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GiveToGrow</h1>
        </div>
        <div class="content">
            <p style="color: #666;">Hello {$donor_name},</p>
            <p style="color: #666; margin-bottom: 25px;">
                Great news! <strong>{$school_name}</strong>, a school you've supported, has posted a new update.
            </p>
            
            <span class="badge">{$type_label}</span>
            <h2 class="update-title">{$update_title}</h2>
            <p class="update-text">{$update_description}...</p>
            
            <a href="{$update_url}" class="cta-button">View Full Update & Impact</a>
            
            <p style="color: #999; font-size: 13px; margin-top: 30px;">
                Thank you for making a difference in students' lives. Your generosity is changing the world, one school at a time.
            </p>
        </div>
        <div class="footer">
            <p>&copy; 2024 GiveToGrow. All rights reserved.</p>
            <p>You're receiving this because you donated to {$school_name}.</p>
        </div>
    </div>
</body>
</html>
HTML;
}

?>
