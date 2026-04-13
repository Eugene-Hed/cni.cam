"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import { 
  ArrowLeft, 
  User, 
  FileText, 
  Globe, 
  History, 
  CreditCard,
  ShieldCheck,
  Download,
  Eye,
  Clock
} from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";
import { motion } from "framer-motion";

export default function AdminDetailDemandePage() {
  const { id } = useParams();
  const [demande, setDemande] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    const fetchDetail = async () => {
      try {
        const resp = await apiClient.get(`/admin/demandes/${id}`);
        setDemande(resp.data.data);
      } catch (err) {
        console.error("Erreur chargement détail", err);
      } finally {
        setLoading(false);
      }
    };
    fetchDetail();
  }, [id]);

  if (loading) return <div className="p-5 text-center text-muted">Chargement du dossier supervision...</div>;
  if (!demande) return <div className="p-5 text-center text-danger">Dossier # {id} introuvable.</div>;

  return (
    <div className="admin-detail pb-5">
      <header className="mb-4 d-flex align-items-center justify-content-between">
        <div className="d-flex align-items-center gap-3">
          <Link href="/admin/demandes" className="btn btn-light rounded-circle p-2 shadow-sm">
            <ArrowLeft size={20} />
          </Link>
          <div>
            <h2 className="fw-bold h3 mb-0">Supervision Dossier #{demande.NumeroReference || demande.DemandeID}</h2>
            <p className="small text-muted mb-0">Propriétaire : {demande.utilisateur?.Prenom} {demande.utilisateur?.Nom}</p>
          </div>
        </div>
        
        <div className="d-flex gap-2">
            <span className={`badge rounded-pill px-3 py-2 ${
                demande.Statut === 'Terminee' ? 'bg-success' : 
                demande.Statut === 'Rejetee' ? 'bg-danger' : 'bg-warning text-dark'
            }`}>
                {demande.Statut}
            </span>
        </div>
      </header>

      <div className="row g-4">
        <div className="col-lg-8">
            {/* Infos Citoyen */}
            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div className="d-flex align-items-center gap-2 mb-4">
                    <User size={20} className="text-primary" />
                    <h5 className="fw-bold mb-0">Informations du Citoyen</h5>
                </div>
                <div className="row g-3">
                    <div className="col-md-6">
                        <label className="x-small text-muted d-block">Identifiant Unique</label>
                        <span className="fw-bold font-monospace">{demande.utilisateur?.Codeutilisateur}</span>
                    </div>
                    <div className="col-md-6">
                        <label className="x-small text-muted d-block">Email de Contact</label>
                        <span className="fw-bold">{demande.utilisateur?.Email}</span>
                    </div>
                    <div className="col-md-6">
                        <label className="x-small text-muted d-block">Type de Demande</label>
                        <span className="badge bg-light text-dark">{demande.TypeDemande}</span>
                    </div>
                    <div className="col-md-6">
                        <label className="x-small text-muted d-block">Date de Soumission</label>
                        <span className="fw-bold">{new Date(demande.DateSoumission).toLocaleString()}</span>
                    </div>
                </div>
            </div>

            {/* Détails Techniques */}
            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div className="d-flex align-items-center gap-2 mb-4">
                    {demande.TypeDemande === 'CNI' ? <FileText size={20} className="text-success" /> : <Globe size={20} className="text-info" />}
                    <h5 className="fw-bold mb-0">Détails de la demande</h5>
                </div>
                <div className="row g-4">
                    {demande.TypeDemande === 'CNI' ? (
                        <>
                            <div className="col-md-4">
                                <label className="x-small text-muted d-block">Profession</label>
                                <p className="fw-bold">{demande.details_cni?.Profession}</p>
                            </div>
                            <div className="col-md-4">
                                <label className="x-small text-muted d-block">Taille (cm)</label>
                                <p className="fw-bold">{demande.details_cni?.Taille}</p>
                            </div>
                        </>
                    ) : (
                        <>
                            <div className="col-md-4">
                                <label className="x-small text-muted d-block">Dernier Domicile</label>
                                <p className="fw-bold">{demande.details_nationalite?.LieuNaissance}</p>
                            </div>
                            <div className="col-md-4">
                                <label className="x-small text-muted d-block">Motif</label>
                                <p className="fw-bold">{demande.details_nationalite?.Motif}</p>
                            </div>
                        </>
                    )}
                </div>
            </div>

            {/* Historique */}
            <div className="card border-0 shadow-sm rounded-4 p-4">
                <div className="d-flex align-items-center gap-2 mb-4">
                    <History size={20} className="text-secondary" />
                    <h5 className="fw-bold mb-0">Historique des Traitements</h5>
                </div>
                <div className="timeline">
                    {demande.historique?.map((h: any, i: number) => (
                        <div key={i} className="d-flex gap-3 mb-3 pb-3 border-bottom border-light">
                            <div className="pt-1">
                                <div className="bg-light p-1 rounded-circle"><Clock size={12} /></div>
                            </div>
                            <div className="flex-grow-1">
                                <div className="d-flex justify-content-between">
                                    <span className="fw-bold small">{h.AncienStatut} → {h.NouveauStatut}</span>
                                    <span className="x-small text-muted">{new Date(h.DateModification).toLocaleString()}</span>
                                </div>
                                <p className="small text-muted mb-1">{h.Commentaire || 'Aucun commentaire'}</p>
                                <div className="x-small text-primary">Modifié par : {h.modifie_par?.Prenom} {h.modifie_par?.Nom}</div>
                            </div>
                        </div>
                    ))}
                    {(!demande.historique || demande.historique.length === 0) && <p className="text-muted small italic">Aucun historique de traitement disponible.</p>}
                </div>
            </div>
        </div>

        <div className="col-lg-4">
            {/* Documents */}
            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
                <h6 className="fw-bold mb-3 d-flex align-items-center gap-2">
                    <Download size={18} /> Documents joints
                </h6>
                <div className="vstack gap-2">
                    {demande.documents?.map((doc: any) => (
                        <div key={doc.DocumentID} className="d-flex justify-content-between align-items-center p-2 bg-white rounded-3 border">
                            <div className="text-truncate me-2">
                                <div className="small fw-bold text-truncate">{doc.TypeDocument}</div>
                                <div className="x-small text-muted">Mis en ligne : {new Date(doc.DateTelechargement).toLocaleDateString()}</div>
                            </div>
                            <a href={`${process.env.NEXT_PUBLIC_API_URL}/storage/${doc.CheminFichier}`} target="_blank" className="btn btn-sm btn-light p-1 border">
                                <Eye size={16} className="text-primary" />
                            </a>
                        </div>
                    ))}
                    {(!demande.documents || demande.documents.length === 0) && <p className="text-muted small italic">Aucune pièce jointe.</p>}
                </div>
            </div>

            {/* Paiements */}
            <div className="card border-0 shadow-sm rounded-4 p-4">
                <h6 className="fw-bold mb-3 d-flex align-items-center gap-2">
                    <CreditCard size={18} className="text-success" /> État Financier
                </h6>
                {demande.paiements && demande.paiements.length > 0 ? (
                    demande.paiements.map((p: any) => (
                        <div key={p.PaiementID} className="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 mb-2">
                            <div className="d-flex justify-content-between align-items-center mb-1">
                                <span className="small fw-bold text-success">Paiement Validé</span>
                                <span className="fw-bold fs-5 text-success">{p.Montant} FCFA</span>
                            </div>
                            <div className="x-small text-muted">ID Transaction : {p.TransactionID}</div>
                            <div className="x-small text-muted">Méthode : {p.ModePaiement}</div>
                        </div>
                    ))
                ) : (
                    <div className="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3">
                        <span className="small fw-bold text-danger">Paiement non finalisé</span>
                    </div>
                )}
            </div>
        </div>
      </div>

      <style jsx>{`
        .x-small { font-size: 0.75rem; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; }
        .admin-detail label { margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
      `}</style>
    </div>
  );
}
