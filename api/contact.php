<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

reject_spam();
enforce_rate_limit('contact');

$name = require_value('name', 'Name', 120);
$email = require_email();
$mobile = input_value('mobile', 40);
$subject = require_value('subject', 'Subject', 180);
$message = require_value('message', 'Message', 2000);

append_json_record('contact-submissions.jsonl', [
    'type' => 'contact',
    'name' => $name,
    'email' => $email,
    'mobile' => $mobile,
    'subject' => $subject,
    'message' => $message,
]);

respond(true, 'Thanks. Your message has been received and we will get back to you shortly.');
