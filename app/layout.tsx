import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Smart Rent - Property Management System",
  description:
    "Professional property management system for landlords. Manage properties, tenants, payments, and automate rent collection.",
  keywords:
    "property management, rental management, landlord, tenant management, payment tracking",
  viewport: {
    width: "device-width",
    initialScale: 1,
    maximumScale: 1,
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="bg-background">
      <body className="bg-background text-foreground">{children}</body>
    </html>
  );
}
