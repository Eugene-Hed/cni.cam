"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { Loader2 } from "lucide-react";
import apiClient from "@/lib/api-client";

export default function LogoutPage() {
  const router = useRouter();

  useEffect(() => {
    const performLogout = async () => {
      try {
        // Call backend to invalidate token if it exists
        if (localStorage.getItem("auth_token")) {
          await apiClient.post("/auth/logout");
        }
      } catch (err) {
        console.error("Erreur déconnexion serveur", err);
      } finally {
        // Always clear local data regardless of API success
        localStorage.removeItem("auth_token");
        localStorage.removeItem("user");
        
        // Redirect to homepage
        router.push("/");
        router.refresh();
      }
    };

    performLogout();
  }, [router]);

  return (
    <div className="min-vh-100 d-flex flex-column align-items-center justify-content-center bg-light">
      <Loader2 className="text-primary animate-spin mb-3" size={48} />
      <h4 className="fw-bold">Déconnexion en cours...</h4>
      <p className="text-muted">Merci d'avoir utilisé CNI.CAM</p>
      
      <style jsx>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
        .animate-spin {
          animation: spin 1s linear infinite;
        }
      `}</style>
    </div>
  );
}
