"use client";

import { useState, useEffect } from "react";
import { Search, Filter, Eye, CheckCircle, XCircle, Clock } from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";

export default function OfficerDemandes() {
  const [demandes, setDemandes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDemandes = async () => {
      try {
        const resp = await apiClient.get("/officier/demandes"); // Backend route for listing
        const data = resp.data.data?.data || resp.data.data || [];
        setDemandes(data);
      } catch (err) {
        console.error("Erreur chargement demandes", err);
      } finally {
        setLoading(false);
      }
    };
    fetchDemandes();
  }, []);

  const getStatusBadge = (status: string) => {
    switch(status?.toLowerCase()) {
      case 'validé': return <span className="badge bg-success"><CheckCircle size={12} className="me-1"/> Validée</span>;
      case 'rejeté': return <span className="badge bg-danger"><XCircle size={12} className="me-1"/> Rejetée</span>;
      case 'en attente': return <span className="badge bg-warning text-dark"><Clock size={12} className="me-1"/> En attente</span>;
      default: return <span className="badge bg-secondary">{status}</span>;
    }
  };

  return (
    <div>
      <div className="mb-5">
        <h2 className="fw-bold">Gestion des Dossiers CNI</h2>
        <p className="text-muted">Consultez et validez les demandes soumises par les citoyens.</p>
      </div>

      <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div className="p-4 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div className="search-box position-relative flex-grow-1" style={{ maxWidth: '400px' }}>
            <Search className="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" size={18} />
            <input 
              type="text" 
              className="form-control border-0 bg-light fs-6 ps-5" 
              placeholder="Rechercher par nom ou numéro..." 
            />
          </div>
          <button className="btn btn-light d-flex align-items-center gap-2">
            <Filter size={18} />
            <span>Filtres</span>
          </button>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="bg-light">
              <tr>
                <th className="px-4 py-3 border-0 small text-uppercase text-muted">ID Dossier</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Citoyen</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Type</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Date</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Statut</th>
                <th className="py-3 border-0 small text-uppercase text-muted text-end px-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                    <td colSpan={6} className="text-center py-5">
                        <div className="spinner-border spinner-border-sm text-primary me-2"></div>
                        Chargement des dossiers...
                    </td>
                </tr>
              ) : demandes.length === 0 ? (
                <tr>
                    <td colSpan={6} className="text-center py-5 text-muted">Aucun dossier trouvé.</td>
                </tr>
              ) : demandes.map((d) => (
                <tr key={d.DemandeID}>
                  <td className="px-4 py-4 border-bottom fw-bold">#{d.DemandeID}</td>
                  <td className="py-4 border-bottom">
                    <div className="fw-bold">{d.utilisateur?.Prenom} {d.utilisateur?.Nom}</div>
                    <div className="small text-muted">{d.utilisateur?.Email}</div>
                  </td>
                  <td className="py-4 border-bottom text-uppercase small">{d.TypeDemande || 'Standard'}</td>
                  <td className="py-4 border-bottom small">{new Date(d.DateCreation).toLocaleDateString()}</td>
                  <td className="py-4 border-bottom">
                    {getStatusBadge(d.Statut)}
                  </td>
                  <td className="py-4 border-bottom text-end px-4">
                    <Link href={`/officier/demandes/${d.DemandeID}`} className="btn btn-sm btn-outline-primary rounded-pill px-3">
                      <Eye size={14} className="me-1" /> Détails
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
