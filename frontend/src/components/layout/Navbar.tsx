"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";

export default function Navbar() {
  const [isScrolled, setIsScrolled] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  return (
    <nav className={`navbar navbar-expand-lg fixed-top transition-all ${isScrolled ? "bg-white shadow-sm py-2" : "bg-transparent py-3"}`}>
      <div className="container">
        <Link href="/" className="navbar-brand d-flex align-items-center">
            <span className={`fw-bold fs-4 ${isScrolled ? "text-primary" : "text-white"}`}>CNI<span className="text-warning">.CAM</span></span>
        </Link>
        
        <button className="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
          <span className="navbar-toggler-icon"></span>
        </button>

        <div className="collapse navbar-collapse" id="nav">
          <ul className="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
            <li className="nav-item">
              <Link href="/" className={`nav-link px-3 ${isScrolled ? "text-dark" : "text-white opacity-90"}`}>Accueil</Link>
            </li>
            <li className="nav-item">
              <Link href="#services" className={`nav-link px-3 ${isScrolled ? "text-dark" : "text-white opacity-90"}`}>Services</Link>
            </li>
            <li className="nav-item">
              <Link href="#faq" className={`nav-link px-3 ${isScrolled ? "text-dark" : "text-white opacity-90"}`}>FAQ</Link>
            </li>
            <li className="nav-item ms-lg-3">
              <Link href="/login" className={`btn ${isScrolled ? "btn-primary shadow-sm" : "btn-outline-light"} rounded-pill px-4`}>
                Connexion
              </Link>
            </li>
          </ul>
        </div>
      </div>
      
      <style jsx>{`
        .transition-all {
          transition: all 0.3s ease;
        }
      `}</style>
    </nav>
  );
}
