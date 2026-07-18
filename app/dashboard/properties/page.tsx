'use client'

import { useEffect, useState } from 'react'
import { DashboardLayout } from '@/components/DashboardLayout'
import { getProperties, Property } from '@/lib/supabase/services'
import Link from 'next/link'

export default function PropertiesPage() {
  const [properties, setProperties] = useState<Property[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchProperties = async () => {
      try {
        const data = await getProperties()
        setProperties(data)
      } catch (error) {
        console.error('[v0] Error fetching properties:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchProperties()
  }, [])

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex justify-between items-center">
          <div>
            <h1 className="heading-1">Properties</h1>
            <p className="text-muted mt-1">Manage all your rental properties</p>
          </div>
          <Link href="/dashboard/properties/new">
            <button className="btn-primary">Add Property</button>
          </Link>
        </div>

        {/* Properties Grid */}
        {loading ? (
          <div className="card">
            <p className="text-muted">Loading properties...</p>
          </div>
        ) : properties.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {properties.map((property) => (
              <Link key={property.id} href={`/dashboard/properties/${property.id}`}>
                <div className="card cursor-pointer hover:shadow-lg transition-shadow">
                  {property.image_url && (
                    <img
                      src={property.image_url}
                      alt={property.name}
                      className="w-full h-40 object-cover rounded-lg mb-4"
                    />
                  )}
                  <h3 className="font-semibold text-lg mb-2">{property.name}</h3>
                  <p className="text-sm text-muted mb-3">
                    {property.address}, {property.city}
                  </p>
                  <div className="grid grid-cols-2 gap-2 text-xs mb-4">
                    <div className="bg-foreground/5 p-2 rounded">
                      <span className="text-muted">Units</span>
                      <p className="font-semibold">{property.total_units}</p>
                    </div>
                    <div className="bg-foreground/5 p-2 rounded">
                      <span className="text-muted">Type</span>
                      <p className="font-semibold capitalize">{property.property_type}</p>
                    </div>
                  </div>
                  <button className="btn-outline w-full text-sm">View Details</button>
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <div className="card text-center py-12">
            <p className="text-muted mb-4">No properties yet</p>
            <Link href="/dashboard/properties/new">
              <button className="btn-primary">Create Your First Property</button>
            </Link>
          </div>
        )}
      </div>
    </DashboardLayout>
  )
}
