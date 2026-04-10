"use client";

import { motion } from "framer-motion";

const stats = [
  { label: "Citoyens inscrits", value: "500K+", color: "primary", delay: 0.1 },
  { label: "Taux de satisfaction", value: "98%", color: "success", delay: 0.2 },
  { label: "Service disponible", value: "24/7", color: "info", delay: 0.3 },
  { label: "Temps de traitement", value: "-70%", color: "warning", delay: 0.4 },
];

export default function Stats() {
  return (
    <section className="py-5 bg-white">
      <div className="container">
        <div className="row g-4 text-center">
          {stats.map((stat, i) => (
            <div key={i} className="col-md-3 col-6">
              <motion.div 
                className="p-4 rounded-4 bg-light"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: stat.delay }}
              >
                <div className={`display-5 fw-bold text-${stat.color} mb-2`}>{stat.value}</div>
                <p className="mb-0 text-muted">{stat.label}</p>
              </motion.div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
