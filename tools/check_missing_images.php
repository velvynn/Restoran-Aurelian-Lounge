<?php
// Quick checker: list product images missing on disk.
// Open in browser or CLI: http://localhost/punya%20kevin/Restoran-Aurelian/tools/check_missing_images.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

$products = [];
$db->query('SELECT id, name, image FROM products ORDER BY id');
$products = $db->resultSet();

$root = realpath(__DIR__ . '/..');
$paths = [
    realpath($root . '/assets/img'),
    realpath($root . '/assets/uploads/products'),
];

$missing = [];
$empty = [];
$found = [];

foreach ($products as $p) {
    $img = trim((string)($p['image'] ?? ''));
    if ($img === '') {
        $empty[] = $p;
        continue;
    }

    $filename = basename($img);
    $exists = false;

    foreach ($paths as $dir) {
        if (!$dir) {
            continue;
        }
        if (file_exists($dir . '/' . $filename)) {
            $exists = true;
            $found[] = ['product' => $p, 'path' => $dir . '/' . $filename];
            break;
        }
    }

    if (!$exists) {
        $missing[] = $p;
    }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Image Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f4f4f4; text-align: left; }
        .missing { background: #ffe6e6; }
        .ok { background: #e8f7e8; }
        .empty { background: #fff7e0; }
    </style>
</head>
<body>
    <h2>Product Image Check</h2>
    <p>Root: <?= htmlspecialchars($root) ?></p>
    <p>Folders checked:</p>
    <ul>
        <?php foreach ($paths as $dir): ?>
            <?php if ($dir): ?>
                <li><?= htmlspecialchars($dir) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <h3>Missing files (<?= count($missing) ?>)</h3>
    <?php if ($missing): ?>
    <table>
        <tr><th>ID</th><th>Name</th><th>Image</th></tr>
        <?php foreach ($missing as $p): ?>
            <tr class="missing">
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['image']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>None.</p>
    <?php endif; ?>

    <h3>Empty image value (<?= count($empty) ?>)</h3>
    <?php if ($empty): ?>
    <table>
        <tr><th>ID</th><th>Name</th></tr>
        <?php foreach ($empty as $p): ?>
            <tr class="empty">
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>None.</p>
    <?php endif; ?>

    <h3>Found files (<?= count($found) ?>)</h3>
    <?php if ($found): ?>
    <table>
        <tr><th>ID</th><th>Name</th><th>Image</th><th>Path</th></tr>
        <?php foreach ($found as $row): ?>
            <tr class="ok">
                <td><?= (int)$row['product']['id'] ?></td>
                <td><?= htmlspecialchars($row['product']['name']) ?></td>
                <td><?= htmlspecialchars($row['product']['image']) ?></td>
                <td><?= htmlspecialchars($row['path']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>None.</p>
    <?php endif; ?>
</body>
</html>