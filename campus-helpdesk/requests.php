<?php
// requests.php
// Shows all support requests saved in the database (newest first).
// The IT team can filter the list by status and change the status of a request.

require 'db_connect.php';

$statuses = ['Open', 'In Progress', 'Resolved'];

// ---------- Update the status of a request ----------
// This runs when the small "Update" form next to a request is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
    $newStatus = $_POST['status'] ?? '';

    // Only update if the ID is a number and the status is one of the allowed values
    if ($id && in_array($newStatus, $statuses, true)) {
        $stmt = $pdo->prepare("UPDATE support_requests SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $id]);

        header('Location: requests.php?updated=' . $id);
        exit;
    }

    header('Location: requests.php');
    exit;
}

// ---------- Get the requests (optionally filtered by status) ----------
$filter = $_GET['status'] ?? '';

if (in_array($filter, $statuses, true)) {
    $stmt = $pdo->prepare("SELECT * FROM support_requests WHERE status = :status ORDER BY id DESC");
    $stmt->execute([':status' => $filter]);
} else {
    $filter = '';
    $stmt = $pdo->query("SELECT * FROM support_requests ORDER BY id DESC");
}
$requests = $stmt->fetchAll();

// ---------- Count the requests in each status for the summary boxes ----------
$counts = ['Open' => 0, 'In Progress' => 0, 'Resolved' => 0];
$countQuery = $pdo->query("SELECT status, COUNT(*) AS total FROM support_requests GROUP BY status");
foreach ($countQuery as $row) {
    $counts[$row['status']] = (int) $row['total'];
}
$totalRequests = array_sum($counts);

$updatedId = isset($_GET['updated']) ? (int) $_GET['updated'] : 0;

// Turns a value like "In Progress" into a CSS class like "in-progress"
function cssClass($text)
{
    return strtolower(str_replace(' ', '-', $text));
}

$pageTitle = 'View Requests';
$currentPage = 'requests';
include 'includes/header.php';
?>

<h1>Support Requests</h1>

<?php if ($updatedId > 0): ?>
    <div class="alert success">
        Request HD-<?php echo str_pad($updatedId, 4, '0', STR_PAD_LEFT); ?> was updated.
    </div>
<?php endif; ?>

<div class="stats">
    <div class="stat">
        <span class="number"><?php echo $totalRequests; ?></span> Total
    </div>
    <div class="stat open">
        <span class="number"><?php echo $counts['Open']; ?></span> Open
    </div>
    <div class="stat in-progress">
        <span class="number"><?php echo $counts['In Progress']; ?></span> In Progress
    </div>
    <div class="stat resolved">
        <span class="number"><?php echo $counts['Resolved']; ?></span> Resolved
    </div>
</div>

<form method="get" action="requests.php" class="filter">
    <label for="status">Show:</label>
    <select id="status" name="status">
        <option value="">All requests</option>
        <?php foreach ($statuses as $status): ?>
            <option value="<?php echo $status; ?>" <?php if ($filter === $status) echo 'selected'; ?>>
                <?php echo $status; ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
</form>

<?php if (count($requests) === 0): ?>

    <div class="card empty">
        <p>No requests found.</p>
        <p><a href="index.php">Submit a new request</a></p>
    </div>

<?php else: ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Problem</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td class="nowrap">
                        <strong>HD-<?php echo str_pad($req['id'], 4, '0', STR_PAD_LEFT); ?></strong><br>
                        <small><?php echo date('M j, Y', strtotime($req['created_at'])); ?></small><br>
                        <small><?php echo date('g:i A', strtotime($req['created_at'])); ?></small>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($req['full_name']); ?><br>
                        <small><?php echo htmlspecialchars($req['user_type']); ?></small><br>
                        <small>
                            <a href="mailto:<?php echo htmlspecialchars($req['email']); ?>"><?php echo htmlspecialchars($req['email']); ?></a>
                        </small>
                        <?php if (!empty($req['phone'])): ?>
                            <br><small><?php echo htmlspecialchars($req['phone']); ?></small>
                        <?php endif; ?>
                    </td>

                    <td class="category"><?php echo htmlspecialchars($req['category']); ?></td>

                    <td>
                        <span class="badge <?php echo htmlspecialchars(cssClass($req['priority'])); ?>">
                            <?php echo htmlspecialchars($req['priority']); ?>
                        </span>
                    </td>

                    <td class="problem">
                        <strong><?php echo htmlspecialchars($req['subject']); ?></strong>
                        <p><?php echo nl2br(htmlspecialchars($req['description'])); ?></p>
                    </td>

                    <td>
                        <span class="badge <?php echo htmlspecialchars(cssClass($req['status'])); ?>">
                            <?php echo htmlspecialchars($req['status']); ?>
                        </span>

                        <form method="post" action="requests.php" class="status-form">
                            <input type="hidden" name="id" value="<?php echo $req['id']; ?>">
                            <select name="status" aria-label="New status">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php if ($req['status'] === $status) echo 'selected'; ?>>
                                        <?php echo $status; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
