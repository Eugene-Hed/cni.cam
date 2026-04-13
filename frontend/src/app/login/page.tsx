"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import apiClient from "@/lib/api-client";
import { useRouter } from "next/navigation";

export default function LoginPage() {
  const [identifiant, setIdentifiant] = useState("");
  const [methode, setMethode] = useState<"email" | "telephone">("email");
  const [step, setStep] = useState<"identifiant" | "otp">("identifiant");
  const [otp, setOtp] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const router = useRouter();

  const handleSendOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    try {
      await apiClient.post("/auth/login", { identifiant, methode });
      setStep("otp");
    } catch (err: any) {
      setError(err.response?.data?.message || "Identifiant introuvable ou erreur serveur.");
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    try {
      const resp = await apiClient.post("/auth/verify-otp", { identifiant, methode, code: otp });
      localStorage.setItem("auth_token", resp.data.token);
      localStorage.setItem("user", JSON.stringify(resp.data.user));
      
      // Redirect based on role
      const role = resp.data.user.role_id;
      if (role === 1) router.push("/admin");
      else if (role === 2) router.push("/citoyen");
      else if (role === 3) router.push("/officier");
      else if (role === 4) router.push("/president");
    } catch (err: any) {
      setError(err.response?.data?.message || "Code OTP invalide.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-container d-flex align-items-center justify-content-center min-vh-100 py-5">
      <motion.div 
        className="login-card bg-white rounded-4 shadow-lg overflow-hidden"
        initial={{ opacity: 0, y: 30 }}
        animate={{ opacity: 1, y: 0 }}
      >
        <div className="text-center p-5 bg-light border-bottom">
            <Link href="/">
                <Image src="/assets/images/Cameroun.png" alt="Logo" width={80} height={80} className="mb-3" />
            </Link>
            <h2 className="fw-bold mb-1">Connexion</h2>
            <p className="text-muted mb-0">Plateforme CNI.CAM</p>
        </div>

        <div className="p-4 p-md-5">
            {error && <div className="alert alert-danger small mb-4">{error}</div>}

            <AnimatePresence mode="wait">
                {step === "identifiant" ? (
                    <motion.form 
                        key="id"
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        exit={{ opacity: 0, x: 20 }}
                        onSubmit={handleSendOtp}
                    >
                        <div className="mb-4">
                            <label className="form-label fw-bold small">Méthode de connexion</label>
                            <div className="btn-group w-100" role="group">
                                <button 
                                    type="button" 
                                    className={`btn btn-sm ${methode === "email" ? "btn-primary" : "btn-outline-primary"}`}
                                    onClick={() => setMethode("email")}
                                >Email</button>
                                <button 
                                    type="button" 
                                    className={`btn btn-sm ${methode === "telephone" ? "btn-primary" : "btn-outline-primary"}`}
                                    onClick={() => setMethode("telephone")}
                                >Téléphone</button>
                            </div>
                        </div>

                        <div className="mb-4">
                            <label className="form-label fw-bold small">
                                {methode === "email" ? "Adresse Email" : "Numéro de Téléphone"}
                            </label>
                            <input 
                                type={methode === "email" ? "email" : "text"}
                                className="form-control form-control-lg fs-6" 
                                placeholder={methode === "email" ? "exemple@domaine.cm" : "677xxxxxx"}
                                value={identifiant}
                                onChange={(e) => setIdentifiant(e.target.value)}
                                required
                            />
                        </div>

                        <button type="submit" className="btn btn-primary btn-lg w-100 rounded-pill shadow-sm" disabled={loading}>
                            {loading ? <span className="spinner-border spinner-border-sm me-2"></span> : <i className="bi bi-send me-2"></i>}
                            Recevoir le code OTP
                        </button>
                    </motion.form>
                ) : (
                    <motion.form 
                        key="otp"
                        initial={{ opacity: 0, x: 20 }}
                        animate={{ opacity: 1, x: 0 }}
                        exit={{ opacity: 0, x: -20 }}
                        onSubmit={handleVerifyOtp}
                    >
                        <div className="text-center mb-4">
                            <p className="small text-muted mb-1">Un code à 6 chiffres a été envoyé à :</p>
                            <p className="fw-bold">{identifiant}</p>
                        </div>

                        <div className="mb-4">
                            <label className="form-label fw-bold small text-center d-block">Code OTP</label>
                            <input 
                                type="text"
                                className="form-control form-control-lg fs-3 text-center ls-2" 
                                placeholder="000000"
                                maxLength={6}
                                value={otp}
                                onChange={(e) => setOtp(e.target.value)}
                                required
                                autoFocus
                            />
                        </div>

                        <button type="submit" className="btn btn-success btn-lg w-100 rounded-pill shadow-sm" disabled={loading}>
                            {loading ? <span className="spinner-border spinner-border-sm me-2"></span> : <i className="bi bi-shield-lock me-2"></i>}
                            Vérifier et Se connecter
                        </button>

                        <button 
                            type="button" 
                            className="btn btn-link w-100 text-muted small mt-3"
                            onClick={() => setStep("identifiant")}
                        >
                            Modifier l'identifiant
                        </button>
                    </motion.form>
                )}
            </AnimatePresence>
            
            <div className="text-center mt-5">
                <p className="small text-muted">
                    Vous n'avez pas de compte ? <Link href="/register" className="text-primary fw-bold text-decoration-none">Inscrivez-vous</Link>
                </p>
            </div>
        </div>
      </motion.div>

      <style jsx>{`
        .login-container {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }
        .login-card {
            width: 100%;
            max-width: 480px;
        }
        .ls-2 { letter-spacing: 0.5rem; }
      `}</style>
    </div>
  );
}
