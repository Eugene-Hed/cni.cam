"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import { 
  ArrowLeft, 
  Clock, 
  CheckCircle, 
  FileText, 
  CreditCard, 
  Download, 
  Info,
  Calendar
} from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";

import SignatureModal from "@/components/ui/SignatureModal";

export default function CitizenDemandeDetails() {
  const params = useParams();
  const router = useRouter();
  const [demande, setDemande] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [showSignatureModal, setShowSignatureModal] = useState(false);

  const fetchDetails = async () => {
    try {
      const resp = await apiClient.get(`/citoyen/demandes/${params.id}`);
      setDemande(resp.data.data);
    } catch (err) {
      console.error("Erreur chargement détails", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (params.id) fetchDetails();
  }, [params.id]);

  if (loading) return <div className="p-5 text-center"><div className="spinner-border text-primary"></div></div>;
  if (!demande) return <div className="p-5 text-center">Demande introuvable.</div>;

  const steps = [
    { label: "Soumission", date: demande.DateSoumission, active: true },
    { label: "Vérification Officier", date: demande.historique?.find((h: any) => h.NouveauStatut === 'EnCours')?.DateModification, active: !!demande.historique?.find((h: any) => h.NouveauStatut === 'EnCours') },
    { label: "Signature", date: demande.DateSignature || demande.DateSignatureOfficier, active: !!(demande.DateSignature || demande.DateSignatureOfficier) },
    { label: "Disponible", date: demande.DateAchevement, active: demande.Statut === 'Validé' || demande.Statut === 'Approuvée' }
  ];

  return (
    <div className="max-w-5xl mx-auto">
      <button onClick={() => router.back()} className="btn btn-link text-muted p-0 mb-4 d-flex align-items-center gap-2 text-decoration-none">
        <ArrowLeft size={18} /> Retour aux demandes
      </button>

      <div className="d-flex justify-content-between align-items-start mb-5 flex-wrap gap-4">
        <div>
          <div className="d-flex align-items-center gap-3 mb-2">
            <h2 className="fw-bold mb-0">Demande #{demande.NumeroReference || demande.DemandeID}</h2>
            <span className={`badge rounded-pill px-3 py-2 ${
                demande.Statut === 'Validé' || demande.Statut === 'Approuvée' ? 'bg-success' : 'bg-warning text-dark'
            }`}>
                {demande.Statut}
            </span>
          </div>
          <p className="text-muted"><Calendar size={14} className="me-1" /> Soumise le {new Date(demande.DateSoumission).toLocaleDateString()}</p>
        </div>
        
        {(demande.Statut === 'Validé' || demande.Statut === 'Approuvée') && (
            <div className="d-flex gap-2">
                <button className="btn btn-success rounded-pill px-4 d-flex align-items-center gap-2 shadow-sm">
                    <Download size={18} /> Télécharger mon document
                </button>
            </div>
        )}
      </div>

      <div className="row g-4 mb-5">
        <div className="col-lg-8">
            {/* Progression Timeline */}
            <div className="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
                <h5 className="fw-bold mb-5">Suivi de l'avancement</h5>
                <div className="position-relative ps-4 border-start border-2 ms-2">
                    {steps.map((step, i) => (
                        <div key={i} className={`mb-5 position-relative ${step.active ? '' : 'opacity-50'}`}>
                            <div className={`position-absolute top-0 start-0 translate-middle-x rounded-circle border border-4 border-white shadow-sm ${step.active ? 'bg-primary' : 'bg-secondary'}`} 
                                 style={{ width: 20, height: 20, marginLeft: -29 }}></div>
                            <h6 className={`fw-bold mb-1 ${step.active ? 'text-primary' : ''}`}>{step.label}</h6>
                            <p className="small text-muted mb-0">
                                {step.active ? (step.date ? new Date(step.date).toLocaleDateString() : 'En cours...') : 'À venir'}
                            </p>
                        </div>
                    ))}
                </div>
            </div>

            {/* Request Summary */}
            <div className="card border-0 shadow-sm rounded-4 p-4">
                <h5 className="fw-bold mb-4">Récapitulatif des informations</h5>
                <div className="row g-4">
                    <div className="col-sm-6">
                        <label className="small text-muted d-block">Type de demande</label>
                        <span className="fw-medium">{demande.TypeDemande}</span>
                    </div>
                    <div className="col-sm-6">
                        <label className="small text-muted d-block">Sous-type</label>
                        <span className="fw-medium">{demande.SousTypeDemande || 'Standard'}</span>
                    </div>
                    <div className="col-sm-12">
                        <hr className="opacity-10" />
                    </div>
                    <div className="col-sm-6">
                        <label className="small text-muted d-block">Nom complet</label>
                        <span className="fw-medium">{demande.details_cni?.Prenom || demande.details_nationalite?.Prenom} {demande.details_cni?.Nom || demande.details_nationalite?.Nom}</span>
                    </div>
                    <div className="col-sm-6">
                        <label className="small text-muted d-block">Lieu de naissance</label>
                        <span className="fw-medium">{demande.details_cni?.LieuNaissance || demande.details_nationalite?.LieuNaissance}</span>
                    </div>
                </div>
            </div>
        </div>

        <div className="col-lg-4">
            {/* Signature Status */}
            <div className={`card border-0 shadow-sm rounded-4 p-4 mb-4 ${demande.SignatureEnregistree ? 'bg-success bg-opacity-10 border border-success' : 'bg-warning bg-opacity-10 border border-warning'}`}>
                <h6 className="fw-bold mb-3">Signature Numérique</h6>
                {demande.SignatureEnregistree ? (
                    <div className="text-center">
                        <CheckCircle size={32} className="text-success mb-2" />
                        <p className="small fw-bold text-success mb-2">Signature validée</p>
                        <img src={`${process.env.NEXT_PUBLIC_API_URL}/storage/${demande.CheminSignature}`} alt="Votre signature" className="bg-white rounded border p-2 w-100" style={{ maxHeight: 80, objectFit: 'contain' }} />
                    </div>
                ) : (
                    <div className="text-center">
                        <p className="small text-warning-emphasis mb-3">Votre signature manuscrite est requise pour finaliser le dossier.</p>
                        <button className="btn btn-warning btn-sm fw-bold w-100 rounded-pill shadow-sm" onClick={() => setShowSignatureModal(true)}>
                            Signer maintenant
                        </button>
                    </div>
                )}
            </div>

            {/* Status Card */}
            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-primary text-white">
                <div className="d-flex align-items-center gap-3 mb-3">
                    <div className="bg-white bg-opacity-20 rounded-circle p-2">
                        <CreditCard size={20} />
                    </div>
                    <h6 className="fw-bold mb-0">Paiement</h6>
                </div>
                <h3 className="fw-bold mb-1">{demande.MontantPaiement} FCFA</h3>
                <p className="small mb-0 opacity-75">{demande.StatutPaiement === 'Payé' ? 'Règlement effectué' : 'Paiement en attente'}</p>
            </div>

            {/* Documents List */}
            <div className="card border-0 shadow-sm rounded-4 p-4">
                <h6 className="fw-bold mb-4">Pièces jointes</h6>
                <div className="d-flex flex-column gap-3">
                    {demande.documents?.length > 0 ? demande.documents.map((doc: any, i: number) => (
                        <div key={i} className="d-flex align-items-center justify-content-between p-2 rounded-3 hover-bg-light border border-light">
                            <div className="d-flex align-items-center gap-2 overflow-hidden">
                                <FileText size={16} className="text-primary flex-shrink-0" />
                                <span className="small text-truncate" title={doc.NomDocument || doc.CheminFichier}>{doc.NomDocument || "Document " + (i+1)}</span>
                            </div>
                            <button className="btn btn-link btn-sm p-0"><Download size={14} /></button>
                        </div>
                    )) : (
                        <p className="small text-muted italic">Aucun document attaché.</p>
                    )}
                </div>
            </div>
            
            <div className="alert alert-info border-0 rounded-4 mt-4 small d-flex gap-3">
                <Info size={24} className="flex-shrink-0" />
                <div>
                    <strong>Besoin d'aide ?</strong><br />
                    Contactez le support si vous remarquez une erreur dans votre dossier.
                </div>
            </div>
        </div>
      </div>

      <SignatureModal 
        show={showSignatureModal} 
        onClose={() => setShowSignatureModal(false)}
        apiEndpoint={`/citoyen/demandes/${params.id}/signature`}
        onSuccess={() => {
            setShowSignatureModal(false);
            fetchDetails(); // refresh to show checked state
        }}
      />

      <style jsx>{`
        .hover-bg-light:hover { background-color: #f8f9fa; }
      `}</style>
    </div>
  );
}
