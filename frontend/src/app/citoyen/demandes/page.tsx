"use client";

import { useState, useEffect } from "react";
import { Plus, Clock, CheckCircle, XCircle, FileSearch } from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";

export default function CitizenDemandes() {
  const [demandes, setDemandes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchMyDemandes = async () => {
      try {
        const resp = await apiClient.get("/citoyen/demandes");
        // Handle Laravel paginated response structure
        const data = resp.data.data?.data || resp.data.data || [];
        setDemandes(data);
      } catch (err) {
        console.error("Erreur chargement demandes", err);
      } finally {
        setLoading(false);
      }
    };
    fetchMyDemandes();
  }, []);

  const getStatusBadge = (status: string) => {
    switch(status?.toLowerCase()) {
      case 'validé': case 'approuvée': return <span className="badge bg-success"><CheckCircle size={12} className="me-1"/> Validée</span>;
      case 'rejeté': case 'rejetée': return <span className="badge bg-danger"><XCircle size={12} className="me-1"/> Rejetée</span>;
      default: return <span className="badge bg-warning text-dark"><Clock size={12} className="me-1"/> En cours</span>;
    }
  };

  return (
    <div>
      <div className="d-flex justify-content-between align-items-end mb-5">
        <div>
          <h2 className="fw-bold">Mes Demandes</h2>
          <p className="text-muted mb-0">Suivez l'état d'avancement de vos demandes de CNI.</p>
        </div>
        <Link href="/citoyen/demandes/nouvelle" className="btn btn-primary rounded-pill px-4 shadow-sm">
          <Plus size={18} className="me-2" />
          Nouvelle Demande
        </Link>
      </div>

      {loading ? (
        <div className="text-center py-5">
            <div className="spinner-border text-primary"></div>
            <p className="mt-3 text-muted">Chargement de vos dossiers...</p>
        </div>
      ) : demandes.length === 0 ? (
        <div className="card border-0 shadow-sm rounded-4 p-5 text-center">
            <FileSearch size={48} className="text-muted mb-3 mx-auto" />
            <h5 className="fw-bold">Aucune demande trouvée</h5>
            <p className="text-muted">Vous n'avez pas encore soumis de demande de Carte National d'Identité.</p>
            <Link href="/citoyen/demandes/nouvelle" className="btn btn-outline-primary rounded-pill mt-3">
                Commencer ma première demande
            </Link>
        </div>
      ) : (
        <div className="row g-4">
            {demandes.map((d) => (
                <div key={d.DemandeID} className="col-md-6 col-lg-4">
                    <div className="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift transition-all">
                        <div className="d-flex justify-content-between align-items-start mb-3">
                            <span className="fw-bold text-primary fs-5">#{d.NumeroReference || d.DemandeID}</span>
                            {getStatusBadge(d.Statut)}
                        </div>
                        <h6 className="fw-bold mb-1">{d.TypeDemande || 'Demande de CNI'}</h6>
                        <p className="small text-muted mb-4">Soumise le {d.DateSoumission ? new Date(d.DateSoumission).toLocaleDateString() : 'Date inconnue'}</p>
                        
                        <div className="mt-auto pt-3 border-top">
                            <Link href={`/citoyen/demandes/${d.DemandeID}`} className="btn btn-sm btn-light w-100 rounded-pill">
                                Voir les détails
                            </Link>
                        </div>
                    </div>
                </div>
            ))}
        </div>
      )}

      <style jsx>{`
        .transition-all { transition: all 0.2s ease; }
        .hover-lift:hover { transform: translateY(-5px); }
      `}</style>
    </div>
  );
}
