<?php
// Function to create notifications for users
function create_notification($user_id, $message) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
    $stmt->execute([$user_id, $message]);
}
