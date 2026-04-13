"use client";

import { useState, useEffect } from "react";
import { Search, Download, Trash2, Eye, Filter } from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";

export default function AdminAllDemandes() {
  const [demandes, setDemandes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchAllDemandes = async () => {
      try {
        const resp = await apiClient.get("/admin/demandes");
        const data = resp.data.data?.data || resp.data.data || [];
        setDemandes(data);
      } catch (err) {
        console.error("Erreur chargement demandes", err);
      } finally {
        setLoading(false);
      }
    };
    fetchAllDemandes();
  }, []);

  return (
    <div>
      <div className="d-flex justify-content-between align-items-end mb-5">
        <div>
          <h2 className="fw-bold">Toutes les Demandes</h2>
          <p className="text-muted mb-0">Vue globale et supervision de tous les dossiers du système.</p>
        </div>
        <button className="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
          <Download size={18} className="me-2" />
          Exporter CSV
        </button>
      </div>

      <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div className="p-4 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div className="search-box position-relative flex-grow-1" style={{ maxWidth: '400px' }}>
            <Search className="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" size={18} />
            <input 
              type="text" 
              className="form-control border-0 bg-light fs-6 ps-5" 
              placeholder="Rechercher par n'importe quel champ..." 
            />
          </div>
          <button className="btn btn-light d-flex align-items-center gap-2">
            <Filter size={18} />
            <span>Filtrer par Statut</span>
          </button>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="bg-light">
              <tr>
                <th className="px-4 py-3 border-0 small text-uppercase text-muted">Référence</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Citoyen</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Statut</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Dernière Action</th>
                <th className="py-3 border-0 small text-uppercase text-muted text-end px-4">Supervision</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                    <td colSpan={5} className="text-center py-5">
                        <div className="spinner-border spinner-border-sm text-primary"></div>
                    </td>
                </tr>
              ) : demandes.length === 0 ? (
                <tr>
                    <td colSpan={5} className="text-center py-5 text-muted">Aucune donnée disponible.</td>
                </tr>
              ) : demandes.map((d) => (
                <tr key={d.DemandeID}>
                  <td className="px-4 py-4 border-bottom fw-bold">#{d.DemandeID}</td>
                  <td className="py-4 border-bottom">
                    <div className="fw-bold">{d.utilisateur?.Prenom} {d.utilisateur?.Nom}</div>
                  </td>
                  <td className="py-4 border-bottom">
                    <span className={`badge ${d.Statut === 'Validé' ? 'bg-success' : d.Statut === 'Rejeté' ? 'bg-danger' : 'bg-warning text-dark'}`}>
                        {d.Statut}
                    </span>
                  </td>
                  <td className="py-4 border-bottom small text-muted">
                    {d.DateMiseAJour ? new Date(d.DateMiseAJour).toLocaleString() : 'Inconnue'}
                  </td>
                  <td className="py-4 border-bottom text-end px-4">
                    <div className="btn-group">
                        <Link href={`/admin/demandes/${d.DemandeID}`} className="btn btn-sm btn-light p-2 rounded-2 me-1 border" title="Visualiser le dossier complet">
                            <Eye size={16} className="text-primary" />
                        </Link>
                        <button className="btn btn-sm btn-light p-2 rounded-2 border" title="Aucune action de suppression autorisée" disabled>
                            <Trash2 size={16} className="text-muted" />
                        </button>
                    </div>
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
