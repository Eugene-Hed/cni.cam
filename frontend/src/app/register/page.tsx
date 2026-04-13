"use client";

import { useState, useEffect } from "react";
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
  const [similarAccount, setSimilarAccount] = useState<any>(null);
  const [success, setSuccess] = useState(false);
  const router = useRouter();

  // Dynamic Geographic State
  const [departementsNaissance, setDepartementsNaissance] = useState<any[]>([]);
  const [villesNaissance, setVillesNaissance] = useState<any[]>([]);
  const [departementsResidence, setDepartementsResidence] = useState<any[]>([]);
  const [villesResidence, setVillesResidence] = useState<any[]>([]);

  useEffect(() => {
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

  useEffect(() => {
    if (formData.region_naissance_id) {
        apiClient.get(`/geo/regions/${formData.region_naissance_id}/departements`)
            .then(res => setDepartementsNaissance(res.data.data))
            .catch(console.error);
    } else { setDepartementsNaissance([]); }
  }, [formData.region_naissance_id]);

  useEffect(() => {
    if (formData.departement_naissance_id) {
        apiClient.get(`/geo/departements/${formData.departement_naissance_id}/villes`)
            .then(res => setVillesNaissance(res.data.data))
            .catch(console.error);
    } else { setVillesNaissance([]); }
  }, [formData.departement_naissance_id]);

  useEffect(() => {
    if (formData.region_residence_id) {
        apiClient.get(`/geo/regions/${formData.region_residence_id}/departements`)
            .then(res => setDepartementsResidence(res.data.data))
            .catch(console.error);
    } else { setDepartementsResidence([]); }
  }, [formData.region_residence_id]);

  useEffect(() => {
    if (formData.departement_residence_id) {
        apiClient.get(`/geo/departements/${formData.departement_residence_id}/villes`)
            .then(res => setVillesResidence(res.data.data))
            .catch(console.error);
    } else { setVillesResidence([]); }
  }, [formData.departement_residence_id]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const validateStep = () => {
    setError("");
    if (step === 1) {
        if (!formData.nom || !formData.prenom || !formData.date_naissance || !formData.genre) {
            setError("Veuillez remplir tous les champs personnels.");
            return false;
        }
        const age = new Date().getFullYear() - new Date(formData.date_naissance).getFullYear();
        if (age < 18) {
            setError("Vous devez avoir au moins 18 ans pour vous inscrire.");
            return false;
        }
    }
    if (step === 2) {
        if (!formData.region_naissance_id || !formData.departement_naissance_id || !formData.ville_naissance_id) {
            setError("Veuillez remplir la région, le département et la ville de naissance.");
            return false;
        }
    }
    if (step === 3) {
        if (!formData.region_residence_id || !formData.departement_residence_id || !formData.ville_residence_id || !formData.adresse || !formData.ethnie_id || !formData.profession) {
            setError("Veuillez fournir toutes les informations de résidence et de profil.");
            return false;
        }
    }
    return true;
  };

  const nextStep = () => {
    if (validateStep()) setStep(step + 1);
  };
  const prevStep = () => setStep(step - 1);

  const handleSubmit = async (e?: React.FormEvent, forceCreation: boolean = false) => {
    if (e) e.preventDefault();
    if (!forceCreation) {
        if (!validateStep()) return;
        if (!formData.email || !formData.telephone) {
            setError("Veuillez renseigner votre email et numéro de téléphone.");
            return;
        }
    }
    
    setLoading(true);
    setError("");
    try {
        await apiClient.post("/auth/register", { ...formData, force_creation: forceCreation });
        setSuccess(true);
        setTimeout(() => {
            router.push("/");
        }, 5000);
    } catch (err: any) {
        if (err.response?.status === 409 && err.response?.data?.is_similar) {
            setSimilarAccount(err.response.data);
            setStep(5);
        } else {
            if(err.response?.status === 422 && err.response?.data?.errors) {
                const msgs = Object.values(err.response.data.errors).flat().join(" ");
                setError(msgs);
            } else {
                setError(err.response?.data?.message || "Une erreur est survenue lors de l'inscription.");
            }
        }
    } finally {
        setLoading(false);
    }
  };

  if (success) {
      return (
          <div className="register-container bg-white min-vh-100 py-3 py-md-5 d-flex align-items-center">
              <div className="register-card p-5 text-center mx-auto" style={{maxWidth: '600px', width: '100%'}}>
                  <div className="success-animation mb-4">
                      <svg className="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" style={{width: '100px', height: '100px', borderRadius: '50%', display: 'block', strokeWidth: '2', stroke: '#fff', strokeMiterlimit: '10', margin: '0 auto', boxShadow: 'inset 0px 0px 0px #28a745', animation: 'fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both'}}>
                          <circle className="checkmark__circle" cx="26" cy="26" r="25" fill="none" style={{strokeDasharray: '166', strokeDashoffset: '166', strokeWidth: '2', strokeMiterlimit: '10', stroke: '#28a745', fill: 'none', animation: 'stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards'}} />
                          <path className="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" style={{transformOrigin: '50% 50%', strokeDasharray: '48', strokeDashoffset: '48', animation: 'stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards'}} />
                      </svg>
                  </div>
                  <h2 className="mb-3">Inscription réussie !</h2>
                  <p className="text-muted mb-4">Votre compte a été créé avec succès.</p>
                  <p className="small text-muted mb-4">Redirection vers l'accueil dans 5 secondes...</p>
                  <div className="d-flex justify-content-center gap-3">
                      <Link href="/login" className="btn btn-primary d-flex align-items-center gap-2">
                          Se connecter
                      </Link>
                      <Link href="/" className="btn btn-outline-primary d-flex align-items-center gap-2">
                          Retour à l'accueil
                      </Link>
                  </div>
              </div>

              <style jsx>{`
                  @keyframes stroke {
                      100% { stroke-dashoffset: 0; }
                  }
                  @keyframes scale {
                      0%, 100% { transform: none; }
                      50% { transform: scale3d(1.1, 1.1, 1); }
                  }
                  @keyframes fill {
                      100% { box-shadow: inset 0px 0px 0px 30px #28a745; }
                  }
              `}</style>
          </div>
      );
  }

  return (
    <div className="register-container bg-white min-vh-100 py-3 py-md-5">
      <div className="container">
        <div className="register-card border rounded-4 shadow-sm mx-auto overflow-hidden" style={{maxWidth: '900px'}}>
            <div className="register-header text-center p-4 bg-light border-bottom">
                <Image src="/assets/images/Cameroun.png" alt="Logo" width={60} height={60} className="mb-3" />
                <h2 className="fw-bold mb-1">Créer un compte</h2>
                <p className="text-muted small">Suivez les étapes</p>
            </div>

            {step < 5 && (
                <div className="progress-bar-container px-4 py-4 bg-light">
                    <div className="progress-steps position-relative d-flex justify-content-between mx-auto" style={{maxWidth: '600px'}}>
                        <div className="progress-line position-absolute w-100" style={{top: '50%', left: '0', height: '2px', background: '#dee2e6', transform: 'translateY(-50%)', zIndex: 1}}></div>
                        
                        {[1, 2, 3, 4].map(num => (
                            <div key={num} className={`progress-step position-relative bg-white rounded-circle d-flex align-items-center justify-content-center fw-bold transition-all ${step >= num ? 'active text-white border-primary' : 'border-secondary text-secondary'}`} style={{width: '40px', height: '40px', border: '2px solid', zIndex: 2, borderColor: step >= num ? '#1774df' : '#dee2e6', background: step >= num ? '#1774df' : 'white'}}>
                                {step > num ? '✓' : num}
                                <span className="progress-label position-absolute text-muted small text-nowrap" style={{top: '45px'}}>{num === 1 ? 'Infos' : num === 2 ? 'Naissance' : num === 3 ? 'Profil' : 'Contact'}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="p-4 p-md-5">
                {error && <div className="alert alert-danger small mb-4">{error}</div>}

                <form onSubmit={step >= 4 ? handleSubmit : (e) => e.preventDefault()}>
                    {step === 1 && (
                        <div className="form-section fade-in">
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
                        </div>
                    )}

                    {step === 2 && (
                        <div className="form-section fade-in">
                            <h4 className="mb-4">Lieu de Naissance</h4>
                            <div className="row g-3">
                                <div className="col-md-4">
                                    <label className="form-label small fw-bold">Région</label>
                                    <select name="region_naissance_id" className="form-select" value={formData.region_naissance_id} onChange={handleChange} required>
                                        <option value="">Sélectionner</option>
                                        {regions.map(r => <option key={r.RegionID} value={r.RegionID}>{r.NomRegion}</option>)}
                                    </select>
                                </div>
                                <div className="col-md-4">
                                    <label className="form-label small fw-bold">Département</label>
                                    <select name="departement_naissance_id" className="form-select" value={formData.departement_naissance_id} onChange={handleChange} required disabled={!formData.region_naissance_id}>
                                        <option value="">Sélectionner</option>
                                        {departementsNaissance.map(d => <option key={d.DepartementID} value={d.DepartementID}>{d.NomDepartement}</option>)}
                                    </select>
                                </div>
                                <div className="col-md-4">
                                    <label className="form-label small fw-bold">Ville / Arrondissement</label>
                                    <select name="ville_naissance_id" className="form-select" value={formData.ville_naissance_id} onChange={handleChange} required disabled={!formData.departement_naissance_id}>
                                        <option value="">Sélectionner</option>
                                        {villesNaissance.map(v => <option key={v.VilleID} value={v.VilleID}>{v.NomVille}</option>)}
                                    </select>
                                </div>
                            </div>
                        </div>
                    )}

                    {step === 3 && (
                        <div className="form-section fade-in">
                            <h4 className="mb-4">Résidence & Profil</h4>
                            <div className="row g-3">
                                <div className="col-md-4">
                                    <label className="form-label small fw-bold">Région Résidence</label>
                                    <select name="region_residence_id" className="form-select" value={formData.region_residence_id} onChange={handleChange} required>
                                        <option value="">Sélectionner</option>
                                        {regions.map(r => <option key={r.RegionID} value={r.RegionID}>{r.NomRegion}</option>)}
                                    </select>
                                </div>
                                <div className="col-md-4">
                                    <label className="form-label small fw-bold">Département Résidence</label>
                                    <select name="departement_residence_id" className="form-select" value={formData.departement_residence_id} onChange={handleChange} required disabled={!formData.region_residence_id}>
                                        <option value="">Sélectionner</option>
                                        {departementsResidence.map(d => <option key={d.DepartementID} value={d.DepartementID}>{d.NomDepartement}</option>)}
                                    </select>
                                </div>
                                <div className="col-md-4">
                                    <label className="form-label small fw-bold">Ville Résidence</label>
                                    <select name="ville_residence_id" className="form-select" value={formData.ville_residence_id} onChange={handleChange} required disabled={!formData.departement_residence_id}>
                                        <option value="">Sélectionner</option>
                                        {villesResidence.map(v => <option key={v.VilleID} value={v.VilleID}>{v.NomVille}</option>)}
                                    </select>
                                </div>
                                <div className="col-md-12">
                                    <label className="form-label small fw-bold">Adresse Complète</label>
                                    <input type="text" name="adresse" className="form-control" placeholder="Quartier, Rue, etc." value={formData.adresse} onChange={handleChange} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Profession</label>
                                    <input type="text" name="profession" className="form-control" value={formData.profession} onChange={handleChange} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Ethnie</label>
                                    <select name="ethnie_id" className="form-select" value={formData.ethnie_id} onChange={handleChange} required>
                                        <option value="">Sélectionner</option>
                                        {ethnies.map(e => <option key={e.EthnieID} value={e.EthnieID}>{e.NomEthnie}</option>)}
                                    </select>
                                </div>
                            </div>
                        </div>
                    )}

                    {step === 4 && (
                        <div className="form-section fade-in">
                            <h4 className="mb-4">Contact & Validation</h4>
                            <div className="row g-3">
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Adresse Email</label>
                                    <input type="email" name="email" className="form-control" placeholder="Ex: jean.dupont@email.com" value={formData.email} onChange={handleChange} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Numéro de Téléphone</label>
                                    <input type="tel" name="telephone" className="form-control" value={formData.telephone} onChange={handleChange} required />
                                </div>
                                <div className="col-12 mt-4 text-center text-muted small px-3 py-2 bg-light rounded border">
                                    En cliquant sur "Terminer l'inscription", vous acceptez nos conditions d'utilisation et certifiez l'exactitude des informations fournies.
                                </div>
                            </div>
                        </div>
                    )}

                    {step === 5 && similarAccount && (
                        <div className="form-section fade-in text-center py-4">
                            <div className="mb-4">
                                <i className="bi bi-exclamation-circle text-warning display-1 mb-3"></i>
                                <h2 className="fw-bold fs-3">Compte similaire détecté</h2>
                            </div>
                            <p className="text-muted mb-4">{similarAccount.message}</p>
                            <div className="p-3 bg-light rounded-3 mb-4">
                                <strong>{similarAccount.similar_email}</strong>
                            </div>
                            <div className="d-flex flex-column gap-3 mx-auto" style={{maxWidth: '400px'}}>
                                <Link href="/login" className="btn btn-primary rounded-pill py-2">
                                    Oui, me connecter
                                </Link>
                                <button type="button" className="btn btn-outline-primary rounded-pill py-2" onClick={() => handleSubmit(undefined, true)} disabled={loading}>
                                    {loading ? "Création..." : "Non, forcer la création d'un nouveau compte"}
                                </button>
                            </div>
                        </div>
                    )}

                    {step < 5 && (
                        <div className="d-flex justify-content-between mt-5">
                            {step > 1 ? (
                                <button type="button" className="btn btn-outline-secondary rounded-pill px-4" onClick={prevStep}>Précédent</button>
                            ) : <div></div>}
                            
                            <div>
                                {step < 4 ? (
                                    <button type="button" className="btn btn-primary rounded-pill px-5" onClick={nextStep}>Suivant</button>
                                ) : (
                                    <button type="submit" className="btn btn-success rounded-pill px-5" disabled={loading}>
                                        {loading ? "Création..." : "Terminer l'inscription"}
                                    </button>
                                )}
                            </div>
                        </div>
                    )}
                </form>
            </div>
        </div>
      </div>
      <style jsx>{`
          .fade-in {
              animation: fadeIn 0.4s ease-in-out;
          }
          @keyframes fadeIn {
              from { opacity: 0; transform: translateY(10px); }
              to { opacity: 1; transform: translateY(0); }
          }
          .custom-shadow { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
          
      `}</style>
    </div>
  );
}
