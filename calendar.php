<?php
require_once __DIR__ . '/includes/header.php';

$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day_of_month);
$day_of_week = date('w', $first_day_of_month);

$class_id = null;
$sql = 'SELECT DAY(start_date) as day, title FROM calendar_events WHERE (user_id = :user_id OR user_id IS NULL';
$params = ['user_id' => $_SESSION['user_id'], 'month' => $month, 'year' => $year];

if ($_SESSION['role'] === 'Student') {
    $stmt_class = $pdo->prepare('SELECT class_id FROM student_classes WHERE student_id = ?');
    $stmt_class->execute([$_SESSION['user_id']]);
    $class_id = $stmt_class->fetchColumn();
    if ($class_id) {
        $sql .= ' OR class_id = :class_id';
        $params['class_id'] = $class_id;
    }
}

$sql .= ') AND MONTH(start_date) = :month AND YEAR(start_date) = :year';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

?>

<h1>Calendar</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><?= date('F Y', $first_day_of_month) ?></h2>
    <div>
        <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>" class="btn btn-primary">&lt; Prev</a>
        <a href="?month=<?= date('m') ?>&year=<?= date('Y') ?>" class="btn btn-primary">Today</a>
        <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>" class="btn btn-primary">Next &gt;</a>
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Sun</th>
            <th>Mon</th>
            <th>Tue</th>
            <th>Wed</th>
            <th>Thu</th>
            <th>Fri</th>
            <th>Sat</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <?php
            for ($i = 0; $i < $day_of_week; $i++) {
                echo '<td></td>';
            }
            for ($day = 1; $day <= $days_in_month; $day++) {
                if ($day_of_week == 7) {
                    echo '</tr><tr>';
                    $day_of_week = 0;
                }
                echo '<td>';
                echo $day;
                if (isset($events[$day])) {
                    foreach ($events[$day] as $event) {
                        echo '<div class="alert alert-info mt-1">' . htmlspecialchars($event['title']) . '</div>';
                    }
                }
                echo '</td>';
                $day_of_week++;
            }
            for ($i = $day_of_week; $i < 7; $i++) {
                echo '<td></td>';
            }
            ?>
        </tr>
    </tbody>
</table>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
