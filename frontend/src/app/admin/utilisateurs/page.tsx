"use client";

import { useState, useEffect } from "react";
import { Search, UserPlus, MoreVertical, Edit, Trash2, Shield, Loader2 } from "lucide-react";
import apiClient from "@/lib/api-client";

export default function UsersManagement() {
  const [users, setUsers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");

  const fetchUsers = async () => {
    setLoading(true);
    try {
      const resp = await apiClient.get(`/admin/utilisateurs?search=${search}`);
      // Handle Laravel pagination
      const data = resp.data.data?.data || resp.data.data || [];
      setUsers(data);
    } catch (err) {
      console.error("Erreur chargement utilisateurs", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const timer = setTimeout(() => {
        fetchUsers();
    }, 500);
    return () => clearTimeout(timer);
  }, [search]);

  const getRoleBadge = (user: any) => {
    const roleId = user.RoleId;
    const roleName = user.role?.Nom || "Inconnu";
    
    switch(roleId) {
      case 1: return <span className="badge bg-danger">{roleName}</span>;
      case 2: return <span className="badge bg-primary">{roleName}</span>;
      case 3: return <span className="badge bg-success">{roleName}</span>;
      case 4: return <span className="badge bg-warning text-dark">{roleName}</span>;
      default: return <span className="badge bg-secondary">{roleName}</span>;
    }
  };

  return (
    <div>
      <div className="d-flex justify-content-between align-items-end mb-5">
        <div>
          <h2 className="fw-bold">Gestion des Utilisateurs</h2>
          <p className="text-muted mb-0">Administrez les comptes et les permissions d'accès.</p>
        </div>
        <button className="btn btn-primary rounded-pill px-4 shadow-sm" disabled>
          <UserPlus size={18} className="me-2" />
          Nouvel Utilisateur
        </button>
      </div>

      <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div className="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
          <div className="search-box position-relative w-50">
            <Search className="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" size={18} />
            <input 
              type="text" 
              className="form-control form-control-lg border-0 bg-light fs-6 ps-5" 
              placeholder="Rechercher par nom, email..." 
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <div className="d-flex gap-2 text-muted small">
            {loading && <Loader2 size={18} className="animate-spin" />}
            <span>{users.length} utilisateurs trouvés</span>
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="bg-light">
              <tr>
                <th className="px-4 py-3 border-0 small text-uppercase text-muted">Utilisateur</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Rôle</th>
                <th className="py-3 border-0 small text-uppercase text-muted">Statut</th>
                <th className="py-3 border-0 small text-uppercase text-muted text-end px-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading && users.length === 0 ? (
                  <tr><td colSpan={4} className="text-center py-5 text-muted">Chargement de la base de données...</td></tr>
              ) : users.length === 0 ? (
                  <tr><td colSpan={4} className="text-center py-5 text-muted">Aucun utilisateur trouvé.</td></tr>
              ) : users.map((user) => (
                <tr key={user.UtilisateurID}>
                  <td className="px-4 py-4 border-bottom">
                    <div className="d-flex align-items-center">
                      <div className="avatar me-3 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style={{ width: 40, height: 40 }}>
                        {user.Prenom?.[0]}{user.Nom?.[0]}
                      </div>
                      <div>
                        <div className="fw-bold text-dark">{user.Prenom} {user.Nom}</div>
                        <div className="small text-muted">{user.Email}</div>
                      </div>
                    </div>
                  </td>
                  <td className="py-4 border-bottom">
                    {getRoleBadge(user)}
                  </td>
                  <td className="py-4 border-bottom">
                    <span className={`d-flex align-items-center gap-2 small ${user.IsActive ? 'text-success' : 'text-danger'}`}>
                      <span className={`dot ${user.IsActive ? 'bg-success' : 'bg-danger'}`}></span> {user.IsActive ? 'Actif' : 'Inactif'}
                    </span>
                  </td>
                  <td className="py-4 border-bottom text-end px-4">
                    <div className="btn-group">
                      <button className="btn btn-sm btn-light p-2 rounded-2 me-1" title="Modifier">
                        <Edit size={16} className="text-primary" />
                      </button>
                      <button className="btn btn-sm btn-light p-2 rounded-2" title="Gérer l'accès">
                        <Shield size={16} className="text-danger" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <style jsx>{`
        .dot {
          width: 8px;
          height: 8px;
          border-radius: 50%;
          display: inline-block;
        }
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
      `}</style>
    </div>
  );
}
