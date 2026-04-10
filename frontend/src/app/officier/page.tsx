"use client";

import { useState, useEffect } from "react";
import { motion } from "framer-motion";
import { 
  Users, 
  Clock, 
  CheckCircle, 
  AlertCircle,
  Eye,
  Settings
} from "lucide-react";
import Link from "next/link";
import apiClient from "@/lib/api-client";

export default function OfficerDashboard() {
  const [demandes, setDemandes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
        try {
            const resp = await apiClient.get("/officier/demandes");
            setDemandes(resp.data.data.data || []); // pagination data
        } catch (err) { console.error(err); }
        finally { setLoading(false); }
    };
    fetchData();
  }, []);

  const stats = [
    { label: "Total Dossiers", value: demandes.length, icon: Users, color: "primary" },
    { label: "En Attente", value: demandes.filter(d => d.Statut === 'Soumise').length, icon: Clock, color: "warning" },
    { label: "Approuvés", value: demandes.filter(d => d.Statut === 'Approuvee').length, icon: CheckCircle, color: "success" },
    { label: "Rejetés", value: demandes.filter(d => d.Statut === 'Rejetee').length, icon: AlertCircle, color: "danger" },
  ];

  return (
    <div className="officer-dashboard">
        <header className="mb-5 d-flex justify-content-between align-items-center">
            <div>
                <h1 className="fw-bold h2 mb-1">Espace Officier</h1>
                <p className="text-muted">Traitement et validation des demandes de CNI.</p>
            </div>
            <div className="d-flex gap-2">
                <button className="btn btn-outline-primary rounded-pill px-4">Exporter</button>
                <button className="btn btn-primary rounded-pill px-4">Nouveau Centre</button>
            </div>
        </header>

        {/* Stats Grid */}
        <div className="row g-4 mb-5">
            {stats.map((s, i) => {
                const Icon = s.icon;
                return (
                    <div key={i} className="col-lg-3 col-sm-6">
                        <div className="card border-0 shadow-sm rounded-4 p-4">
                            <div className="d-flex justify-content-between align-items-start mb-3">
                                <div className={`bg-${s.color} bg-opacity-10 text-${s.color} rounded-circle p-2`}>
                                    <Icon size={20} />
                                </div>
                                <span className="text-success small fw-bold">+12%</span>
                            </div>
                            <h3 className="fw-bold mb-1">{s.value}</h3>
                            <p className="text-muted small mb-0 fw-medium">{s.label}</p>
                        </div>
                    </div>
                );
            })}
        </div>

        {/* Pending Requests Table */}
        <div className="card border-0 shadow-sm rounded-4">
            <div className="card-header bg-white border-0 p-4">
                <div className="d-flex justify-content-between align-items-center">
                    <h5 className="fw-bold mb-0">Demandes à traiter</h5>
                    <div className="d-flex gap-2">
                        <input type="text" className="form-control form-control-sm rounded-pill" placeholder="Filtrer..." style={{ width: 200 }} />
                    </div>
                </div>
            </div>
            <div className="card-body p-0">
                <div className="table-responsive">
                    <table className="table align-middle mb-0">
                        <thead className="bg-light">
                            <tr>
                                <th className="px-4 py-3 text-muted small fw-bold">RÉFÉRENCE</th>
                                <th className="py-3 text-muted small fw-bold">CITOYEN</th>
                                <th className="py-3 text-muted small fw-bold">DATE</th>
                                <th className="py-3 text-muted small fw-bold">LIAISON</th>
                                <th className="py-3 text-muted small fw-bold">STATUT</th>
                                <th className="py-3 text-muted small fw-bold">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan={6} className="p-5 text-center text-muted">Chargement en cours...</td></tr>
                            ) : demandes.map((d) => (
                                <tr key={d.DemandeID}>
                                    <td className="px-4 fw-bold">{d.NumeroReference}</td>
                                    <td>
                                        <div className="d-flex align-items-center gap-2">
                                            <div className="bg-light rounded-circle fw-bold small d-flex align-items-center justify-content-center" style={{ width: 30, height: 30 }}>
                                                {d.utilisateur?.Prenom?.[0]}
                                            </div>
                                            <span className="small">{d.utilisateur?.Prenom} {d.utilisateur?.Nom}</span>
                                        </div>
                                    </td>
                                    <td className="small">{new Date(d.DateSoumission).toLocaleDateString()}</td>
                                    <td className="small">{d.details_cni?.LieuNaissance}</td>
                                    <td>
                                        <span className={`badge rounded-pill ${
                                            d.Statut === 'Soumise' ? 'bg-warning' : 
                                            d.Statut === 'EnCours' ? 'bg-primary' : 
                                            'bg-success'
                                        } px-3 text-white`}>
                                            {d.Statut}
                                        </span>
                                    </td>
                                    <td>
                                        <div className="d-flex gap-2">
                                            <Link href={`/officier/demandes/${d.DemandeID}`} className="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-1">
                                                <Eye size={14} /> Traiter
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  );
}
