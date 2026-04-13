"use client";

import { useState, useEffect } from "react";
import { Bell, Search, User, Menu, LogOut } from "lucide-react";
import Link from "next/link";

export default function TopBar() {
  const [user, setUser] = useState<any>(null);

  useEffect(() => {
    const stored = localStorage.getItem("user");
    if (stored) setUser(JSON.parse(stored));
  }, []);

  return (
    <header className="navbar bg-white border-bottom sticky-top py-3 px-4">
      <div className="container-fluid g-0">
        <div className="d-flex align-items-center gap-3">
          <button className="btn btn-link p-0 d-lg-none text-muted">
            <Menu size={24} />
          </button>
          <div className="d-none d-md-flex position-relative" style={{ width: 300 }}>
             <Search className="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" size={18} />
             <input type="text" className="form-control bg-light border-0 ps-5 rounded-pill" placeholder="Rechercher une demande..." />
          </div>
        </div>

        <div className="d-flex align-items-center gap-3">
          <button className="btn btn-light rounded-circle p-2 position-relative">
            <Bell size={20} />
            <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white" style={{ fontSize: 10 }}>
              3
            </span>
          </button>
          
          <div className="vr mx-2 bg-secondary opacity-25" style={{ height: 30 }}></div>
          
          <div className="dropdown">
            <button 
                className="btn p-0 d-flex align-items-center gap-2 border-0 shadow-none" 
                type="button" 
                id="userDropdown" 
                data-bs-toggle="dropdown" 
                aria-expanded="false"
            >
                <div className="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style={{ width: 35, height: 35 }}>
                    {user?.prenom?.[0] || user?.Prenom?.[0] || "U"}
                </div>
                <div className="text-start d-none d-sm-block">
                    <p className="mb-0 small fw-bold text-dark">{user?.prenom || user?.Prenom} {user?.nom || user?.Nom}</p>
                    <p className="mb-0 text-muted" style={{ fontSize: 10 }}>{user?.code || user?.Codeutilisateur || "Citoyen"}</p>
                </div>
            </button>
            <ul className="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" aria-labelledby="userDropdown">
                <li>
                    <Link href="/profil" className="dropdown-item py-2 small d-flex align-items-center gap-2">
                        <User size={16} className="text-muted" /> Mon Profil
                    </Link>
                </li>
                <li><hr className="dropdown-divider opacity-50" /></li>
                <li>
                    <Link href="/logout" className="dropdown-item py-2 small d-flex align-items-center gap-2 text-danger">
                        <LogOut size={16} /> Déconnexion
                    </Link>
                </li>
            </ul>
          </div>
        </div>
      </div>
    </header>
  );
}
