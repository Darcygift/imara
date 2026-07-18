"use client";

import { useState } from "react";
import Link from "next/link";
import DashboardHeader from "@/components/DashboardHeader";
import StatsCard from "@/components/StatsCard";
import PaymentChart from "@/components/PaymentChart";
import RecentPayments from "@/components/RecentPayments";

export default function Dashboard() {
  const [activeNav, setActiveNav] = useState("overview");

  const stats = {
    totalProperties: 12,
    totalTenants: 34,
    occupiedUnits: 28,
    pendingPayments: 5,
    totalRentCollected: 85000,
    outstandingAmount: 12500,
  };

  return (
    <div className="flex h-screen bg-background">
      {/* Sidebar */}
      <div className="w-64 bg-secondary text-white flex flex-col">
        <div className="p-6 border-b border-white/10">
          <h1 className="text-2xl font-bold">Smart Rent</h1>
          <p className="text-sm text-white/60 mt-1">Property Management</p>
        </div>

        <nav className="flex-1 overflow-y-auto p-4">
          <NavItem
            icon="📊"
            label="Overview"
            active={activeNav === "overview"}
            onClick={() => setActiveNav("overview")}
          />
          <NavItem
            icon="🏠"
            label="Properties"
            active={activeNav === "properties"}
            onClick={() => setActiveNav("properties")}
          />
          <NavItem
            icon="👥"
            label="Tenants"
            active={activeNav === "tenants"}
            onClick={() => setActiveNav("tenants")}
          />
          <NavItem
            icon="💳"
            label="Payments"
            active={activeNav === "payments"}
            onClick={() => setActiveNav("payments")}
          />
          <NavItem
            icon="⚙️"
            label="Settings"
            active={activeNav === "settings"}
            onClick={() => setActiveNav("settings")}
          />
        </nav>

        <div className="p-4 border-t border-white/10">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-accent rounded-full flex items-center justify-center text-secondary font-bold">
              L
            </div>
            <div>
              <p className="text-sm font-semibold">Landlord Account</p>
              <p className="text-xs text-white/60">Profile</p>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 overflow-auto">
        <DashboardHeader />

        <main className="p-8">
          {activeNav === "overview" && (
            <div className="space-y-8">
              {/* Page Title */}
              <div>
                <h2 className="text-3xl font-bold">Dashboard Overview</h2>
                <p className="text-foreground/60 mt-2">
                  Welcome back! Here&apos;s your property management summary.
                </p>
              </div>

              {/* Stats Grid */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <StatsCard
                  label="Total Properties"
                  value={stats.totalProperties}
                  icon="🏢"
                  bgColor="bg-accent/20"
                  textColor="text-accent"
                />
                <StatsCard
                  label="Total Tenants"
                  value={stats.totalTenants}
                  icon="👥"
                  bgColor="bg-primary/20"
                  textColor="text-primary"
                />
                <StatsCard
                  label="Occupied Units"
                  value={`${stats.occupiedUnits}/28`}
                  icon="📍"
                  bgColor="bg-green-500/20"
                  textColor="text-green-600"
                />
              </div>

              {/* Financial Overview */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 card">
                  <div className="flex justify-between items-center mb-6">
                    <h3 className="text-xl font-bold">
                      Payment Collection Trend
                    </h3>
                    <button className="text-foreground/60 hover:text-foreground">
                      ⚙️
                    </button>
                  </div>
                  <PaymentChart />
                </div>

                <div className="space-y-4">
                  <div className="card bg-accent/10 border-accent">
                    <p className="text-sm text-foreground/60 mb-2">
                      Rent Collected (Month)
                    </p>
                    <p className="text-3xl font-bold text-accent">
                      ${stats.totalRentCollected.toLocaleString()}
                    </p>
                    <p className="text-xs text-foreground/60 mt-2">
                      This month&apos;s collection
                    </p>
                  </div>

                  <div className="card bg-red-500/10 border-red-500/20">
                    <p className="text-sm text-foreground/60 mb-2">
                      Outstanding Amount
                    </p>
                    <p className="text-3xl font-bold text-red-600">
                      ${stats.outstandingAmount.toLocaleString()}
                    </p>
                    <p className="text-xs text-foreground/60 mt-2">
                      Pending payments
                    </p>
                  </div>
                </div>
              </div>

              {/* Recent Payments */}
              <RecentPayments />

              {/* Quick Actions */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link href="/properties/new">
                  <button className="w-full btn-primary">
                    Add New Property
                  </button>
                </Link>
                <Link href="/tenants/new">
                  <button className="w-full btn-secondary">Add Tenant</button>
                </Link>
              </div>
            </div>
          )}

          {activeNav === "properties" && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <div>
                  <h2 className="text-3xl font-bold">Properties</h2>
                  <p className="text-foreground/60 mt-1">
                    Manage all your properties
                  </p>
                </div>
                <Link href="/properties/new">
                  <button className="btn-primary">Add Property</button>
                </Link>
              </div>
              <div className="card">
                <p className="text-foreground/60">
                  Properties list will be displayed here with full management
                  capabilities.
                </p>
              </div>
            </div>
          )}

          {activeNav === "tenants" && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <div>
                  <h2 className="text-3xl font-bold">Tenants</h2>
                  <p className="text-foreground/60 mt-1">
                    Manage all your tenants
                  </p>
                </div>
                <Link href="/tenants/new">
                  <button className="btn-primary">Add Tenant</button>
                </Link>
              </div>
              <div className="card">
                <p className="text-foreground/60">
                  Tenants list will be displayed here with full management
                  capabilities.
                </p>
              </div>
            </div>
          )}

          {activeNav === "payments" && (
            <div className="space-y-6">
              <div>
                <h2 className="text-3xl font-bold">Payments</h2>
                <p className="text-foreground/60 mt-1">
                  Track all payment transactions
                </p>
              </div>
              <div className="card">
                <p className="text-foreground/60">
                  Payment transactions will be displayed here with detailed
                  history.
                </p>
              </div>
            </div>
          )}

          {activeNav === "settings" && (
            <div className="space-y-6">
              <div>
                <h2 className="text-3xl font-bold">Settings</h2>
                <p className="text-foreground/60 mt-1">
                  Manage your account and preferences
                </p>
              </div>
              <div className="card">
                <p className="text-foreground/60">
                  Settings will be displayed here.
                </p>
              </div>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}

function NavItem({
  icon,
  label,
  active,
  onClick,
}: {
  icon: string;
  label: string;
  active: boolean;
  onClick: () => void;
}) {
  return (
    <button
      onClick={onClick}
      className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all mb-2 ${
        active ? "bg-white/10 border-l-4 border-accent" : "hover:bg-white/5"
      }`}
    >
      <span className="text-xl">{icon}</span>
      <span className={`font-medium ${active ? "text-accent" : ""}`}>
        {label}
      </span>
    </button>
  );
}
