import { useRef, useState, useEffect } from "react";
import SignatureCanvas from "react-signature-canvas";
import { PenTool, Eraser, Check, X, Loader2 } from "lucide-react";
import apiClient from "@/lib/api-client";

interface SignatureModalProps {
  show: boolean;
  onClose: () => void;
  apiEndpoint: string;
  onSuccess: () => void;
  title?: string;
  description?: string;
}

export default function SignatureModal({
  show,
  onClose,
  apiEndpoint,
  onSuccess,
  title = "Apposer votre signature",
  description = "Veuillez dessiner votre signature ci-dessous pour validation numérique."
}: SignatureModalProps) {
  const sigCanvas = useRef<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    if (show && sigCanvas.current) {
      const nativeCanvas = sigCanvas.current.getCanvas();
      if (nativeCanvas && typeof nativeCanvas.getContext === "function") {
        nativeCanvas.getContext("2d", { willReadFrequently: true });
      }
    }
  }, [show]);

  if (!show) return null;

  const clear = () => {
    sigCanvas.current?.clear();
    setError("");
  };

  const save = async () => {
    if (sigCanvas.current?.isEmpty()) {
      setError("Veuillez dessiner une signature avant de valider.");
      return;
    }

    setLoading(true);
    setError("");
    try {
      // Get base64 string from canvas (it's data:image/png;base64,...)
      const signatureData = sigCanvas.current?.getTrimmedCanvas().toDataURL("image/png");
      
      await apiClient.post(apiEndpoint, { signature: signatureData });
      onSuccess();
    } catch (err: any) {
      console.error("Erreur d'enregistrement de la signature", err);
      setError(err.response?.data?.message || "Une erreur est survenue lors de l'enregistrement de votre signature.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <div className="modal-backdrop fade show" style={{ zIndex: 1050 }}></div>
      <div className="modal fade show d-block" tabIndex={-1} style={{ zIndex: 1055 }}>
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content border-0 shadow-lg rounded-4">
            <div className="modal-header border-bottom-0 pb-0">
              <h5 className="modal-title fw-bold d-flex align-items-center gap-2">
                <PenTool size={20} className="text-primary" /> {title}
              </h5>
              <button 
                type="button" 
                className="btn-close" 
                onClick={onClose}
                disabled={loading}
              ></button>
            </div>
            
            <div className="modal-body">
              <p className="text-muted small mb-4">{description}</p>
              
              {error && <div className="alert alert-danger py-2 small">{error}</div>}
              
              <div className="border rounded-4 overflow-hidden mb-3 bg-light d-flex justify-content-center" style={{ minHeight: "200px" }}>
                <SignatureCanvas 
                  ref={sigCanvas}
                  canvasProps={{
                    className: "signature-canvas w-100",
                    height: 200
                  }}
                  penColor="#000"
                  backgroundColor="rgba(255,255,255,0)"
                />
              </div>

              <div className="d-flex justify-content-end gap-2">
                <button 
                    className="btn btn-outline-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-1"
                    onClick={clear}
                    disabled={loading}
                >
                    <Eraser size={14} /> Recommencer
                </button>
              </div>
            </div>
            
            <div className="modal-footer border-top-0 pt-0">
              <button 
                type="button" 
                className="btn btn-light rounded-pill px-4" 
                onClick={onClose}
                disabled={loading}
              >
                <X size={16} className="me-2" /> Annuler
              </button>
              <button 
                type="button" 
                className="btn btn-primary rounded-pill px-4 d-flex align-items-center" 
                onClick={save}
                disabled={loading}
              >
                {loading ? <Loader2 size={16} className="animate-spin me-2" /> : <Check size={16} className="me-2" />}
                Valider
              </button>
            </div>
          </div>
        </div>
      </div>
      <style jsx>{`
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
      `}</style>
    </>
  );
}
