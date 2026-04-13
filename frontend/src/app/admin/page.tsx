"use client";

import { useState, useEffect } from "react";
import { motion } from "framer-motion";
import { Users, FileText, Activity, ShieldCheck, Clock, Loader2, ArrowRight } from "lucide-react";
import apiClient from "@/lib/api-client";
import Link from "next/link";

export default function AdminDashboard() {
  const [stats, setStats] = useState<any>(null);
  const [logs, setLogs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchData = async () => {
    try {
      const [statsResp, logsResp] = await Promise.all([
        apiClient.get("/admin/dashboard"),
        apiClient.get("/admin/journal")
      ]);
      setStats(statsResp.data.stats);
      // Take only first 5 logs for the home feed
      const allLogs = logsResp.data.data?.data || logsResp.data.data || [];
      setLogs(allLogs.slice(0, 5));
    } catch (err) {
      console.error("Dashboard Sync Error", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const kpis = [
    { title: "Utilisateurs Totaux", value: stats?.total_utilisateurs || 0, icon: Users, color: "primary" },
    { title: "Demandes Globales", value: stats?.total_demandes || 0, icon: FileText, color: "success" },
    { title: "En Attente", value: stats?.en_attente || 0, icon: Clock, color: "warning" },
    { title: "Alertes Système", value: "0", icon: ShieldCheck, color: "info" },
  ];

  // Calculate percentages for distribution
  const citoyenPct = stats?.total_utilisateurs > 0 ? (stats.citoyens / stats.total_utilisateurs) * 100 : 0;
  const officierPct = stats?.total_utilisateurs > 0 ? (stats.officiers / stats.total_utilisateurs) * 100 : 0;
  const adminPct = stats?.total_utilisateurs > 0 ? (1 - (stats.citoyens + stats.officiers) / stats.total_utilisateurs) * 100 : 0;

  return (
    <div>
      <div className="mb-5 d-flex justify-content-between align-items-center">
        <div>
          <h2 className="fw-bold">Tableau de Bord Administrateur</h2>
          <p className="text-muted mb-0">Vue d'ensemble de la plateforme CNI.CAM</p>
        </div>
        <button className="btn btn-sm btn-outline-primary rounded-pill px-3" onClick={fetchData} disabled={loading}>
            {loading ? <Loader2 className="animate-spin" size={16} /> : "Rafraîchir"}
        </button>
      </div>

      <div className="row g-4 mb-5">
        {kpis.map((kpi, i) => {
          const Icon = kpi.icon;
          return (
            <div key={i} className="col-md-3">
              <motion.div 
                className="card border-0 shadow-sm p-4 rounded-4 h-100"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.1 }}
              >
                <div className={`icon-box bg-${kpi.color}-light text-${kpi.color} mb-3`}>
                  <Icon size={24} />
                </div>
                <h3 className="fw-bold mb-1">{kpi.value}</h3>
                <p className="text-muted small mb-0">{kpi.title}</p>
              </motion.div>
            </div>
          );
        })}
      </div>

      <div className="row g-4">
        <div className="col-lg-8">
            <div className="card boder-0 shadow-sm border-0 rounded-4 p-4 mb-4" style={{ minHeight: '400px' }}>
                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h5 className="fw-bold mb-0">Dernières Activités</h5>
                    <Link href="/admin/journal" className="text-primary small text-decoration-none fw-bold">Tout voir <ArrowRight size={14} className="ms-1" /></Link>
                </div>
                
                {loading ? (
                    <div className="text-center py-5 vstack gap-2 text-muted">
                        <Loader2 className="animate-spin mx-auto" />
                        <span className="small">Chargement des logs système...</span>
                    </div>
                ) : logs.length === 0 ? (
                    <div className="text-center py-5 text-muted small">Aucune activité enregistrée.</div>
                ) : (
                    <div className="table-responsive">
                        <table className="table table-sm align-middle mb-0">
                            <tbody>
                                {logs.map((log) => (
                                    <tr key={log.LogID}>
                                        <td className="py-3 items-center d-flex gap-3 border-0">
                                            <div className="bg-light p-2 rounded-circle">
                                                <Activity size={14} className="text-muted" />
                                            </div>
                                            <div>
                                                <div className="small fw-bold">{log.Description}</div>
                                                <div className="x-small text-muted">{log.utilisateur?.Prenom} {log.utilisateur?.Nom} • {new Date(log.DateHeure).toLocaleTimeString()}</div>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
        <div className="col-lg-4">
            <div className="card border-0 shadow-sm p-4 rounded-4 mb-4 h-100">
                <h5 className="fw-bold mb-4">Répartition Rôles</h5>
                <div className="d-flex flex-column gap-4">
                    <div className="d-flex flex-column gap-1">
                        <div className="d-flex justify-content-between align-items-center">
                            <span className="small text-muted">Citoyens</span>
                            <span className="fw-bold small">{Math.round(citoyenPct)}%</span>
                        </div>
                        <div className="progress" style={{ height: 10 }}>
                            <div className="progress-bar bg-primary rounded-pill" style={{ width: `${citoyenPct}%` }}></div>
                        </div>
                    </div>
                    <div className="d-flex flex-column gap-1">
                        <div className="d-flex justify-content-between align-items-center">
                            <span className="small text-muted">Officiers</span>
                            <span className="fw-bold small">{Math.round(officierPct)}%</span>
                        </div>
                        <div className="progress" style={{ height: 10 }}>
                            <div className="progress-bar bg-success rounded-pill" style={{ width: `${officierPct}%` }}></div>
                        </div>
                    </div>
                    <div className="d-flex flex-column gap-1">
                        <div className="d-flex justify-content-between align-items-center">
                            <span className="small text-muted">Administrateurs</span>
                            <span className="fw-bold small">{Math.round(adminPct)}%</span>
                        </div>
                        <div className="progress" style={{ height: 10 }}>
                            <div className="progress-bar bg-warning rounded-pill" style={{ width: `${adminPct}%` }}></div>
                        </div>
                    </div>
                </div>
                
                <div className="mt-auto pt-4 text-center">
                    <Link href="/admin/utilisateurs" className="btn btn-light btn-sm rounded-pill px-4 w-100 border text-decoration-none text-dark small fw-bold">Gérer les accès</Link>
                </div>
            </div>
        </div>
      </div>

      <style jsx>{`
        .icon-box {
          width: 48px;
          height: 48px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 12px;
        }
        .bg-primary-light { background-color: #e7f1ff; }
        .bg-success-light { background-color: #e6fcf5; }
        .bg-warning-light { background-color: #fff9db; }
        .bg-info-light { background-color: #e7f5ff; }
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .x-small { font-size: 0.7rem; }
      `}</style>
    </div>
  );
}
