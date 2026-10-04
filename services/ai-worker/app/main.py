from fastapi import FastAPI, File, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

app = FastAPI(
    title="AI Worker OCR Service",
    description="Microservicio FastAPI para procesamiento de evidencias e imágenes.",
    version="1.0.0"
)

# Configuración básica de CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # En producción restringir al host de Laravel
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class OcrResponse(BaseModel):
    success: bool
    detected_serial: str
    detected_text: str
    confidence: float

@app.get("/health", tags=["Health"])
def health_check():
    """Endpoint de salud del servicio."""
    return {"status": "ok"}

@app.post("/api/v1/ocr/process-evidence", response_model=OcrResponse, tags=["OCR"])
async def process_evidence(file: UploadFile = File(...)):
    """
    Recibe una imagen y extrae el texto usando OCR.
    (Implementación mock funcional para cumplir el principio YAGNI en esta fase).
    """
    # Aquí iría la lógica real usando pytesseract o easyocr
    # file_bytes = await file.read()
    
    # Mock funcional
    return OcrResponse(
        success=True,
        detected_serial="SN-MOCK12345",
        detected_text="Información simulada de la imagen leída exitosamente.",
        confidence=0.95
    )
