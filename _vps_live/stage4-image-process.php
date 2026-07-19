<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

require_once '/opt/cooling-php/includes/helpers.php';

function normalizeStage4Image(
    string $sourcePath,
    string $destinationPath,
    string $mode,
    ?string &$error = null
): bool {
    if ($mode === 'default') {
        return normalizeProductImageFile($sourcePath, $destinationPath, $error);
    }

    $error = null;
    $cutoutPath = dirname($destinationPath) . '/.'
        . pathinfo($destinationPath, PATHINFO_FILENAME) . '-safe-' . bin2hex(random_bytes(4)) . '.png';
    $temporaryPath = dirname($destinationPath) . '/.'
        . pathinfo($destinationPath, PATHINFO_FILENAME) . '-safe-' . bin2hex(random_bytes(4)) . '.webp';
    $curlCommand = '/usr/bin/curl --fail-with-body --silent --show-error'
        . ' --connect-timeout 2 --max-time 90'
        . ' --header ' . escapeshellarg('Content-Type: application/octet-stream')
        . ' --data-binary ' . escapeshellarg('@' . $sourcePath)
        . ' --output ' . escapeshellarg($cutoutPath)
        . ' ' . escapeshellarg('http://127.0.0.1:7010/remove-safe') . ' 2>&1';
    $output = [];
    exec($curlCommand, $output, $exitCode);
    $cutoutInfo = is_file($cutoutPath) ? getimagesize($cutoutPath) : false;
    if ($exitCode !== 0 || !$cutoutInfo || ($cutoutInfo['mime'] ?? '') !== 'image/png') {
        @unlink($cutoutPath);
        $error = trim(implode(' ', $output)) ?: 'safe_background_service_failed';
        return false;
    }

    $convertCommand = '/usr/bin/convert -limit memory 256MiB -limit map 512MiB '
        . escapeshellarg($cutoutPath)
        . ' -auto-orient -strip -colorspace sRGB -trim +repage'
        . ' -filter Lanczos -resize ' . escapeshellarg('1020x765')
        . ' -gravity center -background ' . escapeshellarg('#ffffff')
        . ' -alpha background -alpha off -extent ' . escapeshellarg('1200x900')
        . ' -unsharp ' . escapeshellarg('0x0.55+0.42+0.020')
        . ' -define webp:method=6 -quality 93 '
        . escapeshellarg($temporaryPath) . ' 2>&1';
    $output = [];
    exec($convertCommand, $output, $exitCode);
    @unlink($cutoutPath);
    $normalizedInfo = is_file($temporaryPath) ? getimagesize($temporaryPath) : false;
    if ($exitCode !== 0 || !$normalizedInfo || (int)$normalizedInfo[0] !== 1200 || (int)$normalizedInfo[1] !== 900) {
        @unlink($temporaryPath);
        $error = trim(implode(' ', $output)) ?: 'safe_normalization_failed';
        return false;
    }
    if (!rename($temporaryPath, $destinationPath)) {
        @unlink($temporaryPath);
        $error = 'safe_destination_rename_failed';
        return false;
    }
    chmod($destinationPath, 0664);
    return true;
}

function optionValue(array $arguments, string $name, ?string $default = null): ?string
{
    $prefix = '--' . $name . '=';
    foreach ($arguments as $argument) {
        if (str_starts_with($argument, $prefix)) {
            return substr($argument, strlen($prefix));
        }
    }
    return $default;
}

function hasOption(array $arguments, string $name): bool
{
    return in_array('--' . $name, $arguments, true);
}

function loadAuditRows(string $path): array
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Cannot read image audit CSV');
    }
    $header = fgetcsv($handle);
    if (!is_array($header)) {
        throw new RuntimeException('Image audit CSV is empty');
    }
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]);
    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        if (count($values) !== count($header)) {
            continue;
        }
        $row = array_combine($header, $values);
        if (is_array($row)) {
            $rows[] = $row;
        }
    }
    fclose($handle);
    return $rows;
}

$arguments = array_slice($argv, 1);
$databasePath = optionValue($arguments, 'db', '/var/lib/cooling/cooling.db');
$uploadDir = rtrim((string)optionValue($arguments, 'uploads', '/var/lib/cooling/uploads/products'), '/');
$auditPath = (string)optionValue($arguments, 'audit', '/tmp/cooling-stage4/image-audit/stage4-image-audit.csv');
$manifestPath = (string)optionValue($arguments, 'manifest', '/tmp/cooling-stage4/stage4-image-process.json');
$mode = (string)optionValue($arguments, 'mode', 'safe');
if (!in_array($mode, ['safe', 'default'], true)) {
    throw new InvalidArgumentException('Mode must be safe or default');
}
$apply = hasOption($arguments, 'apply');
$limit = max(0, (int)optionValue($arguments, 'limit', '0'));
$idOption = trim((string)optionValue($arguments, 'ids', ''));
$selectedIds = $idOption === '' ? [] : array_fill_keys(
    array_filter(array_map('intval', explode(',', $idOption))),
    true
);

