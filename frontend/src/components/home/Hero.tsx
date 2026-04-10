"use client";

import { motion } from "framer-motion";
import Link from "next/link";
import Image from "next/image";

export default function Hero() {
  return (
    <section className="hero position-relative overflow-hidden pt-5">
      <div className="hero-bg-animation"></div>
      <div className="hero-overlay"></div>
      
      <div className="container position-relative py-5">
        <div className="row align-items-center min-vh-75 pt-5">
          <motion.div 
            className="col-lg-6 mb-5 mb-lg-0"
            initial={{ opacity: 0, x: -50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, ease: "easeOut" }}
          >
            <h1 className="display-4 fw-bold text-white mb-3">
              Gestion Numérique des <span className="text-warning">CNI</span>
            </h1>
            <p className="lead text-white-90 mb-4 fs-5">
              Simplifiez vos démarches administratives avec la plateforme officielle de gestion des Cartes Nationales d'Identité du Cameroun.
            </p>
            
            <div className="d-flex flex-wrap gap-3">
              <motion.div whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}>
                <Link href="/register" className="btn btn-primary btn-lg px-4 shadow-sm rounded-pill d-flex align-items-center">
                  <i className="bi bi-person-plus me-2"></i>Créer un compte
                </Link>
              </motion.div>
              <motion.div whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}>
                <Link href="/login" className="btn btn-outline-light btn-lg px-4 rounded-pill d-flex align-items-center">
                  <i className="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </Link>
              </motion.div>
            </div>

            <div className="mt-5 d-flex align-items-center">
              <div className="d-flex">
                {[1, 2, 3].map((i) => (
                  <div key={i} className="bg-white rounded-circle border border-2 border-primary shadow d-flex align-items-center justify-content-center overflow-hidden" style={{ width: 40, height: 40, marginLeft: i > 1 ? -10 : 0, zIndex: 4 - i }}>
                    <Image src={`/assets/images/user-${i}.jpg`} alt="u" width={40} height={40} className="object-cover" />
                  </div>
                ))}
              </div>
              <div className="ms-3 text-white-90 small">
                <span className="fw-bold text-white">+10,000</span> citoyens nous font confiance
              </div>
            </div>
          </motion.div>

          <motion.div 
            className="col-lg-6 d-none d-lg-block text-center"
            initial={{ opacity: 0, scale: 0.8 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 1, delay: 0.2 }}
          >
            <div className="position-relative" style={{ animation: "float 6s ease-in-out infinite" }}>
                <Image 
                    src="/assets/images/Cameroun.png" 
                    alt="CNI Cameroun" 
                    width={400} 
                    height={400} 
                    className="img-fluid rounded-4 shadow-lg"
                />
                <div className="position-absolute top-0 end-0 translate-middle-y bg-white p-3 rounded-4 shadow-lg d-flex align-items-center" style={{ maxWidth: 200 }}>
                    <div className="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                        <i className="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div className="text-start">
                        <h6 className="mb-0 fw-bold text-dark">Processus simplifié</h6>
                        <p className="small text-muted mb-0">Délai réduit de 70%</p>
                    </div>
                </div>
            </div>
          </motion.div>
        </div>
      </div>

      <div className="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 100">
          <path fill="#ffffff" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,42.7C1120,32,1280,32,1360,32L1440,32L1440,100L1360,100C1280,100,1120,100,960,100C800,100,640,100,480,100C320,100,160,100,80,100L0,100Z"></path>
        </svg>
      </div>

      <style jsx>{`
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            min-height: 85vh;
        }
        .hero-bg-animation {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('/assets/images/pattern.png') repeat;
            opacity: 0.05;
            animation: slide 60s linear infinite;
        }
        .hero-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at top right, rgba(23, 116, 223, 0.4) 0%, rgba(19, 91, 178, 0.8) 100%);
        }
        .hero-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
        }
      `}</style>
    </section>
  );
}
