<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

function argumentValue(array $arguments, string $name, ?string $default = null): ?string
{
    $prefix = '--' . $name . '=';
    foreach ($arguments as $argument) {
        if (str_starts_with($argument, $prefix)) {
            return substr($argument, strlen($prefix));
        }
    }
    return $default;
}

$arguments = array_slice($argv, 1);
$apply = in_array('--apply', $arguments, true);
$databasePath = (string)argumentValue($arguments, 'db', '/var/lib/cooling/cooling.db');
$uploadDir = rtrim((string)argumentValue($arguments, 'uploads', '/var/lib/cooling/uploads/products'), '/');
$manifestPath = (string)argumentValue($arguments, 'manifest', '/tmp/cooling-stage4/image-all-default.json');
$approvedPath = (string)argumentValue($arguments, 'approved', '/tmp/cooling-stage4/image-approved-ids.txt');
$outputPath = (string)argumentValue($arguments, 'output', '/tmp/cooling-stage4/image-activation.json');

$manifest = json_decode((string)file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
$approvedIds = array_fill_keys(
    array_filter(
        array_map('intval', preg_split('/[^0-9]+/', (string)file_get_contents($approvedPath))),
        static fn(int $id): bool => $id > 0
    ),
    true
);

$pdo = new PDO('sqlite:' . $databasePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$lookup = $pdo->prepare(
    "SELECT i.id,i.product_id,i.file_path,p.status
     FROM product_images i INNER JOIN products p ON p.id=i.product_id
     WHERE i.id=? AND i.product_id NOT IN (2,3,4,5,6,8)"
);

$eligible = [];
$rejected = [];
foreach (($manifest['results'] ?? []) as $item) {
    $imageId = (int)($item['image_id'] ?? 0);
    if (empty($item['ok']) || !isset($approvedIds[$imageId])) {
        continue;
    }
    $lookup->execute([$imageId]);
    $image = $lookup->fetch();
    $reason = null;
    if (!$image || $image['status'] !== 'draft') {
        $reason = 'not_a_restored_draft_image';
    } elseif ((string)$image['file_path'] !== (string)$item['source']) {
        $reason = 'source_path_changed';
    } else {
        $sourcePath = $uploadDir . '/' . basename((string)$item['source']);
        $destinationPath = $uploadDir . '/' . basename((string)$item['destination']);
        $info = is_file($destinationPath) ? getimagesize($destinationPath) : false;
        if (!is_file($sourcePath) || hash_file('sha256', $sourcePath) !== ($item['source_sha256'] ?? '')) {
            $reason = 'source_hash_mismatch';
        } elseif (!$info || (int)$info[0] !== 1200 || (int)$info[1] !== 900) {
            $reason = 'destination_invalid';
        } elseif (hash_file('sha256', $destinationPath) !== ($item['destination_sha256'] ?? '')) {
            $reason = 'destination_hash_mismatch';
        }
    }
    if ($reason !== null) {
        $rejected[] = ['image_id' => $imageId, 'reason' => $reason];
        continue;
    }
    $eligible[] = $item;
}

if (count($eligible) !== count($approvedIds)) {
    throw new RuntimeException(
        'Approved/eligible mismatch: approved=' . count($approvedIds)
        . ' eligible=' . count($eligible) . ' rejected=' . count($rejected)
    );
}

if ($apply && $eligible) {
    $pdo->beginTransaction();
    try {
        $update = $pdo->prepare(
            "UPDATE product_images SET file_path=? WHERE id=? AND product_id=? AND file_path=?"
        );
        foreach ($eligible as $item) {
            $update->execute([
                basename((string)$item['destination']),
                (int)$item['image_id'],
                (int)$item['product_id'],
                basename((string)$item['source']),
            ]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Concurrent update for image ' . $item['image_id']);
            }
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

$result = [
    'ok' => true,
    'approved' => count($approvedIds),
    'eligible' => count($eligible),
    'rejected' => $rejected,
    'applied' => $apply ? count($eligible) : 0,
    'image_ids' => array_map(static fn(array $item): int => (int)$item['image_id'], $eligible),
];
file_put_contents($outputPath, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo json_encode(array_diff_key($result, ['image_ids' => true]), JSON_UNESCAPED_UNICODE) . PHP_EOL;
