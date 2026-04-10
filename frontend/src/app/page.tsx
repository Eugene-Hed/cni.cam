import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import Hero from "@/components/home/Hero";
import Stats from "@/components/home/Stats";
import Services from "@/components/home/Services";
import Process from "@/components/home/Process";
import Chatbot from "@/components/home/Chatbot";

export default function Home() {
  return (
    <main className="min-vh-100 flex flex-col">
      <Navbar />
      <Hero />
      <Stats />
      <Services />
      <Process />
      <Chatbot />
      <Footer />
    </main>
  );
}
