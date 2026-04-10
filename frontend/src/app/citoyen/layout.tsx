import Sidebar from "@/components/dashboard/Sidebar";
import TopBar from "@/components/dashboard/TopBar";

export default function CitizenLayout({
  children,
}: {
  children: React.ReactNode;
}) {
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
