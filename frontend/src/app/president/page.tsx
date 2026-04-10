"use client";

import { useState, useEffect } from "react";
import { 
  FileCheck, 
  Hourglass, 
  ShieldCheck, 
  XCircle,
  Eye,
  Download
} from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";

export default function PresidentDashboard() {
  const [demandes, setDemandes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
        try {
            const resp = await apiClient.get("/president/demandes");
            setDemandes(resp.data.data.data || []);
        } catch (err) { console.error(err); }
        finally { setLoading(false); }
    };
    fetchData();
  }, []);

  const stats = [
    { label: "Certificats à Valider", value: demandes.filter(d => d.Statut === 'Soumise').length, icon: Hourglass, color: "warning" },
    { label: "Total Émis", value: demandes.filter(d => d.Statut === 'Terminee').length, icon: ShieldCheck, color: "success" },
  ];

  return (
    <div className="president-dashboard">
        <header className="mb-5">
            <h1 className="fw-bold h2 mb-1">Espace Présidence</h1>
            <p className="text-muted">Homologation des Certificats de Nationalité.</p>
        </header>

        <div className="row g-4 mb-5">
            {stats.map((s, i) => {
                const Icon = s.icon;
                return (
                    <div key={i} className="col-md-6">
                        <div className="card border-0 shadow-sm rounded-4 p-4 text-center">
                            <div className={`bg-${s.color} bg-opacity-10 text-${s.color} rounded-circle p-3 d-inline-flex mb-3`}>
                                <Icon size={32} />
                            </div>
                            <h2 className="fw-bold mb-1">{s.value}</h2>
                            <p className="text-muted fw-bold mb-0">{s.label}</p>
                        </div>
                    </div>
                );
            })}
        </div>

        <div className="card border-0 shadow-sm rounded-4">
            <div className="card-header bg-white border-bottom p-4">
                <h5 className="fw-bold mb-0">Demandes de Nationalité</h5>
            </div>
            <div className="table-responsive">
                <table className="table align-middle mb-0">
                    <thead className="bg-light">
                        <tr>
                            <th className="px-4 py-3 text-muted small">RÉFÉRENCE</th>
                            <th className="py-3 text-muted small">BÉNÉFICIAIRE</th>
                            <th className="py-3 text-muted small">MOTIF</th>
                            <th className="py-3 text-muted small">DATE</th>
                            <th className="py-3 text-muted small">STATUT</th>
                            <th className="py-3 text-muted small text-end px-4">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr><td colSpan={6} className="text-center p-5 text-muted">Chargement...</td></tr>
                        ) : demandes.map(d => (
                            <tr key={d.DemandeID}>
                                <td className="px-4 fw-bold text-primary">{d.NumeroReference}</td>
                                <td>{d.utilisateur?.Prenom} {d.utilisateur?.Nom}</td>
                                <td><span className="small text-muted">{d.details_nationalite?.Motif}</span></td>
                                <td className="small">{new Date(d.DateSoumission).toLocaleDateString()}</td>
                                <td>
                                    <span className={`badge rounded-pill ${d.Statut === 'Approuvee' ? 'bg-primary' : 'bg-success'} px-3`}>
                                       {d.Statut}
                                    </span>
                                </td>
                                <td className="text-end px-4">
                                    <Link href={`/president/demandes/${d.DemandeID}`} className="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        Vérifier <Eye size={14} className="ms-1" />
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  );
}
