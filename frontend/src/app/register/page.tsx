"use client";

import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import apiClient from "@/lib/api-client";
import { useRouter } from "next/navigation";

export default function RegisterPage() {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    nom: "", prenom: "", date_naissance: "", genre: "M",
    email: "", telephone: "", profession: "", adresse: "",
    region_naissance_id: "", departement_naissance_id: "", ville_naissance_id: "",
    region_residence_id: "", departement_residence_id: "", ville_residence_id: "",
    ethnie_id: ""
  });

  const [regions, setRegions] = useState<any[]>([]);
  const [ethnies, setEthnies] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const router = useRouter();

  useEffect(() => {
    // Fetch initial data
    const fetchRefData = async () => {
        try {
            const [regResp, ethResp] = await Promise.all([
                apiClient.get("/geo/regions"),
                apiClient.get("/geo/ethnies")
            ]);
            setRegions(regResp.data.data);
            setEthnies(ethResp.data.data);
        } catch (err) { console.error(err); }
    };
    fetchRefData();
  }, []);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const nextStep = () => setStep(step + 1);
  const prevStep = () => setStep(step - 1);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    try {
        await apiClient.post("/auth/register", formData);
        router.push("/login?registered=success");
    } catch (err: any) {
        setError(err.response?.data?.message || "Une erreur est survenue lors de l'inscription.");
    } finally {
        setLoading(false);
    }
  };

  const progress = (step / 4) * 100;

  return (
    <div className="register-container min-vh-100 py-3 py-md-5">
      <div className="container">
        <motion.div 
            className="register-card bg-white rounded-4 shadow-lg mx-auto overflow-hidden"
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
        >
            <div className="register-header text-center p-4 bg-light">
                <div className="progress mb-4" style={{ height: 6 }}>
                    <div className="progress-bar transition-all" style={{ width: `${progress}%` }}></div>
                </div>
                <Image src="/assets/images/Cameroun.png" alt="Logo" width={60} height={60} className="mb-3" />
                <h2 className="fw-bold mb-1">Créer un compte</h2>
                <p className="text-muted small">Étape {step} sur 4</p>
            </div>

            <div className="p-4 p-md-5">
                {error && <div className="alert alert-danger small mb-4">{error}</div>}

                <form onSubmit={step === 4 ? handleSubmit : (e) => e.preventDefault()}>
                    <AnimatePresence mode="wait">
                        {step === 1 && (
                            <motion.div key="1" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }}>
                                <h4 className="mb-4">Informations Personnelles</h4>
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Nom</label>
                                        <input type="text" name="nom" className="form-control" value={formData.nom} onChange={handleChange} required />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Prénom</label>
                                        <input type="text" name="prenom" className="form-control" value={formData.prenom} onChange={handleChange} required />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Date de Naissance</label>
                                        <input type="date" name="date_naissance" className="form-control" value={formData.date_naissance} onChange={handleChange} required />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Sexe</label>
                                        <select name="genre" className="form-select" value={formData.genre} onChange={handleChange}>
                                            <option value="M">Masculin</option>
                                            <option value="F">Féminin</option>
                                        </select>
                                    </div>
                                </div>
                            </motion.div>
                        )}

                        {step === 2 && (
                            <motion.div key="2" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }}>
                                <h4 className="mb-4">Lieu de Naissance</h4>
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Région de Naissance</label>
                                        <select name="region_naissance_id" className="form-select" value={formData.region_naissance_id} onChange={handleChange} required>
                                            <option value="">Sélectionner</option>
                                            {regions.map(r => <option key={r.RegionID} value={r.RegionID}>{r.NomRegion}</option>)}
                                        </select>
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Lieu exact (Ville/Village)</label>
                                        <input type="text" name="ville_naissance_id" className="form-control" placeholder="Ex: Yaoundé" value={formData.ville_naissance_id} onChange={handleChange} required />
                                    </div>
                                </div>
                            </motion.div>
                        )}

                        {step === 3 && (
                            <motion.div key="3" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }}>
                                <h4 className="mb-4">Résidence & Profil</h4>
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Région de Résidence</label>
                                        <select name="region_residence_id" className="form-select" value={formData.region_residence_id} onChange={handleChange} required>
                                            <option value="">Sélectionner</option>
                                            {regions.map(r => <option key={r.RegionID} value={r.RegionID}>{r.NomRegion}</option>)}
                                        </select>
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Adresse Complète</label>
                                        <input type="text" name="adresse" className="form-control" placeholder="Quartier, Rue, etc." value={formData.adresse} onChange={handleChange} required />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Ethnie</label>
                                        <select name="ethnie_id" className="form-select" value={formData.ethnie_id} onChange={handleChange} required>
                                            <option value="">Sélectionner</option>
                                            {ethnies.map(e => <option key={e.EthnieID} value={e.EthnieID}>{e.NomEthnie}</option>)}
                                        </select>
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Profession</label>
                                        <input type="text" name="profession" className="form-control" value={formData.profession} onChange={handleChange} required />
                                    </div>
                                </div>
                            </motion.div>
                        )}

                        {step === 4 && (
                            <motion.div key="4" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }}>
                                <h4 className="mb-4">Contact & Sécurité</h4>
                                <div className="row g-3">
                                    <div className="col-md-12">
                                        <label className="form-label small fw-bold">Adresse Email</label>
                                        <input type="email" name="email" className="form-control form-control-lg fs-6" placeholder="votre@email.com" value={formData.email} onChange={handleChange} required />
                                        <div className="form-text">Note : Cette adresse servira à recevoir vos codes OTP.</div>
                                    </div>
                                    <div className="col-md-12">
                                        <label className="form-label small fw-bold">Numéro de Téléphone</label>
                                        <div className="input-group">
                                            <span className="input-group-text bg-light">+237</span>
                                            <input type="text" name="telephone" className="form-control form-control-lg fs-6" placeholder="6XXXXXXXX" value={formData.telephone} onChange={handleChange} required />
                                        </div>
                                    </div>
                                </div>
                                <div className="alert alert-info mt-4 small">
                                    <i className="bi bi-info-circle me-2"></i>
                                    En cliquant sur "Terminer", vous acceptez nos conditions d'utilisation et certifiez l'exactitude des informations fournies.
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>

                    <div className="d-flex justify-content-between mt-5">
                        {step > 1 && <button type="button" className="btn btn-light rounded-pill px-4" onClick={prevStep}>Retour</button>}
                        <div className="ms-auto">
                            {step < 4 ? (
                                <button type="button" className="btn btn-primary rounded-pill px-5" onClick={nextStep}>Suivant</button>
                            ) : (
                                <button type="submit" className="btn btn-success rounded-pill px-5" disabled={loading}>
                                    {loading ? "Création..." : "Terminer l'inscription"}
                                </button>
                            )}
                        </div>
                    </div>
                </form>
            </div>
        </motion.div>
      </div>

      <style jsx>{`
        .register-container {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }
        .register-card { max-width: 800px; }
        .transition-all { transition: all 0.3s ease; }
      `}</style>
    </div>
  );
}
