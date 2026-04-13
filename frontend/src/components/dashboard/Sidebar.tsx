"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { 
  LayoutDashboard, 
  FileText, 
  PlusCircle, 
  Bell, 
  User, 
  LogOut,
  HelpCircle,
  QrCode
} from "lucide-react";

export default function Sidebar() {
  const pathname = usePathname();
  const [role, setRole] = useState<number>(2);

  useEffect(() => {
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    if (user.role_id) setRole(user.role_id);
  }, []);

  const getMenuItems = () => {
    if (role === 1) { // Admin
      return [
        { name: "Tableau de Bord", icon: LayoutDashboard, href: "/admin" },
        { name: "Utilisateurs", icon: User, href: "/admin/utilisateurs" },
        { name: "Toutes les Demandes", icon: FileText, href: "/admin/demandes" },
        { name: "Journal d'Activités", icon: Bell, href: "/admin/journal" },
      ];
    }
    if (role === 3) { // Officier
      return [
        { name: "Tableau de Bord", icon: LayoutDashboard, href: "/officier" },
        { name: "Dossiers CNI", icon: FileText, href: "/officier/demandes" },
      ];
    }
    if (role === 4) { // President
      return [
        { name: "Tableau de Bord", icon: LayoutDashboard, href: "/president" },
        { name: "Demandes Nationalité", icon: FileText, href: "/president/demandes" },
      ];
    }
    return [ // Citoyen (Default)
      { name: "Tableau de Bord", icon: LayoutDashboard, href: "/citoyen" },
      { name: "Mes Demandes", icon: FileText, href: "/citoyen/demandes" },
      { name: "Nouvelle Demande", icon: PlusCircle, href: "/citoyen/demandes/nouvelle" },
      { name: "Assistant IA", icon: HelpCircle, href: "/citoyen/assistant" },
      { name: "Mes Documents", icon: QrCode, href: "/citoyen/documents" },
    ];
  };

  const getDashboardLink = () => {
    if (role === 1) return "/admin";
    if (role === 3) return "/officier";
    if (role === 4) return "/president";
    return "/citoyen";
  };

  const menuItems = getMenuItems();

  return (
    <div className="d-flex flex-column bg-white h-100 border-end" style={{ width: 280 }}>
      <div className="p-4 border-bottom">
        <Link href={getDashboardLink()} className="text-decoration-none">
          <span className="fw-bold fs-4 text-primary">CNI<span className="text-warning">.CAM</span></span>
        </Link>
      </div>

      <div className="flex-grow-1 p-3 overflow-auto">
        <ul className="nav nav-pills flex-column gap-1">
          {menuItems.map((item) => {
            const Icon = item.icon;
            const active = pathname === item.href;
            return (
              <li key={item.name}>
                <Link 
                  href={item.href}
                  className={`nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 transition-all ${active ? "bg-primary text-white shadow-sm" : "text-muted hover-bg-light"}`}
                >
                  <Icon size={20} />
                  <span className="fw-medium">{item.name}</span>
                </Link>
              </li>
            );
          })}
        </ul>
      </div>

      <div className="p-3 border-top mt-auto">
        <Link 
            href="/profil" 
            className={`nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 transition-all mb-1 ${pathname === '/profil' ? "bg-light text-primary fw-bold" : "text-muted hover-bg-light"}`}
        >
          <User size={20} />
          <span className="fw-medium">Mon Profil</span>
        </Link>
        <Link 
            href="/logout" 
            className="nav-link d-flex align-items-center gap-3 py-3 px-4 text-danger rounded-3 transition-all hover-bg-danger-light"
        >
          <LogOut size={20} />
          <span className="fw-medium">Déconnexion</span>
        </Link>
      </div>

      <style jsx>{`
        .transition-all { transition: all 0.2s ease; }
        .hover-bg-light:hover { background-color: #f8f9fa; color: #1774df !important; }
        .hover-bg-danger-light:hover { background-color: #fff5f5; }
      `}</style>
    </div>
  );
}
