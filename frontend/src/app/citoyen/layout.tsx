"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Sidebar from "@/components/dashboard/Sidebar";
import TopBar from "@/components/dashboard/TopBar";

export default function CitizenLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const [authorized, setAuthorized] = useState(false);
  const router = useRouter();

  useEffect(() => {
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    // Role 2 = Citoyen
    if (user.role_id !== 2) {
      router.push("/login?error=unauthorized");
    } else {
      setAuthorized(true);
    }
  }, [router]);

  if (!authorized) return null;

  return (
    <div className="d-flex min-vh-100 bg-light">
      <div className="d-none d-lg-block">
        <Sidebar />
      </div>
      <div className="flex-grow-1 d-flex flex-column h-screen overflow-hidden">
        <TopBar />
        <main className="p-4 p-md-5 overflow-auto bg-light flex-grow-1">
          {children}
        </main>
      </div>
    </div>
  );
}
