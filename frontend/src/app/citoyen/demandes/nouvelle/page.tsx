"use client";

import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { 
  FilePlus, 
  Upload, 
  PenTool, 
  CreditCard, 
  CheckCircle2, 
  ArrowLeft, 
  ArrowRight,
  Info
} from "lucide-react";
import SignaturePad from "@/components/dashboard/SignaturePad";
import CameraCapture from "@/components/ui/CameraCapture";
import apiClient from "@/lib/api-client";
import { useRouter } from "next/navigation";
import Link from "next/link";

export default function NewDemandePage() {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState<any>({
    type_demande: "CNI",
    sous_type: "premiere",
    details: {
        nom: "", prenom: "", date_naissance: "", lieu_naissance: "",
        adresse: "", sexe: "M", taille: 170, profession: "",
        nationalite_pere: "Camerounaise", nationalite_mere: "Camerounaise"
    },
    documents: {},
    signature: ""
  });
  const [modePhoto, setModePhoto] = useState<"upload" | "camera">("upload");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const router = useRouter();

  const handleDetailsChange = (e: any) => {
    setFormData({
        ...formData,
        details: { ...formData.details, [e.target.name]: e.target.value }
    });
  };

  const handleFileChange = (e: any, type: string) => {
    const file = e.target.files[0];
    setFormData({
        ...formData,
        documents: { ...formData.documents, [type]: file }
    });
  };

  const nextStep = () => setStep(step + 1);
  const prevStep = () => setStep(step - 1);

  const handleSubmit = async () => {
    setLoading(true);
    setError("");
    try {
        // multipart/form-data for files
        const data = new FormData();
        data.append("type_demande", formData.type_demande);
        data.append("sous_type", formData.sous_type);
        data.append("signature", formData.signature);
        
        // Append details
        Object.keys(formData.details).forEach(key => {
            data.append(key, formData.details[key]);
        });
        
        // Append docs
        Object.keys(formData.documents).forEach(key => {
            data.append(key, formData.documents[key]);
        });

        await apiClient.post("/citoyen/demandes", data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        setStep(6); // Success step
    } catch (err: any) {
        setError(err.response?.data?.message || "Erreur lors de la soumission.");
    } finally {
        setLoading(false);
    }
  };

  return (
    <div className="new-demande-container pb-5">
        <header className="mb-5 text-center">
            <h1 className="fw-bold h2 mb-2">Nouvelle Demande</h1>
            <p className="text-muted">Suivez les étapes pour soumettre votre dossier.</p>
            
            <div className="d-flex justify-content-center mt-4">
                <div className="d-flex align-items-center gap-2">
                    {[1, 2, 3, 4, 5].map(i => (
                        <div key={i} className="d-flex align-items-center gap-2">
                            <div className={`rounded-circle d-flex align-items-center justify-content-center fw-bold ${step === i ? "bg-primary text-white" : step > i ? "bg-success text-white" : "bg-light text-muted"}`} style={{ width: 35, height: 35 }}>
                                {step > i ? <CheckCircle2 size={18} /> : i}
                            </div>
                            {i < 5 && <div className="bg-light" style={{ width: 40, height: 2 }}></div>}
                        </div>
                    ))}
                </div>
            </div>
        </header>

        <div className="card border-0 shadow-sm rounded-4 overflow-hidden mx-auto" style={{ maxWidth: 800 }}>
            <div className="p-4 p-md-5">
                {error && <div className="alert alert-danger mb-4 small">{error}</div>}

                <AnimatePresence mode="wait">
                    {step === 1 && (
                        <motion.div key="s1" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }}>
                            <h4 className="fw-bold mb-4">Sélection du Type</h4>
                            <div className="row g-4">
                                <div className="col-md-6">
                                    <div 
                                        className={`card h-100 border-2 transition-all p-4 text-center cursor-pointer ${formData.type_demande === 'CNI' ? 'border-primary bg-primary bg-opacity-10' : 'border-light'}`}
                                        onClick={() => setFormData({...formData, type_demande: 'CNI'})}
                                    >
                                        <i className="bi bi-person-vcard fs-1 mb-3 text-primary"></i>
                                        <h5 className="fw-bold">Carte d'Identité</h5>
                                        <p className="small text-muted mb-0">Demande de nouvelle carte ou renouvellement.</p>
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <div 
                                        className={`card h-100 border-2 transition-all p-4 text-center cursor-pointer ${formData.type_demande === 'NATIONALITE' ? 'border-success bg-success bg-opacity-10' : 'border-light'}`}
                                        onClick={() => setFormData({...formData, type_demande: 'NATIONALITE'})}
                                    >
                                        <i className="bi bi-flag fs-1 mb-3 text-success"></i>
                                        <h5 className="fw-bold">Certificat Nationalité</h5>
                                        <p className="small text-muted mb-0">Obtention d'un certificat de nationalité camerounaise.</p>
                                    </div>
                                </div>
                            </div>
                        </motion.div>
                    )}

                    {step === 2 && (
                        <motion.div key="s2" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }}>
                            <h4 className="fw-bold mb-4">Détails de la demande</h4>
                            <div className="row g-3">
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Nom</label>
                                    <input type="text" name="nom" className="form-control" value={formData.details.nom} onChange={handleDetailsChange} />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Prénom</label>
                                    <input type="text" name="prenom" className="form-control" value={formData.details.prenom} onChange={handleDetailsChange} />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Sexe</label>
                                    <select name="sexe" className="form-select" value={formData.details.sexe} onChange={handleDetailsChange}>
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Profession</label>
                                    <input type="text" name="profession" className="form-control" value={formData.details.profession} onChange={handleDetailsChange} />
                                </div>
                            </div>
                        </motion.div>
                    )}

                    {step === 3 && (
                        <motion.div key="s3" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }}>
                            <h4 className="fw-bold mb-4">Pièces Justificatives</h4>
                            <div className="alert alert-info small d-flex gap-2">
                                <Info size={18} />
                                <span>Veuillez télécharger des scans clairs de vos documents originaux au format JPG ou PDF.</span>
                            </div>
                            <div className="vstack gap-3 mt-4">
                                <div className="document-upload p-3 border rounded-4 bg-light">
                                    <label className="d-flex align-items-center gap-3 cursor-pointer">
                                        <div className="bg-white p-2 rounded-circle shadow-sm text-primary">
                                            <Upload size={20} />
                                        </div>
                                        <div className="flex-grow-1">
                                            <p className="mb-0 fw-bold small">Acte de Naissance</p>
                                            <p className="mb-0 text-muted smaller">{formData.documents.acte_naissance?.name || "Cliquer pour choisir un fichier"}</p>
                                        </div>
                                        <input type="file" className="d-none" onChange={(e) => handleFileChange(e, 'acte_naissance')} />
                                    </label>
                                </div>
                                <div className="document-upload p-3 border rounded-4 bg-light">
                                    <div className="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <p className="mb-0 fw-bold small">Photo d'Identité (Fond blanc)</p>
                                        </div>
                                        <div className="btn-group">
                                            <button 
                                                type="button" 
                                                className={`btn btn-sm ${modePhoto === 'upload' ? 'btn-primary' : 'btn-outline-primary'}`}
                                                onClick={() => setModePhoto('upload')}
                                            >
                                                Fichier
                                            </button>
                                            <button 
                                                type="button" 
                                                className={`btn btn-sm ${modePhoto === 'camera' ? 'btn-primary' : 'btn-outline-primary'}`}
                                                onClick={() => setModePhoto('camera')}
                                            >
                                                Caméra
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {modePhoto === 'upload' ? (
                                        <label className="d-flex align-items-center gap-3 cursor-pointer p-3 bg-white border rounded">
                                            <div className="bg-light p-2 rounded-circle shadow-sm text-primary">
                                                <Upload size={20} />
                                            </div>
                                            <div className="flex-grow-1">
                                                <p className="mb-0 text-muted smaller">{formData.documents.photo?.name || "Cliquer pour choisir un fichier (.jpg, .png)"}</p>
                                            </div>
                                            <input type="file" className="d-none" onChange={(e) => handleFileChange(e, 'photo')} accept="image/*" />
                                        </label>
                                    ) : (
                                        <CameraCapture 
                                            label="Prendre votre photo d'identité" 
                                            onCapture={(file) => {
                                                setFormData({
                                                    ...formData,
                                                    documents: { ...formData.documents, photo: file }
                                                });
                                            }} 
                                        />
                                    )}
                                </div>
                            </div>
                        </motion.div>
                    )}

                    {step === 4 && (
                        <motion.div key="s4" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }}>
                            <h4 className="fw-bold mb-4">Signature Numérique</h4>
                            <SignaturePad 
                                label="Signez à l'intérieur du cadre ci-dessous" 
                                onSave={(url) => setFormData({...formData, signature: url})} 
                            />
                        </motion.div>
                    )}

                    {step === 5 && (
                        <motion.div key="s5" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} className="text-center">
                            <h4 className="fw-bold mb-4">Paiement & Soumission</h4>
                            <div className="bg-light p-4 rounded-4 mb-4">
                                <div className="d-flex justify-content-between mb-2">
                                    <span>Frais de dossier ({formData.type_demande})</span>
                                    <span className="fw-bold">{formData.type_demande === 'CNI' ? '10 000' : '5 000'} FCFA</span>
                                </div>
                                <div className="d-flex justify-content-between text-success">
                                    <span>Frais de service plateforme</span>
                                    <span className="fw-bold">Gratuit</span>
                                </div>
                                <hr />
                                <div className="d-flex justify-content-between h5 fw-bold text-dark">
                                    <span>Total à payer</span>
                                    <span>{formData.type_demande === 'CNI' ? '10 000' : '5 000'} FCFA</span>
                                </div>
                            </div>
                            <div className="mb-4">
                                <p className="small text-muted mb-3 text-start">Choisissez votre mode de paiement (Simulation) :</p>
                                <div className="d-flex flex-wrap gap-3 justify-content-center">
                                    <button className="btn btn-outline-warning rounded-pill px-4">Orange Money</button>
                                    <button className="btn btn-outline-warning rounded-pill px-4">MTN MoMo</button>
                                </div>
                            </div>
                            <button className="btn btn-primary btn-lg rounded-pill w-100 shadow-sm" onClick={handleSubmit} disabled={loading}>
                                {loading ? "Traitement..." : "Payer et Soumettre la demande"}
                            </button>
                        </motion.div>
                    )}

                    {step === 6 && (
                        <motion.div key="s6" initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} className="text-center py-4">
                            <div className="bg-success bg-opacity-10 text-success rounded-circle p-4 d-inline-flex mb-4">
                                <CheckCircle2 size={64} />
                            </div>
                            <h2 className="fw-bold mb-2">Demande Soumise !</h2>
                            <p className="text-muted mb-4">Votre demande a été enregistrée avec succès. Vous recevrez une notification par SMS/Email à chaque étape du traitement.</p>
                            <Link href="/citoyen" className="btn btn-primary rounded-pill px-5 py-2">
                                Retour au Tableau de Bord
                            </Link>
                        </motion.div>
                    )}
                </AnimatePresence>

                {step < 6 && (
                    <div className="mt-5 d-flex justify-content-between">
                        {step > 1 && (
                            <button className="btn btn-light rounded-pill px-4" onClick={prevStep} disabled={loading}>
                                <ArrowLeft size={18} className="me-2" /> Précédent
                            </button>
                        )}
                        <div className="ms-auto">
                            {step < 5 && (
                                <button className="btn btn-primary rounded-pill px-4" onClick={nextStep}>
                                    Suivant <ArrowRight size={18} className="ms-2" />
                                </button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
        
        <style jsx>{`
            .cursor-pointer { cursor: pointer; }
            .transition-all { transition: all 0.2s ease; }
            .smaller { font-size: 0.75rem; }
            .h-screen { height: 100vh; }
        `}</style>
    </div>
  );
}
