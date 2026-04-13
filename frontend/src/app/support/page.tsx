"use client";

import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Mail, Phone, MapPin, Send } from "lucide-react";

export default function SupportPage() {
  return (
    <main className="min-vh-100 bg-light">
      <Navbar />
      
      <div className="container py-5 mt-5">
        <div className="text-center mb-5">
          <h1 className="fw-bold">Centre d'Assistance</h1>
          <p className="text-muted">Comment pouvons-nous vous aider aujourd'hui ?</p>
        </div>

        <div className="row g-4 justify-content-center">
          <div className="col-lg-4">
            <div className="card border-0 shadow-sm rounded-4 p-4 h-100">
              <h5 className="fw-bold mb-4">Contactez-nous</h5>
              <div className="vstack gap-4">
                <div className="d-flex align-items-center gap-3">
                  <div className="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                    <Mail size={20} />
                  </div>
                  <div>
                    <p className="small text-muted mb-0">Email</p>
                    <p className="fw-bold mb-0">support@cni.cam</p>
                  </div>
                </div>
                <div className="d-flex align-items-center gap-3">
                  <div className="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                    <Phone size={20} />
                  </div>
                  <div>
                    <p className="small text-muted mb-0">Téléphone</p>
                    <p className="fw-bold mb-0">+237 2XX XX XX XX</p>
                  </div>
                </div>
                <div className="d-flex align-items-center gap-3">
                  <div className="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                    <MapPin size={20} />
                  </div>
                  <div>
                    <p className="small text-muted mb-0">Adresse</p>
                    <p className="fw-bold mb-0">Yaoundé, Cameroun</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="col-lg-6">
            <div className="card border-0 shadow-sm rounded-4 p-4">
              <h5 className="fw-bold mb-4">Envoyez un message</h5>
              <form>
                <div className="row g-3">
                  <div className="col-md-6">
                    <label className="form-label small fw-bold">Nom complet</label>
                    <input type="text" className="form-control bg-light border-0" placeholder="Votre nom" />
                  </div>
                  <div className="col-md-6">
                    <label className="form-label small fw-bold">Email</label>
                    <input type="email" className="form-control bg-light border-0" placeholder="votre@email.com" />
                  </div>
                  <div className="col-12">
                    <label className="form-label small fw-bold">Sujet</label>
                    <input type="text" className="form-control bg-light border-0" placeholder="Sujet de votre message" />
                  </div>
                  <div className="col-12">
                    <label className="form-label small fw-bold">Message</label>
                    <textarea className="form-control bg-light border-0" rows={4} placeholder="Comment pouvons-nous vous aider ?"></textarea>
                  </div>
                  <div className="col-12 text-end">
                    <button type="button" className="btn btn-primary rounded-pill px-5 shadow-sm">
                      <Send size={18} className="me-2" /> Envoyer
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <Footer />
    </main>
  );
}
