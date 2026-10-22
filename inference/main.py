"""Self-hosted background-removal service (rembg + FastAPI).

Run:  .venv\Scripts\python -m uvicorn main:app --host 127.0.0.1 --port 8001
Env:  BG_PYTHON_TOKEN  shared secret expected in the X-Api-Token header
      BG_MODEL         rembg model name (default: birefnet-general-lite)
"""
import hmac
import io
import os

from fastapi import FastAPI, File, Header, HTTPException, UploadFile
from fastapi.responses import Response
from PIL import Image, ImageOps
from rembg import new_session, remove

Image.MAX_IMAGE_PIXELS = 30_000_000  # decompression-bomb guard

TOKEN = os.environ.get("BG_PYTHON_TOKEN", "local-dev-token")
MODEL = os.environ.get("BG_MODEL", "birefnet-general-lite")
MAX_BYTES = 15 * 1024 * 1024

app = FastAPI(title="Background removal service")
session = new_session(MODEL)


@app.get("/health")
def health():
    return {"status": "ok", "model": MODEL}


@app.post("/remove")
async def remove_background(file: UploadFile = File(...), x_api_token: str = Header(default="")):
    if not hmac.compare_digest(x_api_token, TOKEN):
        raise HTTPException(status_code=401, detail="unauthorized")

    data = await file.read(MAX_BYTES + 1)
    if len(data) > MAX_BYTES:
        raise HTTPException(status_code=422, detail="too large")

    try:
        img = Image.open(io.BytesIO(data))
        img.load()
        img = ImageOps.exif_transpose(img).convert("RGBA")  # normalise orientation
    except Exception:
        raise HTTPException(status_code=422, detail="not a decodable image")

    out = remove(img, session=session)
    buf = io.BytesIO()
    out.convert("RGBA").save(buf, format="PNG")
    return Response(content=buf.getvalue(), media_type="image/png")
