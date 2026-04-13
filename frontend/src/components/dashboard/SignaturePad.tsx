"use client";

import { useRef, useState, useEffect } from "react";
import SignatureCanvas from "react-signature-canvas";
import { Trash2, CheckCircle } from "lucide-react";

interface SignaturePadProps {
  onSave: (dataUrl: string) => void;
  label: string;
}

export default function SignaturePad({ onSave, label }: SignaturePadProps) {
  const sigCanvas = useRef<SignatureCanvas>(null);
  const [isEmpty, setIsEmpty] = useState(true);

  useEffect(() => {
    // Suppress Canvas2D willReadFrequently Warning by pre-initializing context
    if (sigCanvas.current) {
      const nativeCanvas = sigCanvas.current.getCanvas();
      if (nativeCanvas && typeof nativeCanvas.getContext === "function") {
        nativeCanvas.getContext("2d", { willReadFrequently: true });
      }
    }
  }, []);

  const clear = () => {
    sigCanvas.current?.clear();
    setIsEmpty(true);
  };

  const save = () => {
    if (sigCanvas.current?.isEmpty()) return;
    const dataUrl = sigCanvas.current?.getTrimmedCanvas().toDataURL("image/png");
    if (dataUrl) {
      onSave(dataUrl);
      setIsEmpty(false);
    }
  };

  return (
    <div className="signature-pad-container">
      <label className="form-label small fw-bold text-muted mb-2">{label}</label>
      <div className="border rounded-4 bg-white overflow-hidden" style={{ height: 200 }}>
        <SignatureCanvas 
          ref={sigCanvas}
          canvasProps={{ width: 500, height: 200, className: "sigCanvas w-100 h-100" }}
          onBegin={() => setIsEmpty(false)}
        />
      </div>
      <div className="d-flex justify-content-between mt-2">
        <button type="button" className="btn btn-sm btn-light text-danger rounded-pill" onClick={clear}>
          <Trash2 size={14} className="me-1" /> Effacer
        </button>
        <button type="button" className="btn btn-sm btn-primary rounded-pill" onClick={save}>
          <CheckCircle size={14} className="me-1" /> Confirmer la signature
        </button>
      </div>
    </div>
  );
}