$auditRows = array_values(array_filter(
    loadAuditRows($auditPath),
    static function (array $row) use ($selectedIds): bool {
        if (($row['classification'] ?? '') !== 'needs_background') {
            return false;
        }
        if (array_key_exists('stage4_action', $row) && ($row['stage4_action'] ?? '') !== 'process') {
            return false;
        }
        $imageId = (int)($row['image_id'] ?? 0);
        return !$selectedIds || isset($selectedIds[$imageId]);
    }
));
if ($limit > 0) {
    $auditRows = array_slice($auditRows, 0, $limit);
}

$pdo = new PDO('sqlite:' . $databasePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$lookup = $pdo->prepare(
    "SELECT i.id,i.product_id,i.file_path,p.status
     FROM product_images i
     INNER JOIN products p ON p.id=i.product_id
     WHERE i.id=? AND i.product_id NOT IN (2,3,4,5,6,8)"
);

$results = [];
$updates = [];
foreach ($auditRows as $row) {
    $imageId = (int)$row['image_id'];
    $lookup->execute([$imageId]);
    $image = $lookup->fetch();
    if (!$image || $image['status'] !== 'draft') {
        $results[] = ['image_id' => $imageId, 'ok' => false, 'error' => 'not_a_restored_draft_image'];
        continue;
    }
    if ((string)$image['file_path'] !== (string)$row['file_path']) {
        $results[] = ['image_id' => $imageId, 'ok' => false, 'error' => 'source_path_changed'];
        continue;
    }

    $sourceName = basename((string)$image['file_path']);
    $sourcePath = $uploadDir . '/' . $sourceName;
    $baseName = pathinfo($sourceName, PATHINFO_FILENAME);
    $destinationName = $baseName . '-stage4-' . $mode . '-bg-i' . $imageId . '.webp';
    $destinationPath = $uploadDir . '/' . $destinationName;
    if (!is_file($sourcePath)) {
        $results[] = ['image_id' => $imageId, 'ok' => false, 'error' => 'source_missing'];
        continue;
    }

    $error = null;
    $startedAt = microtime(true);
    if (!normalizeStage4Image($sourcePath, $destinationPath, $mode, $error)) {
        $results[] = [
            'image_id' => $imageId,
            'product_id' => (int)$image['product_id'],
            'ok' => false,
            'error' => $error ?: 'normalization_failed',
            'seconds' => round(microtime(true) - $startedAt, 3),
        ];
        continue;
    }

    $info = getimagesize($destinationPath);
    $result = [
        'image_id' => $imageId,
        'product_id' => (int)$image['product_id'],
        'ok' => true,
        'source' => $sourceName,
        'destination' => $destinationName,
        'source_sha256' => hash_file('sha256', $sourcePath),
        'destination_sha256' => hash_file('sha256', $destinationPath),
        'width' => (int)($info[0] ?? 0),
        'height' => (int)($info[1] ?? 0),
        'seconds' => round(microtime(true) - $startedAt, 3),
    ];
    $results[] = $result;
    $updates[] = $result;
}

if ($apply && $updates) {
    $pdo->beginTransaction();
    try {
        $update = $pdo->prepare(
            "UPDATE product_images SET file_path=?
             WHERE id=? AND product_id=? AND file_path=?"
        );
        foreach ($updates as $item) {
            $update->execute([
                $item['destination'],
                $item['image_id'],
                $item['product_id'],
                $item['source'],
            ]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Concurrent image update detected for image ' . $item['image_id']);
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

$summary = [
    'ok' => count(array_filter($results, static fn(array $item): bool => !empty($item['ok']))),
    'failed' => count(array_filter($results, static fn(array $item): bool => empty($item['ok']))),
    'selected' => count($auditRows),
    'applied' => $apply ? count($updates) : 0,
    'results' => $results,
];
file_put_contents($manifestPath, json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo json_encode(array_diff_key($summary, ['results' => true]), JSON_UNESCAPED_UNICODE) . PHP_EOL;
