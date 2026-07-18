"use client";

import { useState } from "react";
import Link from "next/link";
import DashboardHeader from "@/components/DashboardHeader";

export default function TenantsPage() {
  const [tenants] = useState([
    {
      id: 1,
      name: "John Doe",
      phone: "(555) 123-4567",
      email: "john@example.com",
      property: "Sunset Apartments",
      unit: "101",
      leaseStart: "2024-01-15",
      leaseEnd: "2025-01-14",
      status: "active",
    },
    {
      id: 2,
      name: "Jane Smith",
      phone: "(555) 234-5678",
      email: "jane@example.com",
      property: "Downtown Complex",
      unit: "205",
      leaseStart: "2023-06-01",
      leaseEnd: "2025-05-31",
      status: "active",
    },
    {
      id: 3,
      name: "Mike Johnson",
      phone: "(555) 345-6789",
      email: "mike@example.com",
      property: "Park View Building",
      unit: "305",
      leaseStart: "2024-03-01",
      leaseEnd: "2025-02-28",
      status: "active",
    },
    {
      id: 4,
      name: "Sarah Williams",
      phone: "(555) 456-7890",
      email: "sarah@example.com",
      property: "Riverside Homes",
      unit: "102",
      leaseStart: "2023-12-01",
      leaseEnd: "2024-11-30",
      status: "expiring",
    },
    {
      id: 5,
      name: "Robert Brown",
      phone: "(555) 567-8901",
      email: "robert@example.com",
      property: "Central District",
      unit: "504",
      leaseStart: "2022-01-01",
      leaseEnd: "2024-12-31",
      status: "expiring",
    },
  ]);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "active":
        return "badge-success";
      case "expiring":
        return "badge-pending";
      case "inactive":
        return "badge-danger";
      default:
        return "badge-pending";
    }
  };

  const getStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
  };

  return (
    <div className="flex h-screen bg-background">
      {/* Sidebar */}
      <div className="w-64 bg-secondary text-white flex flex-col">
        <div className="p-6 border-b border-white/10">
          <h1 className="text-2xl font-bold">Smart Rent</h1>
          <p className="text-sm text-white/60 mt-1">Property Management</p>
        </div>

        <nav className="flex-1 overflow-y-auto p-4 space-y-2">
          <NavLink href="/" label="Overview" icon="📊" />
          <NavLink href="/properties" label="Properties" icon="🏠" />
          <NavLink href="/tenants" label="Tenants" icon="👥" active />
          <NavLink href="/payments" label="Payments" icon="💳" />
          <NavLink href="/settings" label="Settings" icon="⚙️" />
        </nav>

        <div className="p-4 border-t border-white/10">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-accent rounded-full flex items-center justify-center text-secondary font-bold">
              L
            </div>
            <div>
              <p className="text-sm font-semibold">Landlord</p>
              <p className="text-xs text-white/60">Account</p>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 overflow-auto">
        <DashboardHeader />

        <main className="p-8">
          <div className="mb-8">
            <div className="flex justify-between items-center mb-6">
              <div>
                <h2 className="text-3xl font-bold">Tenants</h2>
                <p className="text-foreground/60 mt-1">
                  Manage all your tenants and lease agreements
                </p>
              </div>
              <Link href="/tenants/new">
                <button className="btn-primary">Add New Tenant</button>
              </Link>
            </div>

            {/* Stats Overview */}
            <div className="grid grid-cols-4 gap-4 mb-8">
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">Total Tenants</p>
                <p className="text-3xl font-bold">{tenants.length}</p>
              </div>
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">Active</p>
                <p className="text-3xl font-bold text-green-600">
                  {tenants.filter((t) => t.status === "active").length}
                </p>
              </div>
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">
                  Expiring Soon
                </p>
                <p className="text-3xl font-bold text-yellow-600">
                  {tenants.filter((t) => t.status === "expiring").length}
                </p>
              </div>
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">
                  Lease Renewals
                </p>
                <p className="text-3xl font-bold text-primary">
                  {tenants.filter((t) => t.status === "expiring").length}
                </p>
              </div>
            </div>
          </div>

          {/* Tenants Table */}
          <div className="card overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-border">
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Name
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Property
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Unit
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Phone
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Lease End
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Status
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody>
                {tenants.map((tenant) => (
                  <tr
                    key={tenant.id}
                    className="border-b border-border hover:bg-background transition-colors"
                  >
                    <td className="py-4 px-6">
                      <div>
                        <p className="font-medium">{tenant.name}</p>
                        <p className="text-sm text-foreground/60">
                          {tenant.email}
                        </p>
                      </div>
                    </td>
                    <td className="py-4 px-6 text-sm">{tenant.property}</td>
                    <td className="py-4 px-6 font-semibold">{tenant.unit}</td>
                    <td className="py-4 px-6 text-sm">{tenant.phone}</td>
                    <td className="py-4 px-6 text-sm">
                      {new Date(tenant.leaseEnd).toLocaleDateString("en-US", {
                        year: "numeric",
                        month: "short",
                        day: "numeric",
                      })}
                    </td>
                    <td className="py-4 px-6">
                      <span className={`badge ${getStatusBadge(tenant.status)}`}>
                        {getStatusLabel(tenant.status)}
                      </span>
                    </td>
                    <td className="py-4 px-6">
                      <div className="flex gap-2">
                        <Link href={`/tenants/${tenant.id}`}>
                          <button className="text-xs text-primary hover:underline">
                            View
                          </button>
                        </Link>
                        <span className="text-foreground/40">•</span>
                        <button className="text-xs text-primary hover:underline">
                          Edit
                        </button>
                        <span className="text-foreground/40">•</span>
                        <button className="text-xs text-red-600 hover:underline">
                          Remove
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {tenants.length === 0 && (
            <div className="card text-center py-12">
              <p className="text-foreground/60 mb-4">
                No tenants yet. Add your first tenant to get started.
              </p>
              <Link href="/tenants/new">
                <button className="btn-primary">Add Tenant</button>
              </Link>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}

function NavLink({
  href,
  label,
  icon,
  active = false,
}: {
  href: string;
  label: string;
  icon: string;
  active?: boolean;
}) {
  return (
    <Link href={href}>
      <button
        className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all ${
          active
            ? "bg-white/10 border-l-4 border-accent text-accent"
            : "hover:bg-white/5"
        }`}
      >
        <span className="text-xl">{icon}</span>
        <span className="font-medium">{label}</span>
      </button>
    </Link>
  );
}
