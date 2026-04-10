"use client";

import { useState, useRef, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import axios from "axios";

interface Message {
  text: string;
  sender: "user" | "assistant";
  time: string;
}

export default function Chatbot() {
  const [messages, setMessages] = useState<Message[]>([
    { text: "👋 Bonjour ! Je suis votre assistant virtuel CNI.CAM.", sender: "assistant", time: "À l'instant" },
    { text: "Comment puis-je vous aider aujourd'hui ?", sender: "assistant", time: "À l'instant" }
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
      const resp = await axios.post(`${process.env.NEXT_PUBLIC_API_URL}/chatbot`, { message: input });
      const assistantMsg: Message = { 
        text: resp.data.response, 
        sender: "assistant", 
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) 
      };
      setMessages(prev => [...prev, assistantMsg]);
    } catch (err) {
      setMessages(prev => [...prev, { text: "Désolé, j'ai rencontré une erreur.", sender: "assistant", time: "Maintenant" }]);
    } finally {
      setIsTyping(false);
    }
  };

  return (
    <section className="py-5 bg-light">
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-lg-8 col-md-10">
            <motion.div 
              className="card border-0 shadow-sm rounded-4 overflow-hidden"
              initial={{ opacity: 0, scale: 0.95 }}
              whileInView={{ opacity: 1, scale: 1 }}
              viewport={{ once: true }}
            >
              <div className="card-header bg-white border-0 py-3">
                <div className="d-flex align-items-center">
                  <div className="bg-primary bg-opacity-10 p-3 rounded-circle me-3 d-flex align-items-center justify-content-center" style={{ width: 50, height: 50 }}>
                    <i className="bi bi-robot fs-4 text-primary"></i>
                  </div>
                  <div>
                    <h4 className="mb-0 fw-bold">Assistant CNI.CAM</h4>
                    <p className="text-muted small mb-0">Je réponds à vos questions 24h/24</p>
                  </div>
                  <div className="ms-auto">
                    <span className="badge bg-success px-3 py-2 rounded-pill">En ligne</span>
                  </div>
                </div>
              </div>
              
              <div className="card-body p-4">
                <div className="chat-container mb-3 p-3 bg-light rounded-3" style={{ height: 350, overflowY: "auto" }}>
                  <AnimatePresence initial={false}>
                    {messages.map((m, i) => (
                      <motion.div 
                        key={i} 
                        className={`chat-message ${m.sender}`}
                        initial={{ opacity: 0, x: m.sender === "user" ? 20 : -20 }}
                        animate={{ opacity: 1, x: 0 }}
                      >
                        <div className="message-content">
                          <p className="mb-0">{m.text}</p>
                        </div>
                        <small className="text-muted d-block mt-1">{m.time}</small>
                      </motion.div>
                    ))}
                    {isTyping && (
                      <motion.div className="chat-message assistant" initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
                        <div className="message-content">
                          <div className="typing-dots">
                            <span></span><span></span><span></span>
                          </div>
                        </div>
                      </motion.div>
                    )}
                  </AnimatePresence>
                  <div ref={chatEndRef} />
                </div>

                <form onSubmit={handleSubmit} className="d-flex gap-2">
                  <input 
                    type="text" 
                    className="form-control form-control-lg fs-6" 
                    placeholder="Posez votre question..." 
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                  />
                  <button type="submit" className="btn btn-primary px-4 shadow-sm" disabled={isTyping}>
                    <i className="bi bi-send"></i>
                  </button>
                </form>
              </div>
            </motion.div>
          </div>
        </div>
      </div>

      <style jsx>{`
        .chat-container {
          background-color: #f8f9fa !important;
          display: flex;
          flex-direction: column;
        }
        .chat-message {
          margin-bottom: 15px;
          max-width: 85%;
        }
        .chat-message.user {
          align-self: flex-end;
          text-align: right;
        }
        .chat-message.assistant {
          align-self: flex-start;
        }
        .message-content {
          padding: 10px 15px;
          border-radius: 15px;
          display: inline-block;
          font-size: 0.95rem;
        }
        .chat-message.user .message-content {
          background: var(--primary-color);
          color: white;
          border-bottom-right-radius: 2px;
        }
        .chat-message.assistant .message-content {
          background: white;
          border-bottom-left-radius: 2px;
          box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .typing-dots {
          display: flex;
          gap: 4px;
        }
        .typing-dots span {
          width: 6px;
          height: 6px;
          background: #adb5bd;
          border-radius: 50%;
          animation: typing 1.4s infinite both;
        }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
      `}</style>
    </section>
  );
}
