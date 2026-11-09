<?php
// Run on a schedule (e.g. cron every 5 minutes) to flush scheduled
// notifications and flag ones that failed to send as a crew follow-up.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers/EmailHelper.php';
require_once __DIR__ . '/../src/models/NotificationModel.php';
require_once __DIR__ . '/../src/models/LogModel.php';

$pdo = getDB();

$sql = "SELECT notification_id FROM notifications WHERE is_sent = 0 AND scheduled_at IS NOT NULL AND scheduled_at <= NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$due = $stmt->fetchAll();

foreach ($due as $row) {
    $nid = $row['notification_id'];
    $sent = NotificationModel::sendNow($nid);

    if (!$sent) {
        $q = $pdo->prepare("SELECT * FROM notifications WHERE notification_id = ?");
        $q->execute([$nid]);
        $n = $q->fetch();

        $msg = "Scheduled notification {$nid} (type: {$n['type']}) for report {$n['report_id']} "
             . "could NOT be sent. Recipient has no verified email.";

        $insert = $pdo->prepare("
            INSERT INTO notifications (user_id, report_id, type, title, message, link, is_read, created_at)
            VALUES (NULL, ?, 'crew_follow_up', 'Notification Failed', ?, NULL, 0, NOW())
        ");
        $insert->execute([$n['report_id'], $msg]);

    } else {
        LogModel::insertLog(null, 'notification_sent', 'notifications', $nid, null, json_encode(['sent' => true]), null, 'cron');
    }
}

echo "Processed " . count($due) . " due notification(s).\n";
