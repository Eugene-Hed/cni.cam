"use client";

import { useState, useEffect } from "react";
import { User, Mail, Phone, MapPin, Briefcase, Save, Camera } from "lucide-react";
import apiClient from "@/lib/api-client";

export default function ProfilePage() {
  const [user, setUser] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState({ type: "", text: "" });

  const [formData, setFormData] = useState({
    prenom: "",
    nom: "",
    telephone: "",
    adresse: "",
    profession: ""
  });

  useEffect(() => {
    const fetchProfile = async () => {
      try {
        const resp = await apiClient.get("/user/profile");
        const userData = resp.data.user;
        setUser(userData);
        setFormData({
          prenom: userData.Prenom || "",
          nom: userData.Nom || "",
          telephone: userData.NumeroTelephone || "",
          adresse: userData.Adresse || "",
          profession: userData.Profession || ""
        });
      } catch (err) {
        console.error("Erreur chargement profil", err);
      } finally {
        setLoading(false);
      }
    };
    fetchProfile();
  }, []);

  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setMessage({ type: "", text: "" });

    try {
      await apiClient.put("/user/profile", formData);
      setMessage({ type: "success", text: "Profil mis à jour avec succès !" });
      
      // Update local storage
      const stored = JSON.parse(localStorage.getItem("user") || "{}");
      const newUser = { ...stored, ...formData, Prenom: formData.prenom, Nom: formData.nom, NumeroTelephone: formData.telephone };
      localStorage.setItem("user", JSON.stringify(newUser));
    } catch (err: any) {
      setMessage({ type: "danger", text: err.response?.data?.message || "Erreur lors de la mise à jour." });
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div className="p-5 text-center"><div className="spinner-border text-primary"></div></div>;

  return (
    <div className="max-w-4xl mx-auto">
      <div className="mb-5">
        <h2 className="fw-bold">Mon Profil</h2>
        <p className="text-muted">Gérez vos informations personnelles et vos paramètres de compte.</p>
      </div>

      <div className="row g-4">
        <div className="col-lg-4">
          <div className="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
            <div className="position-relative d-inline-block mx-auto mb-4">
                <div className="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-1 shadow" style={{ width: 120, height: 120 }}>
                    {user?.Prenom?.[0] || "U"}
                </div>
                <button className="btn btn-sm btn-light rounded-circle position-absolute bottom-0 end-0 p-2 shadow-sm border">
                    <Camera size={16} className="text-primary" />
                </button>
            </div>
            <h4 className="fw-bold mb-1">{user?.Prenom} {user?.Nom}</h4>
            <p className="text-muted small mb-3">{user?.Codeutilisateur}</p>
            <span className="badge bg-primary-light text-primary rounded-pill px-3 py-2">
                {user?.role?.role || "Utilisateur"}
            </span>
          </div>
        </div>

        <div className="col-lg-8">
          <div className="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            {message.text && (
                <div className={`alert alert-${message.type} small mb-4`}>{message.text}</div>
            )}

            <form onSubmit={handleUpdate}>
                <div className="row g-3">
                    <div className="col-md-6 mb-3">
                        <label className="form-label small fw-bold">Prénom</label>
                        <div className="input-group">
                            <span className="input-group-text bg-light border-0"><User size={16} /></span>
                            <input 
                                type="text" 
                                className="form-control bg-light border-0" 
                                value={formData.prenom}
                                onChange={(e) => setFormData({...formData, prenom: e.target.value})}
                            />
                        </div>
                    </div>
                    <div className="col-md-6 mb-3">
                        <label className="form-label small fw-bold">Nom</label>
                        <div className="input-group">
                            <span className="input-group-text bg-light border-0"><User size={16} /></span>
                            <input 
                                type="text" 
                                className="form-control bg-light border-0" 
                                value={formData.nom}
                                onChange={(e) => setFormData({...formData, nom: e.target.value})}
                            />
                        </div>
                    </div>
                    <div className="col-md-6 mb-3">
                        <label className="form-label small fw-bold">Email</label>
                        <div className="input-group">
                            <span className="input-group-text bg-light border-0"><Mail size={16} /></span>
                            <input type="email" className="form-control bg-light border-0 text-muted" value={user?.Email} disabled />
                        </div>
                        <div className="form-text x-small text-danger">L'email ne peut pas être modifié.</div>
                    </div>
                    <div className="col-md-6 mb-3">
                        <label className="form-label small fw-bold">Téléphone</label>
                        <div className="input-group">
                            <span className="input-group-text bg-light border-0"><Phone size={16} /></span>
                            <input 
                                type="text" 
                                className="form-control bg-light border-0" 
                                value={formData.telephone}
                                onChange={(e) => setFormData({...formData, telephone: e.target.value})}
                            />
                        </div>
                    </div>
                    <div className="col-12 mb-3">
                        <label className="form-label small fw-bold">Adresse</label>
                        <div className="input-group">
                            <span className="input-group-text bg-light border-0"><MapPin size={16} /></span>
                            <input 
                                type="text" 
                                className="form-control bg-light border-0" 
                                value={formData.adresse}
                                onChange={(e) => setFormData({...formData, adresse: e.target.value})}
                            />
                        </div>
                    </div>
                    <div className="col-12 mb-4">
                        <label className="form-label small fw-bold">Profession</label>
                        <div className="input-group">
                            <span className="input-group-text bg-light border-0"><Briefcase size={16} /></span>
                            <input 
                                type="text" 
                                className="form-control bg-light border-0" 
                                value={formData.profession}
                                onChange={(e) => setFormData({...formData, profession: e.target.value})}
                            />
                        </div>
                    </div>
                </div>

                <div className="text-end">
                    <button type="submit" className="btn btn-primary rounded-pill px-5 shadow-sm" disabled={saving}>
                        {saving ? <span className="spinner-border spinner-border-sm me-2"></span> : <Save size={18} className="me-2" />}
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
          </div>
        </div>
      </div>

      <style jsx>{`
        .bg-primary-light { background-color: #e7f1ff; }
        .x-small { font-size: 0.7rem; }
      `}</style>
    </div>
  );
}
