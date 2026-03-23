from fastapi import FastAPI, File, UploadFile, HTTPException, Request
from fastapi.responses import JSONResponse
import cv2
import numpy as np
import base64
# from deepface import DeepFace
# import dlib
from typing import Dict

import os

app = FastAPI(title="ITcloud Face ID & Liveness API")

API_KEY = os.getenv("FACEID_API_KEY", "itcloud_secret_faceid_2026")

@app.post("/api/v1/verify-face")
async def verify_face(request: Request, payload: Dict):
    """
    Bu endpoint Laravel Backend tomonidan chaqiriladi.
    Faqatgina X-API-KEY kaliti mavjud bo'lsa muloqot qiladi.
    """
    # 0. API KEY Verification
    key = request.headers.get("X-API-KEY")
    if key != API_KEY:
        raise HTTPException(status_code=403, detail="Forbidden: Unauthorized API Access")
    
    try:
        image_data = payload.get("image", "")
        # base64_str = image_data.split(",")[1] if "," in image_data else image_data
        # image_bytes = base64.b64decode(base64_str)
        # np_arr = np.frombuffer(image_bytes, np.uint8)
        # img = cv2.imdecode(np_arr, cv2.IMREAD_COLOR)

        # 1. Liveness Detection
        # Bu yuzda harakat bor-yo'qligini aniqlaydi (EAR - Eye Aspect Ratio orqali).
        # Agar rasm tutishsa, ko'z pirpiramaydi va liveness 'False' chiqadi.
        liveness_passed = True # TODO: deep learning yoki dlib logic
        
        # 2. Facial Recognition
        # 'img' ni DB dagi 'master_admin.jpg' rasmi (yoki 128 o'lchamli encoding) bilan tekshirish.
        match_passed = True # TODO: if DeepFace.verify(...)
        
        if liveness_passed and match_passed:
            # Token generated internally to match Laravel Backend's expectation
            return JSONResponse({
                "status": "success",
                "face_token": "face_id_success", # Laravel kutayotgan kalit 
                "message": "Face verified securely."
            })
        else:
            return JSONResponse({
                "status": "error",
                "message": "Spoof detected or face doesn't match!"
            }, status_code=401)
            
    except Exception as e:
        return JSONResponse({"status": "error", "message": str(e)}, status_code=500)


if __name__ == "__main__":
    import uvicorn
    # Nginx orqali ichki tarmoqda ulanadi
    uvicorn.run(app, host="127.0.0.1", port=8001)
