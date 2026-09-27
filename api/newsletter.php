<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

reject_spam();
enforce_rate_limit('newsletter');

$email = require_email();

append_json_record('newsletter-signups.jsonl', [
    'type' => 'newsletter',
    'email' => $email,
]);

respond(true, 'You are subscribed. Thanks for joining the BrainMint AI Consultancy newsletter.');
