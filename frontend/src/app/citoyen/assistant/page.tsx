"use client";

import { useState, useRef, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Send, Bot, User, Sparkles, MessageCircle, Info } from "lucide-react";
import apiClient from "@/lib/api-client";

interface Message {
  text: string;
  sender: "user" | "assistant";
  time: string;
}

export default function CitizenAssistantPage() {
  const [messages, setMessages] = useState<Message[]>([
    { text: "Bonjour ! Je suis votre assistant IA dédié. Je peux vous aider à suivre vos dossiers ou répondre à vos questions sur les procédures.", sender: "assistant", time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }
  ]);
  const [input, setInput] = useState("");
  const [isTyping, setIsTyping] = useState(false);
  const chatEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    chatEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  useEffect(scrollToBottom, [messages, isTyping]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!input.trim()) return;

    const userMsg: Message = { text: input, sender: "user", time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) };
    setMessages(prev => [...prev, userMsg]);
    setInput("");
    setIsTyping(true);

    try {
      const resp = await apiClient.post("/chatbot", { message: input });
      const assistantMsg: Message = { 
        text: resp.data.response, 
        sender: "assistant", 
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) 
      };
      setMessages(prev => [...prev, assistantMsg]);
    } catch (err) {
      setMessages(prev => [...prev, { text: "Désolé, j'ai rencontré une erreur technique. Veuillez réessayer.", sender: "assistant", time: "Maintenant" }]);
    } finally {
      setIsTyping(false);
    }
  };

  const quickActions = [
    "Où en est ma demande ?",
    "Quels documents pour la CNI ?",
    "Comment payer les frais ?",
    "Délai de traitement"
  ];

  return (
    <div className="h-100 d-flex flex-column max-w-5xl mx-auto">
      <div className="mb-4">
        <h2 className="fw-bold d-flex align-items-center gap-2">
            <Sparkles className="text-primary" /> Assistant Intelligent
        </h2>
        <p className="text-muted">Posez vos questions sur vos dossiers en cours ou sur les procédures administratives.</p>
      </div>

      <div className="row g-4 flex-grow-1 overflow-hidden min-h-0">
        <div className="col-lg-8 d-flex flex-column h-100">
            <div className="card border-0 shadow-sm rounded-4 flex-grow-1 overflow-hidden d-flex flex-column">
                <div className="card-header bg-white border-bottom py-3 px-4">
                    <div className="d-flex align-items-center gap-3">
                        <div className="bg-primary text-white rounded-circle p-2">
                            <Bot size={20} />
                        </div>
                        <div>
                            <h6 className="fw-bold mb-0">IA CNI.CAM</h6>
                            <small className="text-success d-flex align-items-center gap-1">
                                <span className="bg-success rounded-circle" style={{ width: 8, height: 8 }}></span> En ligne
                            </small>
                        </div>
                    </div>
                </div>

                <div className="card-body p-4 overflow-auto bg-light" style={{ flex: 1 }}>
                    <AnimatePresence initial={false}>
                        {messages.map((m, i) => (
                            <motion.div 
                                key={i} 
                                className={`d-flex mb-4 ${m.sender === 'user' ? 'justify-content-end' : 'justify-content-start'}`}
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                            >
                                <div className={`d-flex gap-2 max-w-85 ${m.sender === 'user' ? 'flex-row-reverse' : ''}`}>
                                    <div className={`rounded-circle p-2 flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm`}
                                        style={{ width: 32, height: 32, backgroundColor: m.sender === 'user' ? '#1774df' : 'white' }}>
                                        {m.sender === 'user' ? <User size={16} className="text-white" /> : <Bot size={16} className="text-primary" />}
                                    </div>
                                    <div>
                                        <div className={`p-3 rounded-4 shadow-sm ${m.sender === 'user' ? 'bg-primary text-white rounded-tr-0' : 'bg-white text-dark rounded-tl-0'}`}>
                                            <p className="mb-0 small">{m.text}</p>
                                        </div>
                                        <small className={`text-muted mt-1 d-block ${m.sender === 'user' ? 'text-end' : ''}`} style={{ fontSize: '0.7rem' }}>
                                            {m.time}
                                        </small>
                                    </div>
                                </div>
                            </motion.div>
                        ))}
                    </AnimatePresence>
                    {isTyping && (
                        <div className="d-flex justify-content-start mb-4">
                            <div className="bg-white p-3 rounded-4 shadow-sm">
                                <span className="spinner-grow spinner-grow-sm text-primary"></span>
                                <span className="spinner-grow spinner-grow-sm text-primary mx-1"></span>
                                <span className="spinner-grow spinner-grow-sm text-primary"></span>
                            </div>
                        </div>
                    )}
                    <div ref={chatEndRef} />
                </div>

                <div className="card-footer bg-white border-top p-3">
                    <form onSubmit={handleSubmit} className="d-flex gap-2">
                        <input 
                            type="text" 
                            className="form-control border-0 bg-light fs-6 py-2 px-3 focus-none" 
                            placeholder="Votre message ici..." 
                            value={input}
                            onChange={(e) => setInput(e.target.value)}
                        />
                        <button type="submit" className="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style={{ width: 42, height: 42 }} disabled={isTyping}>
                            <Send size={20} />
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div className="col-lg-4 d-none d-lg-block">
            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 className="fw-bold mb-3 d-flex align-items-center gap-2">
                    <MessageCircle size={18} className="text-primary" /> Questions Rapides
                </h6>
                <div className="vstack gap-2">
                    {quickActions.map((q, i) => (
                        <button 
                            key={i} 
                            className="btn btn-outline-light text-dark text-start border-light-subtle rounded-3 small py-2 px-3 hover-bg-primary transition-all"
                            onClick={() => setInput(q)}
                        >
                            {q}
                        </button>
                    ))}
                </div>
            </div>

            <div className="alert alert-warning border-0 rounded-4 p-4 small">
                <div className="d-flex gap-3">
                    <Info size={24} className="flex-shrink-0" />
                    <div>
                        <h6 className="fw-bold mb-1">Information</h6>
                        L'assistant IA peut accéder au statut de vos dossiers actuels pour vous répondre plus précisément.
                    </div>
                </div>
            </div>
        </div>
      </div>

      <style jsx>{`
        .max-w-85 { max-width: 85%; }
        .focus-none:focus { box-shadow: none; background-color: #f1f3f5; }
        .rounded-tr-0 { border-top-right-radius: 4px !important; }
        .rounded-tl-0 { border-top-left-radius: 4px !important; }
        .hover-bg-primary:hover { background-color: var(--bs-primary); color: white !important; border-color: var(--bs-primary); }
        .transition-all { transition: all 0.2s ease; }
      `}</style>
    </div>
  );
}
