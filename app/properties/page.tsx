"use client";

import { useState } from "react";
import Link from "next/link";
import DashboardHeader from "@/components/DashboardHeader";

export default function PropertiesPage() {
  const [properties] = useState([
    {
      id: 1,
      name: "Sunset Apartments",
      address: "123 Main Street, Downtown",
      city: "New York",
      type: "apartment",
      units: 8,
      occupied: 6,
      rentCollected: 3000,
    },
    {
      id: 2,
      name: "Park View Building",
      address: "456 Oak Avenue, Midtown",
      city: "New York",
      type: "apartment",
      units: 12,
      occupied: 10,
      rentCollected: 4500,
    },
    {
      id: 3,
      name: "Riverside Homes",
      address: "789 River Road, Suburbs",
      city: "New York",
      type: "house",
      units: 4,
      occupied: 3,
      rentCollected: 1500,
    },
    {
      id: 4,
      name: "Central District",
      address: "321 Center Plaza, Downtown",
      city: "New York",
      type: "commercial",
      units: 6,
      occupied: 5,
      rentCollected: 2000,
    },
  ]);

  const getPropertyTypeColor = (type: string) => {
    switch (type) {
      case "apartment":
        return "bg-blue-100 text-blue-800";
      case "house":
        return "bg-green-100 text-green-800";
      case "commercial":
        return "bg-purple-100 text-purple-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const getOccupancyPercentage = (occupied: number, units: number) => {
    return Math.round((occupied / units) * 100);
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
          <NavLink href="/properties" label="Properties" icon="🏠" active />
          <NavLink href="/tenants" label="Tenants" icon="👥" />
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
                <h2 className="text-3xl font-bold">Properties</h2>
                <p className="text-foreground/60 mt-1">
                  Manage all your rental properties
                </p>
              </div>
              <Link href="/properties/new">
                <button className="btn-primary">Add New Property</button>
              </Link>
            </div>

            {/* Stats Overview */}
            <div className="grid grid-cols-4 gap-4 mb-8">
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">
                  Total Properties
                </p>
                <p className="text-3xl font-bold">{properties.length}</p>
              </div>
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">Total Units</p>
                <p className="text-3xl font-bold">
                  {properties.reduce((sum, p) => sum + p.units, 0)}
                </p>
              </div>
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">
                  Occupied Units
                </p>
                <p className="text-3xl font-bold text-green-600">
                  {properties.reduce((sum, p) => sum + p.occupied, 0)}
                </p>
              </div>
              <div className="card">
                <p className="text-sm text-foreground/60 mb-2">
                  Monthly Revenue
                </p>
                <p className="text-3xl font-bold text-primary">
                  ${properties
                    .reduce((sum, p) => sum + p.rentCollected, 0)
                    .toLocaleString()}
                </p>
              </div>
            </div>
          </div>

          {/* Properties Grid */}
          <div className="grid gap-6">
            {properties.map((property) => (
              <div key={property.id} className="card hover:shadow-lg transition-shadow">
                <div className="flex justify-between items-start mb-4">
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-2">
                      <h3 className="text-xl font-bold">{property.name}</h3>
                      <span
                        className={`text-xs font-semibold px-3 py-1 rounded-full ${getPropertyTypeColor(
                          property.type
                        )}`}
                      >
                        {property.type.charAt(0).toUpperCase() +
                          property.type.slice(1)}
                      </span>
                    </div>
                    <p className="text-foreground/60 text-sm mb-1">
                      {property.address}
                    </p>
                    <p className="text-foreground/60 text-sm">{property.city}</p>
                  </div>
                  <div className="flex gap-2">
                    <Link href={`/properties/${property.id}/edit`}>
                      <button className="btn-secondary px-3 py-2 text-sm">
                        Edit
                      </button>
                    </Link>
                    <button className="px-3 py-2 text-sm bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors">
                      Delete
                    </button>
                  </div>
                </div>

                <div className="grid grid-cols-4 gap-4">
                  <div className="bg-background p-3 rounded-lg">
                    <p className="text-xs text-foreground/60 mb-1">Units</p>
                    <p className="text-2xl font-bold">{property.units}</p>
                  </div>
                  <div className="bg-background p-3 rounded-lg">
                    <p className="text-xs text-foreground/60 mb-1">Occupied</p>
                    <p className="text-2xl font-bold text-green-600">
                      {property.occupied}
                    </p>
                  </div>
                  <div className="bg-background p-3 rounded-lg">
                    <p className="text-xs text-foreground/60 mb-1">
                      Occupancy
                    </p>
                    <p className="text-2xl font-bold text-primary">
                      {getOccupancyPercentage(property.occupied, property.units)}%
                    </p>
                  </div>
                  <div className="bg-background p-3 rounded-lg">
                    <p className="text-xs text-foreground/60 mb-1">Revenue</p>
                    <p className="text-2xl font-bold">
                      ${property.rentCollected.toLocaleString()}
                    </p>
                  </div>
                </div>

                <div className="mt-4 pt-4 border-t border-border flex gap-2">
                  <Link href={`/properties/${property.id}`}>
                    <button className="text-sm text-primary hover:underline">
                      View Details
                    </button>
                  </Link>
                  <span className="text-foreground/40">•</span>
                  <button className="text-sm text-primary hover:underline">
                    Manage Units
                  </button>
                  <span className="text-foreground/40">•</span>
                  <button className="text-sm text-primary hover:underline">
                    View Tenants
                  </button>
                </div>
              </div>
            ))}
          </div>

          {properties.length === 0 && (
            <div className="card text-center py-12">
              <p className="text-foreground/60 mb-4">
                No properties yet. Get started by adding your first property.
              </p>
              <Link href="/properties/new">
                <button className="btn-primary">Add Property</button>
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
