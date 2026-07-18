"use client";

import { useState } from "react";
import Link from "next/link";
import DashboardHeader from "@/components/DashboardHeader";

export default function PaymentsPage() {
  const [payments] = useState([
    {
      id: 1,
      tenant: "John Doe",
      property: "Sunset Apartments",
      unit: "101",
      amount: 1200,
      dueDate: "2024-08-01",
      paidDate: "2024-07-28",
      status: "completed",
    },
    {
      id: 2,
      tenant: "Jane Smith",
      property: "Downtown Complex",
      unit: "205",
      amount: 1500,
      dueDate: "2024-08-01",
      paidDate: null,
      status: "pending",
    },
    {
      id: 3,
      tenant: "Mike Johnson",
      property: "Park View Building",
      unit: "305",
      amount: 1100,
      dueDate: "2024-08-01",
      paidDate: "2024-07-29",
      status: "completed",
    },
    {
      id: 4,
      tenant: "Sarah Williams",
      property: "Riverside Homes",
      unit: "102",
      amount: 900,
      dueDate: "2024-07-01",
      paidDate: null,
      status: "overdue",
    },
    {
      id: 5,
      tenant: "Robert Brown",
      property: "Central District",
      unit: "504",
      amount: 1350,
      dueDate: "2024-07-15",
      paidDate: null,
      status: "overdue",
    },
  ]);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "completed":
        return "badge-success";
      case "pending":
        return "badge-pending";
      case "overdue":
        return "badge-danger";
      default:
        return "badge-pending";
    }
  };

  const getStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
  };

  const totalAmount = payments.reduce((sum, p) => sum + p.amount, 0);
  const totalCollected = payments
    .filter((p) => p.status === "completed")
    .reduce((sum, p) => sum + p.amount, 0);
  const totalPending = payments
    .filter((p) => p.status === "pending")
    .reduce((sum, p) => sum + p.amount, 0);
  const totalOverdue = payments
    .filter((p) => p.status === "overdue")
    .reduce((sum, p) => sum + p.amount, 0);

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
          <NavLink href="/tenants" label="Tenants" icon="👥" />
          <NavLink href="/payments" label="Payments" icon="💳" active />
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
                <h2 className="text-3xl font-bold">Payment Tracking</h2>
                <p className="text-foreground/60 mt-1">
                  Monitor all rent payments and collection
                </p>
              </div>
              <button className="btn-primary">Record Payment</button>
            </div>

            {/* Financial Overview */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">Total Amount</p>
                <p className="text-2xl font-bold">
                  ${totalAmount.toLocaleString()}
                </p>
              </div>
              <div className="card bg-green-500/10 border-green-500/20">
                <p className="text-sm text-foreground/60 mb-2">Collected</p>
                <p className="text-2xl font-bold text-green-600">
                  ${totalCollected.toLocaleString()}
                </p>
              </div>
              <div className="card bg-yellow-500/10 border-yellow-500/20">
                <p className="text-sm text-foreground/60 mb-2">Pending</p>
                <p className="text-2xl font-bold text-yellow-600">
                  ${totalPending.toLocaleString()}
                </p>
              </div>
              <div className="card bg-red-500/10 border-red-500/20">
                <p className="text-sm text-foreground/60 mb-2">Overdue</p>
                <p className="text-2xl font-bold text-red-600">
                  ${totalOverdue.toLocaleString()}
                </p>
              </div>
            </div>
          </div>

          {/* Collection Rate */}
          <div className="card mb-8">
            <h3 className="text-lg font-bold mb-4">Collection Status</h3>
            <div className="space-y-4">
              <div>
                <div className="flex justify-between mb-2">
                  <span className="text-sm font-medium">Collected</span>
                  <span className="text-sm font-medium">
                    {Math.round((totalCollected / totalAmount) * 100)}%
                  </span>
                </div>
                <div className="w-full bg-background rounded-full h-3 overflow-hidden">
                  <div
                    className="bg-green-600 h-full transition-all"
                    style={{
                      width: `${(totalCollected / totalAmount) * 100}%`,
                    }}
                  />
                </div>
              </div>
              <div>
                <div className="flex justify-between mb-2">
                  <span className="text-sm font-medium">Pending</span>
                  <span className="text-sm font-medium">
                    {Math.round((totalPending / totalAmount) * 100)}%
                  </span>
                </div>
                <div className="w-full bg-background rounded-full h-3 overflow-hidden">
                  <div
                    className="bg-yellow-600 h-full transition-all"
                    style={{
                      width: `${(totalPending / totalAmount) * 100}%`,
                    }}
                  />
                </div>
              </div>
              <div>
                <div className="flex justify-between mb-2">
                  <span className="text-sm font-medium">Overdue</span>
                  <span className="text-sm font-medium">
                    {Math.round((totalOverdue / totalAmount) * 100)}%
                  </span>
                </div>
                <div className="w-full bg-background rounded-full h-3 overflow-hidden">
                  <div
                    className="bg-red-600 h-full transition-all"
                    style={{
                      width: `${(totalOverdue / totalAmount) * 100}%`,
                    }}
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Payments Table */}
          <div className="card overflow-x-auto">
            <h3 className="text-lg font-bold mb-4">Payment Records</h3>
            <table className="w-full">
              <thead>
                <tr className="border-b border-border">
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Tenant
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Property
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Amount
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Due Date
                  </th>
                  <th className="text-left py-4 px-6 font-semibold text-foreground/60">
                    Paid Date
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
                {payments.map((payment) => (
                  <tr
                    key={payment.id}
                    className="border-b border-border hover:bg-background transition-colors"
                  >
                    <td className="py-4 px-6">
                      <div>
                        <p className="font-medium">{payment.tenant}</p>
                        <p className="text-sm text-foreground/60">
                          Unit {payment.unit}
                        </p>
                      </div>
                    </td>
                    <td className="py-4 px-6 text-sm">{payment.property}</td>
                    <td className="py-4 px-6 font-semibold">
                      ${payment.amount.toLocaleString()}
                    </td>
                    <td className="py-4 px-6 text-sm">
                      {new Date(payment.dueDate).toLocaleDateString("en-US", {
                        year: "numeric",
                        month: "short",
                        day: "numeric",
                      })}
                    </td>
                    <td className="py-4 px-6 text-sm">
                      {payment.paidDate
                        ? new Date(payment.paidDate).toLocaleDateString(
                            "en-US",
                            {
                              year: "numeric",
                              month: "short",
                              day: "numeric",
                            }
                          )
                        : "—"}
                    </td>
                    <td className="py-4 px-6">
                      <span
                        className={`badge ${getStatusBadge(payment.status)}`}
                      >
                        {getStatusLabel(payment.status)}
                      </span>
                    </td>
                    <td className="py-4 px-6">
                      <div className="flex gap-2">
                        {payment.status !== "completed" && (
                          <>
                            <button className="text-xs text-primary hover:underline">
                              Record
                            </button>
                            <span className="text-foreground/40">•</span>
                          </>
                        )}
                        <button className="text-xs text-primary hover:underline">
                          View
                        </button>
                        <span className="text-foreground/40">•</span>
                        <button className="text-xs text-primary hover:underline">
                          SMS
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
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
