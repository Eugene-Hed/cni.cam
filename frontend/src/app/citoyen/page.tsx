"use client";

import { useState, useEffect } from "react";
import { motion } from "framer-motion";
import { 
  FileText, 
  Clock, 
  CheckCircle, 
  AlertCircle,
  ArrowRight,
  ChevronRight
} from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";

export default function CitizenDashboard() {
  const [demandes, setDemandes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDemandes = async () => {
        try {
            const resp = await apiClient.get("/demandes");
            setDemandes(resp.data.data);
        } catch (err) { console.error(err); }
        finally { setLoading(false); }
    };
    fetchDemandes();
  }, []);

  const stats = [
    { label: "Demandes Totales", value: demandes.length, icon: FileText, color: "primary" },
    { label: "En Cours", value: demandes.filter(d => d.Statut === 'EnCours' || d.Statut === 'Soumise').length, icon: Clock, color: "warning" },
    { label: "Approuvées", value: demandes.filter(d => d.Statut === 'Approuvee').length, icon: CheckCircle, color: "success" },
    { label: "Rejetées", value: demandes.filter(d => d.Statut === 'Rejetee').length, icon: AlertCircle, color: "danger" },
  ];

  return (
    <div className="citizen-dashboard">
        <header className="mb-5">
            <h1 className="fw-bold h2 mb-2">Bienvenue sur votre Espace Citoyen</h1>
            <p className="text-muted">Gérez vos demandes de CNI et de Certificat de Nationalité.</p>
        </header>

        {/* Quick Stats */}
        <div className="row g-4 mb-5">
            {stats.map((s, i) => {
                const Icon = s.icon;
                return (
                    <div key={i} className="col-lg-3 col-sm-6">
                        <motion.div 
                            className="card border-0 shadow-sm rounded-4 p-3 h-100"
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: i * 0.1 }}
                        >
                            <div className="d-flex align-items-center gap-3">
                                <div className={`bg-${s.color} bg-opacity-10 text-${s.color} rounded-circle p-3`}>
                                    <Icon size={24} />
                                </div>
                                <div>
                                    <p className="text-muted small mb-0 fw-medium">{s.label}</p>
                                    <h3 className="fw-bold mb-0">{s.value}</h3>
                                </div>
                            </div>
                        </motion.div>
                    </div>
                );
            })}
        </div>

        <div className="row g-4">
            {/* Recent Requests */}
            <div className="col-lg-8">
                <div className="card border-0 shadow-sm rounded-4 h-100">
                    <div className="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                        <h5 className="fw-bold mb-0">Mes Demandes Récentes</h5>
                        <Link href="/citoyen/demandes" className="text-primary text-decoration-none small fw-bold">
                            Voir tout <ChevronRight size={16} />
                        </Link>
                    </div>
                    <div className="card-body p-0">
                        {loading ? (
                            <div className="p-5 text-center text-muted">Chargement...</div>
                        ) : demandes.length === 0 ? (
                            <div className="p-5 text-center">
                                <FileText size={48} className="text-muted opacity-25 mb-3" />
                                <p className="text-muted">Vous n'avez pas encore de demande.</p>
                                <Link href="/citoyen/demandes/nouvelle" className="btn btn-primary rounded-pill px-4 mt-2">
                                    Commencer une demande
                                </Link>
                            </div>
                        ) : (
                            <div className="table-responsive">
                                <table className="table align-middle mb-0">
                                    <thead className="bg-light">
                                        <tr>
                                            <th className="px-4 py-3 text-muted small fw-bold">Référence</th>
                                            <th className="py-3 text-muted small fw-bold">Type</th>
                                            <th className="py-3 text-muted small fw-bold">Date</th>
                                            <th className="py-3 text-muted small fw-bold">Statut</th>
                                            <th className="py-3 text-muted small fw-bold"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {demandes.slice(0, 5).map((d) => (
                                            <tr key={d.DemandeID}>
                                                <td className="px-4 fw-medium text-dark">{d.NumeroReference}</td>
                                                <td>
                                                    <span className={`badge ${d.TypeDemande === 'CNI' ? 'bg-primary' : 'bg-success'} bg-opacity-10 text-${d.TypeDemande === 'CNI' ? 'primary' : 'success'} fw-medium`}>
                                                        {d.TypeDemande}
                                                    </span>
                                                </td>
                                                <td className="small text-muted">{new Date(d.DateSoumission).toLocaleDateString()}</td>
                                                <td>
                                                    <span className={`badge rounded-pill ${
                                                        d.Statut === 'Terminee' ? 'bg-success' : 
                                                        d.Statut === 'Rejetee' ? 'bg-danger' : 
                                                        'bg-warning'
                                                    } px-3`}>
                                                        {d.Statut}
                                                    </span>
                                                </td>
                                                <td className="text-end px-4">
                                                    <Link href={`/citoyen/demandes/${d.DemandeID}`} className="btn btn-light btn-sm rounded-circle p-2">
                                                        <ArrowRight size={16} />
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* AI Assistant Help Card */}
            <div className="col-lg-4">
                <div className="card boder-0 shadow-sm border-0 rounded-4 bg-primary text-white p-4 h-100 position-relative overflow-hidden">
                    <div className="position-relative z-2">
                        <h4 className="fw-bold mb-3">Besoin d'aide ?</h4>
                        <p className="small mb-4 text-white-90">Notre assistant IA est là pour vous guider dans vos démarches d'obtention de CNI.</p>
                        <Link href="/citoyen/support" className="btn btn-light btn-sm rounded-pill px-4 py-2 fw-bold text-primary">
                            Discuter avec l'assistant
                        </Link>
                    </div>
                    <div className="position-absolute bottom-0 end-0 p-3 opacity-25">
                        <i className="bi bi-robot" style={{ fontSize: 100 }}></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
  );
}
