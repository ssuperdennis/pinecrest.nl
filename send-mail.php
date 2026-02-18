<?php
/**
 * PineCrest Contact Form Handler
 * Processes contact form submissions and sends email notifications
 */

// Configuration
$recipientEmail = 'info@pinecrest.nl'; // Change this to your actual email
$companyName = 'PineCrest';

// Set headers
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Sanitize and validate input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Get and validate form data
// Honeypot spam check - if the hidden field is filled, it's a bot
if (!empty($_POST['website'])) {
    // Silently fail for bots
    echo json_encode(['success' => true, 'message' => 'Thank you for your message.']);
    exit;
}

$name = cleanInput($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$company = cleanInput($_POST['company'] ?? '');
$phone = cleanInput($_POST['phone'] ?? '');
$service = cleanInput($_POST['service'] ?? '');
$message = cleanInput($_POST['message'] ?? '');
$privacy = isset($_POST['privacy']) ? true : false;

// Validate required fields
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($service)) {
    $errors[] = 'Please select a service';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

if (!$privacy) {
    $errors[] = 'You must agree to the processing of your personal data';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Service names mapping
$serviceNames = [
    'project-recovery' => 'Project Recovery',
    'pm-transformation' => 'PM Department Transformation',
    'ccpm-implementation' => 'Critical Chain Implementation',
    'team-coaching' => 'Team Coaching',
    'project-audit' => 'Project Audit',
    'interim-pm' => 'Interim PM Leadership',
    'other' => 'Other'
];

$serviceName = $serviceNames[$service] ?? 'Other';

// Prepare email
$to = $recipientEmail;
$subject = "New Contact Form Submission from $name - $serviceName";

// Email body
$emailBody = "
You have received a new inquiry via the PineCrest website.

CONTACT DETAILS
===============
Name: $name
Email: $email
" . ($company ? "Company: $company\n" : "") . "
" . ($phone ? "Phone: $phone\n" : "") . "
Service Interest: $serviceName

MESSAGE
=======
$message
";

// Email body for auto-reply
$autoReplyBody = "
Dear $name,

Thank you for your inquiry with PineCrest.

We have received your message regarding our $serviceName service. One of our consultants will review your inquiry and get back to you within 24 hours.

If you need immediate assistance, please don't hesitate to contact us directly.

Best regards,
The PineCrest Team

---
PineCrest
Critical Chain Project Management Consultancy
Wenum Wiesel, The Netherlands
info@pinecrest.nl
www.pinecrest.nl
";

// Headers
$headers = [
    'From: ' . $companyName . ' <noreply@pinecrest.nl>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

$autoReplyHeaders = [
    'From: ' . $companyName . ' <noreply@pinecrest.nl>',
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

// Send email to company
$mailSent = mail($to, $subject, wordwrap($emailBody, 70), implode("\r\n", $headers));

// Send auto-reply to customer
$autoReplySent = mail($email, "Thank you for your inquiry - PineCrest", wordwrap($autoReplyBody, 70), implode("\r\n", $autoReplyHeaders));

if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message. We will get back to you within 24 hours.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to send email. Please try again later or contact us directly at info@pinecrest.nl'
    ]);
}
