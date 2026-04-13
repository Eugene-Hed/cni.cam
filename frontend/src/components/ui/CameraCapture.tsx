"use client";

import { useState, useRef, useEffect } from "react";
import { Camera, RefreshCw, CheckCircle, XCircle } from "lucide-react";

interface CameraCaptureProps {
  onCapture: (file: File) => void;
  label?: string;
  onCancelClick?: () => void;
}

export default function CameraCapture({ onCapture, label = "Prendre une photo", onCancelClick }: CameraCaptureProps) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  
  const [stream, setStream] = useState<MediaStream | null>(null);
  const [photoData, setPhotoData] = useState<string | null>(null);
  const [isActive, setIsActive] = useState(false);

  const startCamera = async () => {
    try {
      const mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
      setStream(mediaStream);
      setIsActive(true);
      setPhotoData(null);
    } catch (err) {
      console.error("Camera access error:", err);
      alert("Impossible d'accéder à la caméra. Vérifiez vos permissions.");
    }
  };

  const stopCamera = () => {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      setStream(null);
    }
    setIsActive(false);
  };

  const handleCancelClick = () => {
     stopCamera();
     if(onCancelClick) onCancelClick();
  };

  const capturePhoto = () => {
    if (videoRef.current && canvasRef.current) {
      const video = videoRef.current;
      const canvas = canvasRef.current;
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const ctx = canvas.getContext("2d", { willReadFrequently: true });
      if (ctx) {
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL("image/jpeg", 0.9);
        setPhotoData(dataUrl);
        stopCamera();
      }
    }
  };

  const confirmPhoto = () => {
    if (!photoData || !canvasRef.current) return;
    canvasRef.current.toBlob((blob) => {
      if (blob) {
        const file = new File([blob], `photo_${Date.now()}.jpg`, { type: "image/jpeg" });
        onCapture(file);
      }
    }, "image/jpeg", 0.9);
  };

  const retakePhoto = () => {
    setPhotoData(null);
    startCamera();
  };

  useEffect(() => {
    if (isActive && stream && videoRef.current) {
        videoRef.current.srcObject = stream;
    }
    return () => {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
      }
    };
  }, [stream, isActive]);

  if (photoData) {
    return (
      <div className="camera-capture-container text-center border p-3 rounded-4 bg-white">
        <label className="fw-bold mb-2 small text-muted">{label} (Aperçu)</label>
        <img src={photoData} alt="Captured" className="img-fluid rounded border mb-3 w-100" style={{ maxHeight: '250px', objectFit: 'contain' }} />
        <div className="d-flex justify-content-center gap-2">
          <button type="button" className="btn btn-sm btn-outline-secondary rounded-pill px-3" onClick={retakePhoto}>
            <RefreshCw size={14} className="me-1" /> Reprendre
          </button>
          <button type="button" className="btn btn-sm btn-success rounded-pill px-3" onClick={confirmPhoto}>
            <CheckCircle size={14} className="me-1" /> Valider
          </button>
        </div>
        <canvas ref={canvasRef} className="d-none"></canvas>
      </div>
    );
  }

  return (
    <div className="camera-capture-container text-center border p-3 rounded-4 bg-white">
      {!isActive ? (
        <div className="py-2">
          <button type="button" className="btn btn-primary rounded-pill px-4 shadow-sm" onClick={startCamera}>
            <Camera size={18} className="me-2" /> Démarrer la Caméra
          </button>
        </div>
      ) : (
        <div>
           <div className="position-relative bg-dark rounded overflow-hidden mb-3" style={{ height: '250px' }}>
              <video ref={videoRef} autoPlay playsInline muted className="w-100 h-100" style={{ objectFit: 'cover' }} />
           </div>
           <div className="d-flex justify-content-center gap-3">
              <button type="button" className="btn btn-light text-danger rounded-circle p-3 shadow-sm" onClick={handleCancelClick}>
                 <XCircle size={22} />
              </button>
              <button type="button" className="btn btn-primary rounded-circle p-3 shadow-sm" onClick={capturePhoto}>
                 <Camera size={22} />
              </button>
           </div>
           <canvas ref={canvasRef} className="d-none"></canvas>
        </div>
      )}
    </div>
  );
}
