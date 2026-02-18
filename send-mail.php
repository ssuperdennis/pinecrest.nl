<?php
/**
 * PineCrest Contact Form Handler
 * Processes contact form submissions and sends email notifications
 */

// Start session for CSRF protection
session_start();

// Configuration
$recipientEmail = 'info@pinecrest.nl'; // Change this to your actual email
$companyName = 'PineCrest';

// Rate limiting configuration
$rateLimitFile = sys_get_temp_dir() . '/pinecrest_contact_' . md5($_SERVER['REMOTE_ADDR']);
$rateLimitPeriod = 60; // seconds
$rateLimitMax = 3; // max submissions per period

// Set headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Security functions

/**
 * Sanitize input - removes dangerous characters but preserves legitimate content
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Sanitize email for use in headers (prevents header injection)
 */
function sanitizeEmailForHeader($email) {
    // Remove newlines, tabs, and other dangerous characters
    $email = preg_replace('/[\r\n\t\f\v]/', '', $email);
    // Only allow valid email characters
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return $email;
}

/**
 * Check rate limiting to prevent spam flooding
 */
function checkRateLimit($file, $period, $max) {
    $now = time();
    $submissions = [];

    if (file_exists($file)) {
        $submissions = json_decode(file_get_contents($file), true) ?: [];
        // Remove old entries
        $submissions = array_filter($submissions, fn($t) => ($now - $t) < $period);
    }

    if (count($submissions) >= $max) {
        return false;
    }

    $submissions[] = $now;
    file_put_contents($file, json_encode($submissions));
    return true;
}

/**
 * Validate CSRF token
 */
function validateCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Generate CSRF token for next form load (available via session)
generateCSRFToken();

// Rate limiting check
if (!checkRateLimit($rateLimitFile, $rateLimitPeriod, $rateLimitMax)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many submissions. Please wait before trying again.']);
    exit;
}

// Honeypot spam check - multiple honeypots for better protection
if (!empty($_POST['website']) || !empty($_POST['url']) || !empty($_POST['confirm_email'])) {
    // Silently fail for bots - return success to confuse them
    echo json_encode(['success' => true, 'message' => 'Thank you for your message.']);
    exit;
}

// Timestamp check - forms submitted too quickly are likely bots
$formTime = isset($_POST['form_time']) ? (int)$_POST['form_time'] : 0;
if ($formTime > 0 && (time() - $formTime) < 3) {
    // Submitted in less than 3 seconds - likely a bot
    echo json_encode(['success' => true, 'message' => 'Thank you for your message.']);
    exit;
}

// Get and sanitize form data
$name = cleanInput($_POST['name'] ?? '');
$email = sanitizeEmailForHeader($_POST['email'] ?? '');
$company = cleanInput($_POST['company'] ?? '');
$phone = cleanInput($_POST['phone'] ?? '');
$service = cleanInput($_POST['service'] ?? '');
$message = cleanInput($_POST['message'] ?? '');
$privacy = isset($_POST['privacy']) ? true : false;
$csrfToken = $_POST['csrf_token'] ?? '';

// Validate CSRF token
if (!validateCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security validation failed. Please refresh the page and try again.']);
    exit;
}

// Validate CAPTCHA
$captchaFormId = cleanInput($_POST['captcha_form_id'] ?? '');
$captchaAnswer = strtolower(trim($_POST['captcha_answer'] ?? ''));
$captchaSessionKey = 'captcha_' . $captchaFormId;

if (empty($captchaFormId) || !isset($_SESSION[$captchaSessionKey])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Security check expired. Please refresh the page and try again.']);
    exit;
}

$captchaData = $_SESSION[$captchaSessionKey];

// Check if CAPTCHA has expired (5 minutes)
if (time() > $captchaData['expires']) {
    unset($_SESSION[$captchaSessionKey]);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Security check expired. Please refresh the page and try again.']);
    exit;
}

// Validate the answer (case-insensitive comparison)
if (empty($captchaAnswer) || $captchaAnswer !== $captchaData['code']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Incorrect security code. Please try again.']);
    exit;
}

// Clear the used CAPTCHA to prevent replay attacks
unset($_SESSION[$captchaSessionKey]);

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
    'Content-Type: text/plain; charset=UTF-8'
];

$autoReplyHeaders = [
    'From: ' . $companyName . ' <noreply@pinecrest.nl>',
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
