"use client";

import Link from "next/link";

export default function Footer() {
  return (
    <footer className="bg-dark text-white pt-5 pb-4">
      <div className="container">
        <div className="row g-4">
          <div className="col-lg-4 col-md-6">
            <h5 className="fw-bold mb-4 text-white">CNI<span className="text-warning">.CAM</span></h5>
            <p className="text-white-50 small mb-4">
                Plateforme officielle de gestion numérique des titres d'identité en République du Cameroun. 
                Simplification, Transparence, Modernité.
            </p>
            <div className="d-flex gap-3">
              {['facebook', 'twitter', 'linkedin', 'instagram'].map(s => (
                <a key={s} href="#" className="text-white-50 hover-white">
                  <i className={`bi bi-${s} fs-5`}></i>
                </a>
              ))}
            </div>
          </div>
          
          <div className="col-lg-2 col-md-6">
            <h6 className="fw-bold mb-4">Services</h6>
            <ul className="list-unstyled small space-y-2">
              <li><Link href="/login" className="text-white-50 text-decoration-none hover-white">Carte d'Identité</Link></li>
              <li><Link href="/login" className="text-white-50 text-decoration-none hover-white">Certificat de Nationalité</Link></li>
              <li><Link href="/login" className="text-white-50 text-decoration-none hover-white">Suivi de demande</Link></li>
            </ul>
          </div>
          
          <div className="col-lg-2 col-md-6">
            <h6 className="fw-bold mb-4">Support</h6>
            <ul className="list-unstyled small space-y-2">
              <li><Link href="/citoyen/assistant" className="text-white-50 text-decoration-none hover-white">Aide / FAQ (IA)</Link></li>
              <li><Link href="/support" className="text-white-50 text-decoration-none hover-white">Contact</Link></li>
              <li><Link href="#" className="text-white-50 text-decoration-none hover-white">Centres d'enrôlement</Link></li>
            </ul>
          </div>
          
          <div className="col-lg-4 col-md-6">
            <h6 className="fw-bold mb-4">Newsletter</h6>
            <p className="text-white-50 small mb-4">Restez informé des mises à jour administratives.</p>
            <form className="d-flex gap-2">
              <input type="email" className="form-control form-control-sm bg-secondary border-0 text-white shadow-none" placeholder="Votre email" />
              <button className="btn btn-primary btn-sm px-3">Ok</button>
            </form>
          </div>
        </div>
        
        <hr className="my-4 border-secondary opacity-25" />
        
        <div className="row align-items-center">
          <div className="col-md-6">
            <p className="small text-white-50 mb-0">© {new Date().getFullYear()} CNI.CAM. Tous droits réservés.</p>
          </div>
          <div className="col-md-6 text-md-end">
            <ul className="list-inline mb-0 small">
              <li className="list-inline-item"><Link href="#" className="text-white-50 text-decoration-none">Confidentialité</Link></li>
              <li className="list-inline-item ms-3"><Link href="#" className="text-white-50 text-decoration-none">Mentions légales</Link></li>
            </ul>
          </div>
        </div>
      </div>
      
      <style jsx>{`
        .space-y-2 > li { margin-bottom: 0.75rem; }
        .hover-white:hover { color: white !important; }
      `}</style>
    </footer>
  );
}
