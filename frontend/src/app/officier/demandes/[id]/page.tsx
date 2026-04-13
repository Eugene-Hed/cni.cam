"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import { 
  ArrowLeft, 
  CheckCircle, 
  XSquare, 
  FileText, 
  User, 
  MapPin, 
  Calendar,
  Eye,
  Settings,
  Printer
} from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";
import SignatureModal from "@/components/ui/SignatureModal";
import { motion } from "framer-motion";

export default function OfficerDetailDemandePage() {
  const { id } = useParams();
  const [demande, setDemande] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [message, setMessage] = useState("");
  const [showSignatureModal, setShowSignatureModal] = useState(false);
  const router = useRouter();

  const fetchDetail = async () => {
    try {
        const resp = await apiClient.get(`/officier/demandes/${id}`);
        setDemande(resp.data.data);
    } catch (err) { console.error(err); }
    finally { setLoading(false); }
  };

  useEffect(() => {
    fetchDetail();
  }, [id]);

  const handleUpdateStatus = async (status: string) => {
    setProcessing(true);
    try {
        await apiClient.put(`/officier/demandes/${id}/statut`, { statut: status, commentaire: "Traitement par l'officier." });
        setMessage(`Demande mise à jour: ${status}`);
        fetchDetail();
    } catch (err) { console.error(err); }
    finally { setProcessing(false); }
  };

  const handleGenerateCni = async () => {
    setProcessing(true);
    try {
        const resp = await apiClient.post(`/officier/demandes/${id}/generer-cni`);
        setMessage("CNI générée avec succès !");
        fetchDetail();
    } catch (err: any) { 
        setMessage(err.response?.data?.message || "Erreur lors de la génération.");
    }
    finally { setProcessing(false); }
  };

  const handleValidateDocument = async (docId: number, status: string) => {
    setProcessing(true);
    try {
        await apiClient.put(`/officier/documents/${docId}/valider`, { statut: status });
        setMessage(`Document mis à jour: ${status}`);
        fetchDetail();
    } catch (err: any) {
        setMessage(err.response?.data?.message || "Erreur lors de la validation du document.");
    } finally {
        setProcessing(false);
    }
  };

  if (loading) return <div className="p-5 text-center text-muted">Chargement du dossier...</div>;
  if (!demande) return <div className="p-5 text-center text-danger">Dossier introuvable.</div>;

  const user = demande.utilisateur;
  const details = demande.details_cni;

  return (
    <div className="officer-detail pb-5">
        <header className="mb-4 d-flex align-items-center justify-content-between">
            <div className="d-flex align-items-center gap-3">
                <Link href="/officier" className="btn btn-light rounded-circle p-2">
                    <ArrowLeft size={20} />
                </Link>
                <div className="text-start">
                    <h2 className="fw-bold h3 mb-0">Demande #{demande.NumeroReference}</h2>
                    <span className={`badge rounded-pill ${demande.Statut === 'Terminee' ? 'bg-success' : 'bg-warning'} px-3`}>
                        {demande.Statut}
                    </span>
                </div>
            </div>
            
            <div className="d-flex gap-2">
                {demande.Statut === 'Soumise' && (
                    <>
                        <button className="btn btn-outline-danger rounded-pill px-4" onClick={() => handleUpdateStatus('Rejetee')} disabled={processing}>Rejeter</button>
                        <button className="btn btn-primary rounded-pill px-4" onClick={() => handleUpdateStatus('Approuvee')} disabled={processing}>Approuver le dossier</button>
                    </>
                )}
                {demande.Statut === 'Approuvee' && !demande.SignatureOfficierEnregistree && (
                    <button className="btn btn-warning rounded-pill px-4 fw-bold" onClick={() => setShowSignatureModal(true)} disabled={processing}>
                        Signature requise
                    </button>
                )}
                {demande.Statut === 'Approuvee' && demande.SignatureOfficierEnregistree && (
                    <button className="btn btn-success rounded-pill px-4" onClick={handleGenerateCni} disabled={processing}>
                        <Printer size={18} className="me-2" /> Générer la CNI
                    </button>
                )}
                {demande.Statut === 'Terminee' && demande.carte_identite && (
                    <a href={`${process.env.NEXT_PUBLIC_API_URL}/storage/${demande.carte_identite.CheminFichier}`} target="_blank" className="btn btn-outline-primary rounded-pill px-4">
                        <FileText size={18} className="me-2" /> Voir le PDF
                    </a>
                )}
            </div>
        </header>

        {message && <div className="alert alert-success alert-dismissible fade show mb-4">{message}</div>}

        <div className="row g-4">
            {/* Citizen Info Column */}
            <div className="col-lg-4">
                <div className="card boder-0 shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div className="bg-primary p-4 text-center text-white">
                        <div className="bg-white rounded-circle shadow p-1 mx-auto mb-3" style={{ width: 100, height: 100 }}>
                            <div className="bg-light w-100 h-100 rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold display-6">
                                {user.Prenom[0]}
                            </div>
                        </div>
                        <h4 className="fw-bold mb-1">{user.Prenom} {user.Nom}</h4>
                        <p className="small mb-0 opacity-75">{user.Codeutilisateur}</p>
                    </div>
                    <div className="card-body p-4">
                        <div className="vstack gap-3">
                            <div className="d-flex align-items-center gap-3">
                                <Calendar size={18} className="text-muted" />
                                <div>
                                    <p className="small text-muted mb-0">Né le</p>
                                    <p className="fw-bold mb-0">{new Date(details.DateNaissance).toLocaleDateString()}</p>
                                </div>
                            </div>
                            <div className="d-flex align-items-center gap-3">
                                <MapPin size={18} className="text-muted" />
                                <div>
                                    <p className="small text-muted mb-0">Lieu de Naissance</p>
                                    <p className="fw-bold mb-0">{details.LieuNaissance}</p>
                                </div>
                            </div>
                            <div className="d-flex align-items-center gap-3">
                                <User size={18} className="text-muted" />
                                <div>
                                    <p className="small text-muted mb-0">Profession</p>
                                    <p className="fw-bold mb-0">{details.Profession}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Documents and Review Column */}
            <div className="col-lg-8">
                <div className="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 className="fw-bold mb-4">Pièces Justificatives</h5>
                    <div className="row g-3">
                        {demande.documents?.map((doc: any) => (
                            <div key={doc.DocumentID} className="col-md-6">
                                <motion.div 
                                    className="p-3 border rounded-4 bg-light hover-bg-light transition-all cursor-pointer"
                                    whileHover={{ scale: 1.02 }}
                                >
                                    <div className="d-flex justify-content-between align-items-center">
                                        <div className="d-flex align-items-center gap-3">
                                            <div className="bg-white p-2 rounded-circle shadow-sm text-primary">
                                                <FileText size={20} />
                                            </div>
                                            <div>
                                                <p className="mb-0 fw-bold small">{doc.TypeDocument}</p>
                                                <span className={`badge rounded-pill bg-${
                                                    doc.StatutValidation === 'Approuve' ? 'success' : 
                                                    doc.StatutValidation === 'Rejete' ? 'danger' : 'warning'
                                                } smaller`}>
                                                    {doc.StatutValidation || 'En attente'}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="d-flex gap-2">
                                            {demande.Statut === 'Soumise' && (
                                                <div className="btn-group">
                                                    <button 
                                                        className={`btn btn-sm btn-outline-success p-1 rounded-circle ${doc.StatutValidation === 'Approuve' ? 'bg-success text-white' : ''}`}
                                                        onClick={(e) => { e.stopPropagation(); handleValidateDocument(doc.DocumentID, 'Approuve'); }}
                                                        disabled={processing}
                                                        title="Approuver le document"
                                                    >
                                                        <CheckCircle size={14} />
                                                    </button>
                                                    <button 
                                                        className={`btn btn-sm btn-outline-danger p-1 rounded-circle ${doc.StatutValidation === 'Rejete' ? 'bg-danger text-white' : ''}`}
                                                        onClick={(e) => { e.stopPropagation(); handleValidateDocument(doc.DocumentID, 'Rejete'); }}
                                                        disabled={processing}
                                                        title="Rejeter le document"
                                                    >
                                                        <XSquare size={14} />
                                                    </button>
                                                </div>
                                            )}
                                            <Link href={`${process.env.NEXT_PUBLIC_API_URL}/storage/${doc.CheminFichier}`} target="_blank" className="btn btn-sm btn-white shadow-sm rounded-circle p-2">
                                                <Eye size={16} />
                                            </Link>
                                        </div>
                                    </div>
                                </motion.div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="row g-4">
                    <div className="col-md-6">
                        <div className="card border-0 shadow-sm rounded-4 p-4 h-100">
                            <h5 className="fw-bold mb-4">Signature du Citoyen</h5>
                            <div className="bg-light p-4 rounded-4 text-center border d-flex align-items-center justify-content-center" style={{ minHeight: 150 }}>
                                {demande.CheminSignature ? (
                                     <img src={`${process.env.NEXT_PUBLIC_API_URL}/storage/${demande.CheminSignature}`} alt="Signature Citoyen" style={{ maxHeight: 100 }} />
                                ) : (
                                    <p className="text-muted small my-auto">Signature non disponible.</p>
                                )}
                            </div>
                        </div>
                    </div>
                    <div className="col-md-6">
                        <div className={`card border-0 shadow-sm rounded-4 p-4 h-100 ${demande.Statut === 'Approuvee' && !demande.SignatureOfficierEnregistree ? 'border-warning border border-2' : ''}`}>
                            <h5 className="fw-bold mb-4">Signature de l'Officier</h5>
                            <div className="bg-light p-4 rounded-4 text-center border d-flex flex-column align-items-center justify-content-center" style={{ minHeight: 150 }}>
                                {demande.SignatureOfficierEnregistree ? (
                                    <img src={`${process.env.NEXT_PUBLIC_API_URL}/storage/${demande.CheminSignatureOfficier}`} alt="Signature Officier" style={{ maxHeight: 100 }} />
                                ) : demande.Statut === 'Approuvee' ? (
                                    <>
                                        <p className="text-muted small mb-3">Requis pour l'émission.</p>
                                        <button className="btn btn-primary btn-sm rounded-pill px-4" onClick={() => setShowSignatureModal(true)}>Signer maintenant</button>
                                    </>
                                ) : (
                                    <p className="text-muted small my-auto">Approuvez d'abord le dossier.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <SignatureModal 
            show={showSignatureModal} 
            onClose={() => setShowSignatureModal(false)}
            apiEndpoint={`/officier/demandes/${id}/signature`}
            title="Validation Officielle du Dossier"
            description="L'apposition de cette signature certifie que toutes les pièces ont été rigoureusement vérifiées et sont conformes."
            onSuccess={() => {
                setShowSignatureModal(false);
                fetchDetail();
            }}
        />
    </div>
  );
}
