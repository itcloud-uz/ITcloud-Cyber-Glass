from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import JSONResponse
import cv2
import numpy as np
import base64
from typing import Dict
import os
import logging
import time

# Faster Logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("faceid-v6-fast")

app = FastAPI(title="ITcloud Face ID Speedy API")

API_KEY = os.getenv("FACEID_API_KEY", "itcloud_secret_faceid_2026")

# Pre-load detector
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

def decode_image(base64_str: str):
    if not base64_str:
        return None
    try:
        if "," in base64_str:
            base64_str = base64_str.split(",")[1]
        image_bytes = base64.b64decode(base64_str)
        return cv2.imdecode(np.frombuffer(image_bytes, np.uint8), cv2.IMREAD_COLOR)
    except:
        return None

def get_face_crop(img):
    """
    Optimized Face Detection and Cropping
    """
    if img is None:
        return None
    
    # 1. Downscale for faster detection (320px width)
    h, w = img.shape[:2]
    scale = 320 / w
    img_small = cv2.resize(img, (320, int(h * scale)))
    gray = cv2.cvtColor(img_small, cv2.COLOR_BGR2GRAY)
    
    # 2. Fast Detection parameters
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.2, minNeighbors=5, minSize=(40, 40))
    
    if len(faces) == 0:
        return None
        
    # Take the most prominent face
    fx, fy, fw, fh = sorted(faces, key=lambda x: x[2]*x[3], reverse=True)[0]
    
    # 3. Map back to original image coordinates
    orig_x, orig_y = int(fx / scale), int(fy / scale)
    orig_w, orig_h = int(fw / scale), int(fh / scale)
    
    return img[orig_y:orig_y+orig_h, orig_x:orig_x+orig_w]

def compare_biometrics(live_img, orig_img):
    """
    Super-fast Histogram Analysis on cropped faces
    """
    start = time.time()
    
    live_face = get_face_crop(live_img)
    orig_face = get_face_crop(orig_img)
    
    if live_face is None or orig_face is None:
        logger.info(f"Biometric fail: Face not found. Time: {time.time()-start:.3f}s")
        return False
        
    # Standardize for comparison
    live_face = cv2.resize(live_face, (150, 150))
    orig_face = cv2.resize(orig_face, (150, 150))
    
    # HSV Histograms
    hsv_live = cv2.cvtColor(live_face, cv2.COLOR_BGR2HSV)
    hsv_orig = cv2.cvtColor(orig_face, cv2.COLOR_BGR2HSV)
    
    hist_l = cv2.calcHist([hsv_live], [0, 1], None, [40, 40], [0, 180, 0, 256])
    hist_o = cv2.calcHist([hsv_orig], [0, 1], None, [40, 40], [0, 180, 0, 256])
    
    cv2.normalize(hist_l, hist_l, 0, 1, cv2.NORM_MINMAX)
    cv2.normalize(hist_o, hist_o, 0, 1, cv2.NORM_MINMAX)
    
    corr = cv2.compareHist(hist_l, hist_o, cv2.HISTCMP_CORREL)
    logger.info(f"Match: {corr:.2f} | Pure Time: {time.time()-start:.3f}s")
    
    return corr > 0.85

@app.post("/api/v1/verify-face")
async def verify_face(request: Request, payload: Dict):
    key = request.headers.get("X-API-KEY")
    if key != API_KEY:
        raise HTTPException(status_code=403, detail="Forbidden")
    
    try:
        live = decode_image(payload.get("live_image", ""))
        orig = decode_image(payload.get("original_image", ""))

        if compare_biometrics(live, orig):
            return JSONResponse({"status": "success", "face_token": "speedy_auth_v6", "message": "Identity OK"})
        else:
            return JSONResponse({"status": "error", "message": "Taqqoslash muvaffaqiyatsiz bo'ldi"}, status_code=401)
            
    except Exception as e:
        logger.error(f"Error: {e}")
        return JSONResponse({"status": "error", "message": str(e)}, status_code=500)

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8001)
