"use client";

import { motion } from "framer-motion";
import Link from "next/link";

const services = [
  {
    title: "Carte Nationale d'Identité",
    desc: "Demandez ou renouvelez votre CNI en quelques clics. Processus simplifié et suivi en temps réel.",
    icon: "bi-person-vcard",
    color: "primary",
    badge: "Populaire",
    badgeType: "success"
  },
  {
    title: "Certificat de Nationalité",
    desc: "Obtenez votre certificat de nationalité en ligne. Procédure sécurisée et vérification rapide.",
    icon: "bi-flag",
    color: "success",
    badge: "Nouveau",
    badgeType: "warning"
  },
  {
    title: "Suivi de Demande",
    desc: "Suivez l'état de vos demandes en temps réel. Notifications automatiques à chaque étape du processus.",
    icon: "bi-search",
    color: "info",
    badge: "Pratique",
    badgeType: "secondary"
  }
];

export default function Services() {
  return (
    <section id="services" className="py-5">
      <div className="container">
        <div className="text-center mb-5">
          <motion.span 
            className="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-2"
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            viewport={{ once: true }}
          >
            Nos Services
          </motion.span>
          <motion.h2 
            className="display-5 fw-bold"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
          >
            Solutions Numériques
          </motion.h2>
          <p className="lead text-muted mx-auto" style={{ maxWidth: 700 }}>
            Découvrez nos services numériques conçus pour simplifier vos démarches administratives
          </p>
        </div>

        <div className="row g-4">
          {services.map((s, i) => (
            <div key={i} className="col-lg-4 col-md-6">
              <motion.div 
                className="card h-100 border-0 shadow-sm hover-card rounded-4"
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1 }}
                whileHover={{ y: -10 }}
              >
                <div className="card-body p-4">
                  <div className={`bg-${s.color} bg-opacity-10 text-${s.color} rounded-circle p-3 mb-4 d-inline-flex`}>
                    <i className={`bi ${s.icon} fs-3`}></i>
                  </div>
                  <h3 className="h4 mb-3">{s.title}</h3>
                  <p className="text-muted mb-4">{s.desc}</p>
                  <div className="d-flex justify-content-between align-items-center">
                    <Link href="/login" className={`btn btn-outline-${s.color} rounded-pill px-4`}>
                      Commencer <i className="bi bi-arrow-right ms-2"></i>
                    </Link>
                    <span className={`badge bg-${s.badgeType} bg-opacity-10 text-${s.badgeType} px-3 py-2`}>
                      {s.badge}
                    </span>
                  </div>
                </div>
              </motion.div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
