import sys
import cv2
import numpy as np
import os

def clean_watermark(input_path, output_path):
    if not os.path.exists(input_path):
        print(f"Error: input file {input_path} not found")
        sys.exit(1)

    img = cv2.imread(input_path)
    if img is None:
        print(f"Error: failed to read image {input_path}")
        sys.exit(1)

    h, w = img.shape[:2]

    # Convert HSV & BGR
    hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)
    b, g, r = cv2.split(img)

    # 1. Nhận diện chính xác Logo màu xanh dương (PHUTUNGOTOMIENBAC logo & car icon)
    lower_blue = np.array([90, 35, 35])
    upper_blue = np.array([135, 255, 255])
    blue_mask = cv2.inRange(hsv, lower_blue, upper_blue)

    # 2. Nhận diện chữ watermark xám mờ trên nền trắng (TUYỆT ĐỐI KHÔNG chạm vào nhựa tối màu cản xe)
    # Chỉ chọn các pixel có độ sáng cao (R,G,B từ 140..245) và nằm cạnh nền trắng (R,G,B > 245)
    light_gray = (r >= 140) & (r <= 245) & (g >= 140) & (g <= 245) & (b >= 140) & (b <= 245)
    diff_rg = cv2.absdiff(r, g)
    diff_rb = cv2.absdiff(r, b)
    is_neutral_gray = (diff_rg < 30) & (diff_rb < 30)

    # Nhận diện vùng nền trắng xung quanh
    kernel_white = np.ones((9, 9), np.uint8)
    white_pixels = ((r > 245) & (g > 245) & (b > 245)).astype(np.uint8) * 255
    is_near_white = cv2.dilate(white_pixels, kernel_white) > 0

    watermark_text_mask = light_gray & is_neutral_gray & is_near_white

    # Tổng hợp mask logo xanh + watermark chữ
    combined = cv2.bitwise_or(blue_mask, (watermark_text_mask.astype(np.uint8) * 255))

    # Giới hạn vùng trung tâm (relX 0.15..0.85, relY 0.20..0.80)
    roi = np.zeros((h, w), dtype=np.uint8)
    roi[int(h * 0.20):int(h * 0.80), int(w * 0.15):int(w * 0.85)] = 255

    final_mask = cv2.bitwise_and(combined, roi)

    # Nếu không phát hiện watermark, giữ nguyên 100% ảnh gốc
    if cv2.countNonZero(final_mask) < 20:
        cv2.imwrite(output_path, img)
        print(f"SUCCESS: no watermark detected, kept original in {output_path}")
        return

    # Giới hạn vùng xóa nhỏ gọn (kernel 3x3), TUYỆT ĐỐI không làm nhòe hay vệt ố nhựa cản xe
    kernel_small = np.ones((3, 3), np.uint8)
    final_mask = cv2.dilate(final_mask, kernel_small, iterations=1)

    # Thực hiện Inpainting Telea nội suy tái tạo bề mặt mịn mượt
    cleaned_img = cv2.inpaint(img, final_mask, inpaintRadius=3, flags=cv2.INPAINT_TELEA)

    # Lưu file kết quả
    ext = os.path.splitext(output_path)[1].lower()
    if ext in ['.jpg', '.jpeg']:
        cv2.imwrite(output_path, cleaned_img, [int(cv2.IMWRITE_JPEG_QUALITY), 95])
    elif ext == '.png':
        cv2.imwrite(output_path, cleaned_img, [int(cv2.IMWRITE_PNG_COMPRESSION), 6])
    else:
        cv2.imwrite(output_path, cleaned_img)

    print(f"SUCCESS: cleaned image saved to {output_path}")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python clean_watermark_ai.py <input_path> <output_path>")
        sys.exit(1)

    input_p = sys.argv[1]
    output_p = sys.argv[2]
    clean_watermark(input_p, output_p)
