<?php
/**
 * ProjectFlow Beta Signup Handler
 * Processes beta waitlist signups and stores/sends notifications
 */

// Start session for CSRF protection
session_start();

// Configuration
$recipientEmail = 'info@pinecrest.nl'; // Change this to your actual email
$companyName = 'PineCrest';
$productName = 'ProjectFlow';

// Rate limiting configuration
$rateLimitFile = sys_get_temp_dir() . '/pinecrest_beta_' . md5($_SERVER['REMOTE_ADDR']);
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
    echo json_encode(['success' => true, 'message' => 'Thank you for signing up!']);
    exit;
}

// Timestamp check - forms submitted too quickly are likely bots
$formTime = isset($_POST['form_time']) ? (int)$_POST['form_time'] : 0;
if ($formTime > 0 && (time() - $formTime) < 3) {
    // Submitted in less than 3 seconds - likely a bot
    echo json_encode(['success' => true, 'message' => 'Thank you for signing up!']);
    exit;
}

// Get and sanitize form data
$name = cleanInput($_POST['name'] ?? '');
$email = sanitizeEmailForHeader($_POST['email'] ?? '');
$company = cleanInput($_POST['company'] ?? '');
$teamSize = cleanInput($_POST['team_size'] ?? '');
$useCase = cleanInput($_POST['use_case'] ?? '');
$privacy = isset($_POST['privacy']) ? true : false;
$csrfToken = $_POST['csrf_token'] ?? '';

// Validate CSRF token
if (!validateCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security validation failed. Please refresh the page and try again.']);
    exit;
}

// Validate required fields
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (!$privacy) {
    $errors[] = 'You must agree to receive updates';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Use case names
$useCaseNames = [
    'software' => 'Software Development',
    'consulting' => 'Consulting Firm',
    'product' => 'Product Organization',
    'agency' => 'Agency/Marketing',
    'government' => 'Government/Public Sector',
    'education' => 'Education/Research',
    'other' => 'Other'
];

$useCaseName = $useCaseNames[$useCase] ?? 'Not specified';

// Prepare email for admin
$to = $recipientEmail;
$subject = "New $productName Beta Signup: $name";

$emailBody = "
New beta waitlist signup for $productName!

SIGNUP DETAILS
==============
Name: $name
Email: $email
" . ($company ? "Company: $company\n" : "") . "
" . ($teamSize ? "Team Size: $teamSize\n" : "") . "
" . ($useCase ? "Use Case: $useCaseName\n" : "") . "

Signup Date: " . date('Y-m-d H:i:s') . "

This person has agreed to receive updates about $productName.
";

// Email body for user confirmation
$autoReplyBody = "
Hi $name,

Thank you for joining the $productName beta waitlist!

We're excited to have you on board. $productName is being built to help teams deliver projects faster using Critical Chain Project Management principles.

WHAT'S NEXT:
=============
• We'll keep you updated on our development progress
• You'll be among the first to know when beta access is available
• Your feedback will help shape the final product

Expected Release: May 2026

If you have any questions or want to learn more about PineCrest's consulting services, feel free to reply to this email.

Best regards,
The PineCrest Team

---
PineCrest
Critical Chain Project Management Consultancy
Wenum Wiesel, The Netherlands
info@pinecrest.nl
www.pinecrest.nl

$productName - Projects. Programmes. Portfolios. Simplified.
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

// Send confirmation to user
$autoReplySent = mail($email, "You're on the $productName Beta Waitlist!", wordwrap($autoReplyBody, 70), implode("\r\n", $autoReplyHeaders));

if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for joining the beta waitlist! Check your email for confirmation.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to process signup. Please try again later or contact us directly at info@pinecrest.nl'
    ]);
}
