import os
import glob
from PIL import Image

dirs = [
    r"c:\xampp2\htdocs\coolingsystems\public\uploads\banners",
    r"c:\xampp2\htdocs\coolingsystems\public\uploads\products"
]

for d in dirs:
    if not os.path.exists(d): continue
    for filepath in glob.glob(os.path.join(d, "*")):
        if not os.path.isfile(filepath): continue
        ext = os.path.splitext(filepath)[1].lower()
        if ext in ['.png', '.jpg', '.jpeg']:
            webp_path = os.path.splitext(filepath)[0] + '.webp'
            try:
                im = Image.open(filepath)
                im.convert('RGB').save(webp_path, 'WEBP', quality=82, method=6)
                orig_kb = os.path.getsize(filepath) // 1024
                webp_kb = os.path.getsize(webp_path) // 1024
                print(f"Compressed {os.path.basename(filepath)} ({orig_kb}KB) -> {os.path.basename(webp_path)} ({webp_kb}KB)")
            except Exception as e:
                print(f"Error compressing {filepath}: {e}")
