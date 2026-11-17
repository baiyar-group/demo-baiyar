"""Builds the per-colour product photos (needs Pillow): colour 1 is the original photo, colours 2 and 3 are
the same photo tinted. Output: bin/product-images/<slug>-color-<n>.jpg."""
import sys
from pathlib import Path
from PIL import Image, ImageChops

DIR = Path(__file__).parent / "product-images"
TINTS = {
    "runner-knit-sneakers": ["#8FB4D9", "#9DB59A"],
    "wool-comfort-sneakers": ["#C47A5A", "#4A5A85"],
    "everyday-canvas-sneakers": ["#E8C75A", "#5A6C99"],
    "recycled-slide-sandals": ["#CBB89A", "#8A8A55"],
    "eva-comfort-sandals": ["#6F8FAE", "#8A8A55"],
    "strap-sandals": ["#C96F4A", "#5A6C99"],
}


def hex_rgb(h):
    h = h.lstrip("#")
    return tuple(int(h[i:i + 2], 16) for i in (0, 2, 4))


for slug, tints in TINTS.items():
    src = Image.open(DIR / f"{slug}.jpg").convert("RGB")
    src.save(DIR / f"{slug}-color-1.jpg", quality=84)
    for n, tint in enumerate(tints, start=2):
        light = tuple(int(c + (255 - c) * 0.15) for c in hex_rgb(tint))
        layer = Image.new("RGB", src.size, light)
        tinted = ImageChops.multiply(src, layer)
        Image.blend(src, tinted, 1.0).save(DIR / f"{slug}-color-{n}.jpg", quality=84)
print("ok")
