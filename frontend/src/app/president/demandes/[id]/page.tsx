"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import { 
  ArrowLeft, 
  CheckCircle, 
  XSquare, 
  FileText, 
  ShieldCheck,
  Printer,
  History
} from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";
import SignatureModal from "@/components/ui/SignatureModal";

export default function PresidentDetailDemandePage() {
  const { id } = useParams();
  const [demande, setDemande] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [message, setMessage] = useState("");
  const [showSignatureModal, setShowSignatureModal] = useState(false);
  const router = useRouter();

  const fetchDetail = async () => {
    try {
        const resp = await apiClient.get(`/president/demandes/${id}`);
        setDemande(resp.data.data);
    } catch (err) { console.error(err); }
    finally { setLoading(false); }
  };

  useEffect(() => {
    fetchDetail();
  }, [id]);

  const handleApprove = async () => {
    setProcessing(true);
    try {
        await apiClient.put(`/president/demandes/${id}/statut`, { statut: 'Approuvee' });
        setMessage("Dossier approuvé avec succès !");
        fetchDetail();
    } catch (err) { console.error(err); }
    finally { setProcessing(false); }
  };

  const handleGenerateCert = async () => {
    setProcessing(true);
    try {
        await apiClient.post(`/president/demandes/${id}/generer-certificat`);
        setMessage("Certificat de Nationalité généré !");
        fetchDetail();
    } catch (err) { console.error(err); }
    finally { setProcessing(false); }
  };

  if (loading) return <div className="p-5 text-center text-muted">Vérification du certificat...</div>;
  if (!demande) return <div className="p-5 text-center text-danger">Dossier introuvable.</div>;

  return (
    <div className="president-detail pb-5">
        <header className="mb-4 d-flex align-items-center justify-content-between">
            <div className="d-flex align-items-center gap-3">
                <Link href="/president" className="btn btn-light rounded-circle p-2">
                    <ArrowLeft size={20} />
                </Link>
                <div>
                    <h2 className="fw-bold h3 mb-0">Certificat #{demande.NumeroReference}</h2>
                    <p className="small text-muted mb-0">Bénéficiaire : {demande.utilisateur?.Prenom} {demande.utilisateur?.Nom}</p>
                </div>
            </div>
            
            <div className="d-flex gap-2">
                {demande.Statut === 'Soumise' && (
                    <button className="btn btn-success rounded-pill px-4" onClick={handleApprove} disabled={processing}>
                        <CheckCircle size={18} className="me-2" /> Valider l'homologation
                    </button>
                )}
                {demande.Statut === 'Approuvee' && (
                    <button className="btn btn-primary rounded-pill px-4" onClick={handleGenerateCert} disabled={processing}>
                        <Printer size={18} className="me-2" /> Émettre le Certificat
                    </button>
                )}
                {demande.Statut === 'Terminee' && demande.certificat_nationalite && !demande.certificat_nationalite.SignaturePresidentielle && (
                    <button className="btn btn-warning rounded-pill px-4 fw-bold" onClick={() => setShowSignatureModal(true)} disabled={processing}>
                        Signature requise
                    </button>
                )}
                {demande.Statut === 'Terminee' && demande.certificat_nationalite && demande.certificat_nationalite.SignaturePresidentielle && (
                    <a href={`${process.env.NEXT_PUBLIC_API_URL}/storage/${demande.certificat_nationalite.CheminFichier}`} target="_blank" className="btn btn-dark rounded-pill px-4">
                        <FileText size={18} className="me-2" /> Ouvrir l'acte final
                    </a>
                )}
            </div>
        </header>

        {message && <div className="alert alert-success alert-dismissible mb-4">{message}</div>}

        <div className="row g-4">
            <div className="col-lg-8">
                <div className="card boder-0 shadow-sm border-0 rounded-4 p-4 mb-4">
                    <h5 className="fw-bold mb-4">Détails de l'Acte</h5>
                    <div className="row g-4">
                        <div className="col-md-6">
                            <label className="small text-muted d-block">Lieu de Naissance</label>
                            <p className="fw-bold fs-5">{demande.details_nationalite?.LieuNaissance}</p>
                        </div>
                        <div className="col-md-6">
                            <label className="small text-muted d-block">Nom du Père</label>
                            <p className="fw-bold fs-5">{demande.details_nationalite?.NomPere}</p>
                        </div>
                        <div className="col-md-6">
                            <label className="small text-muted d-block">Nom de la Mère</label>
                            <p className="fw-bold fs-5">{demande.details_nationalite?.NomMere}</p>
                        </div>
                        <div className="col-md-6">
                            <label className="small text-muted d-block">Motif de la demande</label>
                            <p className="fw-bold fs-5">{demande.details_nationalite?.Motif}</p>
                        </div>
                    </div>
                </div>

                <div className="row g-4 mb-4">
                    <div className="col-md-12">
                        <div className={`card border-0 shadow-sm rounded-4 p-4 h-100 ${demande.Statut === 'Terminee' && (!demande.certificat_nationalite || !demande.certificat_nationalite.SignaturePresidentielle) ? 'border-warning border border-2' : ''}`}>
                            <h5 className="fw-bold mb-4">Sceau et Signature du Président</h5>
                            <div className="bg-light p-4 rounded-4 text-center border d-flex flex-column align-items-center justify-content-center" style={{ minHeight: 150 }}>
                                {demande.certificat_nationalite?.SignaturePresidentielle ? (
                                    <img src={`${process.env.NEXT_PUBLIC_API_URL}/storage/${demande.certificat_nationalite.CheminSignaturePresident}`} alt="Signature Président" style={{ maxHeight: 100 }} />
                                ) : demande.Statut === 'Terminee' ? (
                                    <>
                                        <p className="text-muted small mb-3">Requis pour finaliser légalement l'acte.</p>
                                        <button className="btn btn-primary btn-sm rounded-pill px-4" onClick={() => setShowSignatureModal(true)}>Signer Numériquement</button>
                                    </>
                                ) : (
                                    <p className="text-muted small my-auto">Générez l'acte avant de le signer.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="card boder-0 shadow-sm border-0 rounded-4 p-4">
                    <h5 className="fw-bold mb-4">Historique des Validations</h5>
                    <div className="timeline small">
                        <div className="d-flex gap-3 mb-3">
                            <div className="btn btn-success btn-sm rounded-circle p-1 h-fit"><CheckCircle size={14} /></div>
                            <div>
                                <p className="fw-bold mb-0">Demande Soumise par le Citoyen</p>
                                <p className="text-muted mb-0">{new Date(demande.DateSoumission).toLocaleString()}</p>
                            </div>
                        </div>
                        {demande.Statut === 'Terminee' && (
                            <div className="d-flex gap-3">
                                <div className="btn btn-primary btn-sm rounded-circle p-1 h-fit"><ShieldCheck size={14} /></div>
                                <div>
                                    <p className="fw-bold mb-0">Certificat Émis par la Présidence</p>
                                    <p className="text-muted mb-0">Opération finalisée.</p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <div className="col-lg-4">
                <div className="card border-0 shadow-sm rounded-4 p-4 bg-light">
                    <h6 className="fw-bold mb-3"><History size={18} className="me-2" /> Note de Service</h6>
                    <p className="small text-muted">
                        Veuillez vérifier la concordance entre l'acte de naissance fourni dans les pièces jointes et les informations saisies ci-dessus avant toute homologation.
                    </p>
                    <div className="d-grid mt-4">
                        <button className="btn btn-outline-secondary btn-sm rounded-pill">Consulter le dossier archivé</button>
                    </div>
                </div>
            </div>
        </div>

        <SignatureModal 
            show={showSignatureModal} 
            onClose={() => setShowSignatureModal(false)}
            apiEndpoint={`/president/demandes/${id}/signature`}
            title="Sceau et Signature"
            description="Je certifie par la présente la validité de ce certificat de nationalité."
            onSuccess={() => {
                setShowSignatureModal(false);
                fetchDetail();
            }}
        />
    </div>
  );
}
