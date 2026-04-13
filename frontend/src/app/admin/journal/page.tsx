"use client";

import { useState, useEffect } from "react";
import { History, ShieldAlert, LogIn, Save, Trash2, Search, Loader2 } from "lucide-react";
import apiClient from "@/lib/api-client";

export default function AuditLogs() {
  const [logs, setLogs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");

  const fetchLogs = async () => {
    setLoading(true);
    try {
      const resp = await apiClient.get("/admin/journal");
      // Handle Laravel pagination
      const data = resp.data.data?.data || resp.data.data || [];
      setLogs(data);
    } catch (err) {
      console.error("Erreur chargement logs", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLogs();
  }, []);

  const getLogIcon = (type: string) => {
    if (type.includes("Connexion") || type.includes("Login")) return <LogIn size={16} className="text-primary" />;
    if (type.includes("Validation") || type.includes("Traitement") || type.includes("CHGT_STATUT")) return <Save size={16} className="text-success" />;
    if (type.includes("Alerte") || type.includes("Erreur")) return <ShieldAlert size={16} className="text-danger" />;
    if (type.includes("Desactivation") || type.includes("Suppression")) return <Trash2 size={16} className="text-danger" />;
    return <History size={16} className="text-muted" />;
  };

  // Basic filtering for the UI
  const filteredLogs = logs.filter(log => 
    log.TypeActivite?.toLowerCase().includes(search.toLowerCase()) ||
    log.Description?.toLowerCase().includes(search.toLowerCase()) ||
    (log.utilisateur?.Prenom + " " + log.utilisateur?.Nom).toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div>
      <div className="mb-5 d-flex justify-content-between align-items-end">
        <div>
            <h2 className="fw-bold">Journal d'Activités</h2>
            <p className="text-muted mb-0">Historique complet des actions effectuées par les utilisateurs et les systèmes.</p>
        </div>
        <button className="btn btn-sm btn-outline-secondary rounded-pill px-3" onClick={fetchLogs} disabled={loading}>
            Actualiser
        </button>
      </div>

      <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div className="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
          <div className="search-box position-relative flex-grow-1" style={{ maxWidth: '400px' }}>
            <Search className="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" size={18} />
            <input 
              type="text" 
              className="form-control border-0 bg-light fs-6 ps-5" 
              placeholder="Rechercher dans les logs (action, auteur...)" 
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <div className="ms-3 text-muted small">
              {loading ? <Loader2 size={18} className="animate-spin" /> : `${filteredLogs.length} logs affichés`}
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-sm table-hover align-middle mb-0">
            <thead className="bg-light">
              <tr>
                <th className="px-4 py-3 border-0 small text-uppercase text-muted">Action</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Auteur</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Adresse IP</th>
                <th className="py-3 border-0 small text-uppercase text-muted px-4 text-end">Date & Heure</th>
              </tr>
            </thead>
            <tbody>
              {loading && logs.length === 0 ? (
                  <tr><td colSpan={4} className="text-center py-5 text-muted small">Interrogation du journal système...</td></tr>
              ) : filteredLogs.length === 0 ? (
                  <tr><td colSpan={4} className="text-center py-5 text-muted small">Aucun événement ne correspond à votre recherche.</td></tr>
              ) : filteredLogs.map((log) => (
                <tr key={log.LogID}>
                  <td className="px-4 py-3 border-bottom">
                    <div className="d-flex align-items-center gap-2">
                        {getLogIcon(log.TypeActivite)}
                        <span className="fw-medium small text-dark">{log.Description}</span>
                    </div>
                  </td>
                  <td className="py-3 border-bottom small">
                      <div className="fw-bold">{log.utilisateur?.Prenom} {log.utilisateur?.Nom}</div>
                      <div className="x-small text-muted">{log.utilisateur?.Email || 'Automate'}</div>
                  </td>
                  <td className="py-3 border-bottom font-monospace x-small text-muted text-uppercase">{log.AdresseIP || 'Interne'}</td>
                  <td className="py-3 border-bottom text-end px-4 small text-muted">
                    {new Date(log.DateHeure).toLocaleString('fr-FR')}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
      
      <div className="mt-4 text-center">
          <button className="btn btn-link text-muted small text-decoration-none">Charger l'historique plus ancien</button>
      </div>

      <style jsx>{`
        .x-small { font-size: 0.7rem; }
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .font-monospace { font-family: 'Courier New', Courier, monospace; }
      `}</style>
    </div>
  );
}
