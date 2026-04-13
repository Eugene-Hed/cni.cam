"use client";

import { useState } from "react";
import { QrCode, Download, Eye, ShieldCheck, Printer } from "lucide-react";

export default function CitizenDocuments() {
  const [documents, setDocuments] = useState([
    { id: 1, name: "Demande de CNI #124", type: "PDF", date: "11/04/2026", status: "Signée" },
    { id: 2, name: "Certificat de Résidence", type: "Image", date: "10/04/2026", status: "Reçu" }
  ]);

  return (
    <div>
      <div className="mb-5">
        <h2 className="fw-bold">Mes Documents Numériques</h2>
        <p className="text-muted">Accédez à vos récépissés et copies numériques signées.</p>
      </div>

      <div className="row g-4">
        {documents.map((doc) => (
          <div key={doc.id} className="col-md-6 col-lg-4">
            <div className="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift transition-all">
              <div className="p-4 bg-primary text-white text-center position-relative overflow-hidden">
                <QrCode size={120} className="position-absolute translate-middle top-50 start-50 opacity-10" />
                <div className="position-relative">
                   <QrCode size={64} className="mb-3" />
                   <h5 className="fw-bold mb-0">{doc.name}</h5>
                </div>
              </div>
              <div className="p-4 bg-white">
                <div className="d-flex justify-content-between align-items-center mb-4">
                  <span className="small text-muted">{doc.date}</span>
                  <span className="badge bg-success-light text-success font-monospace small">
                    <ShieldCheck size={14} className="me-1" /> VALIDE
                  </span>
                </div>
                
                <div className="d-flex gap-2">
                   <button className="btn btn-primary rounded-pill flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                        <Download size={16} /> Télécharger
                   </button>
                   <button className="btn btn-light rounded-circle p-2 border" title="Imprimer">
                        <Printer size={18} />
                   </button>
                </div>
              </div>
            </div>
          </div>
        ))}

        <div className="col-md-6 col-lg-4">
            <div className="card h-100 border-2 border-dashed rounded-4 p-5 d-flex align-items-center justify-content-center text-center text-muted">
                <div className="opacity-50">
                    <Eye size={48} className="mb-3 mx-auto" strokeWidth={1} />
                    <p className="small mb-0">Les nouveaux documents <br />apparaîtront ici.</p>
                </div>
            </div>
        </div>
      </div>

      <style jsx>{`
        .transition-all { transition: all 0.2s ease; }
        .hover-lift:hover { transform: translateY(-5px); }
        .bg-success-light { background-color: #e6fcf5; }
        .border-dashed { border-style: dashed !important; }
      `}</style>
    </div>
  );
}
