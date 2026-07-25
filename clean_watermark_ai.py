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

    # Create mask for watermark & logo in the middle region
    mask = np.zeros((h, w), dtype=np.uint8)

    # Convert to HSV & Lab color spaces for accurate logo detection
    hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)
    
    # 1. Detect Blue logo (PHUTUNGOTOMIENBAC logo & car icon)
    # Blue HSV range: Hue 100-140, Saturation 40-255, Value 40-255
    lower_blue = np.array([95, 35, 40])
    upper_blue = np.array([140, 255, 255])
    blue_mask = cv2.inRange(hsv, lower_blue, upper_blue)

    # 2. Detect Watermark text/lines in central area (relX 0.15..0.85, relY 0.20..0.80)
    roi_mask = np.zeros((h, w), dtype=np.uint8)
    ymin, ymax = int(h * 0.20), int(h * 0.80)
    xmin, xmax = int(w * 0.15), int(w * 0.85)
    roi_mask[ymin:ymax, xmin:xmax] = 255

    # Semi-transparent brown/gray watermark overlay mask
    # Brown/gray tone: R >= G >= B with low saturation
    b, g, r = cv2.split(img)
    diff_rg = cv2.absdiff(r, g)
    diff_rb = cv2.absdiff(r, b)
    brown_mask = (r >= g) & (g >= b) & (diff_rg < 40) & (diff_rb < 60) & (r < 252) & (g < 252) & (b < 252)
    brown_mask = (brown_mask.astype(np.uint8) * 255) & roi_mask

    # Combine masks
    full_mask = cv2.bitwise_or(blue_mask & roi_mask, brown_mask)

    # Dilate mask slightly to cover edges smoothly (3x3 kernel)
    kernel = np.ones((5, 5), np.uint8)
    dilated_mask = cv2.dilate(full_mask, kernel, iterations=2)

    # Run OpenCV Telea Inpainting to reconstruct product structure & grille lines seamlessly
    cleaned_img = cv2.inpaint(img, dilated_mask, inpaintRadius=7, flags=cv2.INPAINT_TELEA)

    # Save output image
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
