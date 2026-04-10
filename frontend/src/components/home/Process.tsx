"use client";

import { motion } from "framer-motion";
import Link from "next/link";

const steps = [
  { id: 1, title: "Créez votre compte", desc: "Inscrivez-vous en quelques minutes avec vos informations personnelles", btn: "S'inscrire", link: "/register" },
  { id: 2, title: "Soumettez votre demande", desc: "Remplissez le formulaire en ligne et téléchargez les documents requis", btn: "Commencer", link: "/login" },
  { id: 3, title: "Suivez votre dossier", desc: "Consultez l'état d'avancement et recevez des notifications en temps réel", btn: "Suivre", link: "/login" },
  { id: 4, title: "Récupérez votre document", desc: "Retirez votre document final au centre indiqué ou recevez-le par courrier", btn: "En savoir plus", link: "/login", isSpecial: true },
];

export default function Process() {
  return (
    <section className="py-5 bg-light overflow-hidden">
      <div className="container">
        <div className="text-center mb-5">
          <span className="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-2">Processus</span>
          <h2 className="display-5 fw-bold text-dark">Comment ça marche ?</h2>
          <p className="lead text-muted mx-auto" style={{ maxWidth: 700 }}>Un processus simple en 4 étapes pour obtenir votre document</p>
        </div>
        
        <div className="position-relative">
          {/* Connecting line for desktop */}
          <div className="d-none d-md-block position-absolute top-0 start-50 translate-middle-x" style={{ width: '80%', height: 2, background: '#e0e0e0', top: 35, zIndex: 0 }}></div>
          
          <div className="row g-4 position-relative" style={{ zIndex: 1 }}>
            {steps.map((step, i) => (
              <div key={i} className="col-md-3">
                <motion.div 
                  className="text-center px-3"
                  initial={{ opacity: 0, scale: 0.8 }}
                  whileInView={{ opacity: 1, scale: 1 }}
                  viewport={{ once: true }}
                  transition={{ delay: i * 0.1 }}
                >
                  <div className={`mx-auto mb-4 d-flex align-items-center justify-content-center shadow-sm ${step.isSpecial ? "bg-success" : "bg-primary"}`} style={{ width: 70, height: 70, borderRadius: '50%', color: 'white', border: '5px solid white' }}>
                    <span className="h3 mb-0 fw-bold">{step.id}</span>
                  </div>
                  <h4 className="h5 mb-3 fw-bold text-dark">{step.title}</h4>
                  <p className="text-muted mb-3 small">{step.desc}</p>
                  <Link href={step.link} className={`btn btn-sm rounded-pill px-3 ${step.isSpecial ? "btn-outline-success" : "btn-outline-primary"}`}>
                    {step.btn} <i className="bi bi-arrow-right ms-1"></i>
                  </Link>
                </motion.div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
