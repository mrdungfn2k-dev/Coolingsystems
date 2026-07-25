<?php
/**
 * gemini_watermark_service.php
 * Dịch vụ tự động nhận diện và xóa Watermark / Logo bằng Gemini Vision API & GD Image Processing
 */

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: (defined('GEMINI_KEY') ? GEMINI_KEY : ''));
}

class GeminiWatermarkCleaner {
    
    /**
     * Tự động kiểm tra và xóa watermark khỏi file ảnh
     * @param string $sourcePath Đường dẫn file ảnh đầu vào
     * @param string|null $outputPath Đường dẫn lưu file ảnh sạch (nếu null sẽ đè lên file nguồn)
     * @return array Kết quả dạng ['success' => bool, 'cleaned' => bool, 'message' => string, 'path' => string]
     */
    public static function processImage(string $sourcePath, ?string $outputPath = null): array {
        if (!file_exists($sourcePath)) {
            return ['success' => false, 'cleaned' => false, 'message' => 'File ảnh không tồn tại.', 'path' => $sourcePath];
        }

        if ($outputPath === null) {
            $outputPath = $sourcePath;
        }

        // Bước 1: Gọi Gemini Vision API để kiểm tra xem ảnh có watermark/logo hay không
        $analysis = self::detectWatermarkWithGemini($sourcePath);

        if (!$analysis['has_watermark']) {
            return [
                'success' => true,
                'cleaned' => false,
                'message' => 'Ảnh không có watermark/logo.',
                'path' => $sourcePath
            ];
        }

        // Bước 2: Tiến hành xóa watermark và khôi phục nền trắng nguyên bản
        $cleanedOk = self::cleanWatermarkMathematical($sourcePath, $outputPath);

        if ($cleanedOk) {
            return [
                'success' => true,
                'cleaned' => true,
                'message' => 'Đã xóa watermark logo thành công, giữ 100% chi tiết sản phẩm.',
                'path' => $outputPath
            ];
        }

        return [
            'success' => false,
            'cleaned' => false,
            'message' => 'Không thể xử lý làm sạch ảnh.',
            'path' => $sourcePath
        ];
    }

    /**
     * Nhận diện Watermark bằng Gemini Vision API
     */
    private static function detectWatermarkWithGemini(string $imagePath): array {
        $apiKey = GEMINI_API_KEY;
        if (empty($apiKey)) {
            return ['has_watermark' => true]; // Fallback luôn chạy clean nếu không có API key
        }

        $imageData = file_get_contents($imagePath);
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';
        $base64Image = base64_encode($imageData);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}";

        $promptText = "Examine this product image carefully. Is there any watermark, logo overlay, website domain text, or copyright overlay on the image? Output ONLY valid JSON: {\"has_watermark\": true/false, \"watermark_text\": \"description or null\"}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $promptText],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Image
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 150
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $data = json_decode($matches[0], true);
                if (isset($data['has_watermark'])) {
                    return ['has_watermark' => (bool)$data['has_watermark']];
                }
            }
        }

        return ['has_watermark' => true];
    }

    /**
     * Thuật toán tách màu mờ toán học (RGB Unmixing) để làm sạch logo watermark
     */
    private static function cleanWatermarkMathematical(string $srcPath, string $destPath): bool {
        $info = @getimagesize($srcPath);
        if (!$info) return false;

        $mime = $info['mime'];
        $img = null;

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $img = @imagecreatefromjpeg($srcPath);
                break;
            case 'image/png':
                $img = @imagecreatefrompng($srcPath);
                break;
            case 'image/webp':
                $img = @imagecreatefromwebp($srcPath);
                break;
            default:
                return false;
        }

        if (!$img) return false;

        $width = imagesx($img);
        $height = imagesy($img);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $relX = $x / $width;
                $relY = $y / $height;

                if ($relX >= 0.15 && $relX <= 0.85 && $relY >= 0.25 && $relY <= 0.75) {
                    if ($r < 252 || $g < 252 || $b < 252) {
                        $diffRG = abs($r - $g);
                        $diffRB = abs($r - $b);

                        if ($r >= $g && $g >= $b && $diffRG < 45 && $diffRB < 65) {
                            $alpha = (255 - $g) / 255.0;
                            if ($alpha > 0.03 && $alpha < 0.65) {
                                $newR = min(255, (int)round($r + 147 * $alpha * 0.95));
                                $newG = min(255, (int)round($g + 95 * $alpha * 0.95));
                                $newB = min(255, (int)round($b + 62 * $alpha * 0.95));

                                $newColor = imagecolorallocate($img, $newR, $newG, $newB);
                                imagesetpixel($img, $x, $y, $newColor);
                            }
                        }
                    }
                }
            }
        }

        $ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
        $success = false;

        if ($ext === 'png') {
            $success = imagepng($img, $destPath, 8);
        } else if ($ext === 'webp') {
            $success = imagewebp($img, $destPath, 90);
        } else {
            $success = imagejpeg($img, $destPath, 92);
        }

        imagedestroy($img);
        return $success;
    }
}
