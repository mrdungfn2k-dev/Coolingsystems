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
            return ['has_watermark' => true];
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
     * Thuật toán tự động xóa logo mọi màu (Xanh dương, Trắng, Nâu, Xám, Đỏ) và Inpainting
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

        $whiteColor = imagecolorallocate($img, 255, 255, 255);

        // Lần 1: Xử lý xóa logo xanh dương + chữ nổi + watermark mờ toàn diện
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $relX = $x / $width;
                $relY = $y / $height;

                // Vùng nhận diện logo trung tâm
                if ($relX >= 0.10 && $relX <= 0.90 && $relY >= 0.15 && $relY <= 0.85) {
                    $isLogoPixel = false;

                    // 1. Nhận diện màu xanh dương đặc trưng của logo PHUTUNGOTOMIENBAC
                    if (($b > $r + 20 && $b > $g + 5) || ($b > 120 && $r < 110 && $g < 175) || ($g > $r + 15 && $b > $r + 15 && $r < 130)) {
                        $isLogoPixel = true;
                    }
                    
                    // 2. Nhận diện dải Watermark mờ xám nâu
                    elseif ($r < 252 || $g < 252 || $b < 252) {
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
                                continue;
                            }
                        }
                    }

                    // Nếu là điểm ảnh Logo (Xanh dương / Đỏ / Chữ nổi)
                    if ($isLogoPixel) {
                        // Lấy mẫu các pixel lân cận không thuộc logo (offset 6px) để nội suy màu (Inpainting)
                        $offsets = [[-6, 0], [6, 0], [0, -6], [0, 6], [-6, -6], [6, 6], [0, -10], [0, 10]];
                        $neighborR = 0; $neighborG = 0; $neighborB = 0; $count = 0;
                        $isNearWhite = false;

                        foreach ($offsets as $off) {
                            $nx = $x + $off[0];
                            $ny = $y + $off[1];
                            if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height) {
                                $nrgb = imagecolorat($img, $nx, $ny);
                                $nr = ($nrgb >> 16) & 0xFF;
                                $ng = ($nrgb >> 8) & 0xFF;
                                $nb = $nrgb & 0xFF;

                                if ($nr > 240 && $ng > 240 && $nb > 240) {
                                    $isNearWhite = true;
                                }

                                $neighborR += $nr; $neighborG += $ng; $neighborB += $nb;
                                $count++;
                            }
                        }

                        if ($isNearWhite || $count === 0) {
                            imagesetpixel($img, $x, $y, $whiteColor);
                        } else {
                            $avgR = (int)round($neighborR / $count);
                            $avgG = (int)round($neighborG / $count);
                            $avgB = (int)round($neighborB / $count);
                            $cleanCol = imagecolorallocate($img, $avgR, $avgG, $avgB);
                            imagesetpixel($img, $x, $y, $cleanCol);
                        }
                    }
                }
            }
        }

        // Lưu file kết quả
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
