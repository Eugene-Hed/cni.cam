"use client";

import { useState, useEffect } from "react";
import { Search, PenLine, CheckSquare, Clock, MapPin } from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";

export default function PresidentDemandes() {
  const [demandes, setDemandes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDemandes = async () => {
      try {
        // Fetch only those waiting for national signature
        const resp = await apiClient.get("/president/demandes?statut=En+attente+de+signature");
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

  return (
    <div>
      <div className="mb-5">
        <h2 className="fw-bold">Signature des Certificats</h2>
        <p className="text-muted">Dossiers de nationalité validés en attente de signature présidentielle.</p>
      </div>

      <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div className="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
          <div className="search-box position-relative flex-grow-1" style={{ maxWidth: '400px' }}>
            <Search className="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" size={18} />
            <input 
              type="text" 
              className="form-control border-0 bg-light fs-6 ps-5" 
              placeholder="Rechercher par citoyen..." 
            />
          </div>
          <div className="d-flex gap-2">
            <button className="btn btn-outline-primary btn-sm rounded-pill">Signer la sélection</button>
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="bg-light">
              <tr>
                <th className="px-4 py-3 border-0 small text-uppercase text-muted" style={{ width: 40 }}>
                    <input type="checkbox" className="form-check-input" />
                </th>
                <th className="py-3 border-0 small text-uppercase text-muted">Citoyen</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Région</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Attente</th>
                <th className="py-3 border-0 small text-uppercase text-muted text-end px-4">Actions</th>
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
                    <td colSpan={5} className="text-center py-5">
                        <CheckSquare size={32} className="text-success mb-2 mx-auto" />
                        <div className="text-muted">Tous les dossiers sont à jour.</div>
                    </td>
                </tr>
              ) : demandes.map((d) => (
                <tr key={d.DemandeID}>
                  <td className="px-4 py-4 border-bottom">
                    <input type="checkbox" className="form-check-input" />
                  </td>
                  <td className="py-4 border-bottom">
                    <div className="fw-bold">{d.utilisateur?.Prenom} {d.utilisateur?.Nom}</div>
                    <div className="small text-muted">{d.utilisateur?.Email}</div>
                  </td>
                  <td className="py-4 border-bottom small">
                    <MapPin size={14} className="me-1 text-muted" /> {d.utilisateur?.region_naissance?.Nom || 'N/A'}
                  </td>
                  <td className="py-4 border-bottom">
                    <span className="text-warning small fw-medium">
                        <Clock size={14} className="me-1" /> 2 jours
                    </span>
                  </td>
                  <td className="py-4 border-bottom text-end px-4">
                    <Link href={`/president/demandes/${d.DemandeID}`} className="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                      <PenLine size={14} className="me-1" /> Signer
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
