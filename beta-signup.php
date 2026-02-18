<?php
/**
 * ProjectFlow Beta Signup Handler
 * Processes beta waitlist signups and stores/sends notifications
 */

// Configuration
$recipientEmail = 'info@pinecrest.nl'; // Change this to your actual email
$companyName = 'PineCrest';
$productName = 'ProjectFlow';

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
    echo json_encode(['success' => true, 'message' => 'Thank you for signing up!']);
    exit;
}

$name = cleanInput($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$company = cleanInput($_POST['company'] ?? '');
$teamSize = cleanInput($_POST['team_size'] ?? '');
$useCase = cleanInput($_POST['use_case'] ?? '');
$privacy = isset($_POST['privacy']) ? true : false;

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
