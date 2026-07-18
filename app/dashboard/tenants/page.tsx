'use client'

import { useEffect, useState } from 'react'
import { DashboardLayout } from '@/components/DashboardLayout'
import { getTenants, Tenant } from '@/lib/supabase/services'
import Link from 'next/link'

export default function TenantsPage() {
  const [tenants, setTenants] = useState<Tenant[]>([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState<'all' | 'active' | 'inactive'>('all')

  useEffect(() => {
    const fetchTenants = async () => {
      try {
        const data = await getTenants()
        setTenants(data)
      } catch (error) {
        console.error('[v0] Error fetching tenants:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchTenants()
  }, [])

  const filteredTenants = tenants.filter((tenant) => {
    if (filter === 'all') return true
    return tenant.status === filter
  })

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
        return 'badge-success'
      case 'inactive':
        return 'badge-danger'
      default:
        return 'badge-info'
    }
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex justify-between items-center">
          <div>
            <h1 className="heading-1">Tenants</h1>
            <p className="text-muted mt-1">Manage all your tenants and leases</p>
          </div>
          <Link href="/dashboard/tenants/new">
            <button className="btn-primary">Add Tenant</button>
          </Link>
        </div>

        {/* Filters */}
        <div className="flex gap-2">
          {(['all', 'active', 'inactive'] as const).map((status) => (
            <button
              key={status}
              onClick={() => setFilter(status)}
              className={`px-4 py-2 rounded-lg font-medium transition-all ${
                filter === status
                  ? 'bg-primary text-white'
                  : 'bg-foreground/5 text-foreground hover:bg-foreground/10'
              }`}
            >
              {status.charAt(0).toUpperCase() + status.slice(1)}
            </button>
          ))}
        </div>

        {/* Tenants List */}
        {loading ? (
          <div className="card">
            <p className="text-muted">Loading tenants...</p>
          </div>
        ) : filteredTenants.length > 0 ? (
          <div className="card overflow-x-auto">
            <table className="w-full">
              <thead className="border-b border-border bg-foreground/5">
                <tr>
                  <th className="text-left py-3 px-4 font-semibold">Name</th>
                  <th className="text-left py-3 px-4 font-semibold">Email</th>
                  <th className="text-left py-3 px-4 font-semibold">Phone</th>
                  <th className="text-left py-3 px-4 font-semibold">Lease Amount</th>
                  <th className="text-left py-3 px-4 font-semibold">Status</th>
                  <th className="text-left py-3 px-4 font-semibold">Action</th>
                </tr>
              </thead>
              <tbody>
                {filteredTenants.map((tenant) => (
                  <tr key={tenant.id} className="border-b border-border/50 hover:bg-foreground/2">
                    <td className="py-3 px-4">
                      <div>
                        <p className="font-medium">
                          {tenant.first_name} {tenant.last_name}
                        </p>
                      </div>
                    </td>
                    <td className="py-3 px-4">{tenant.email}</td>
                    <td className="py-3 px-4">{tenant.phone || 'N/A'}</td>
                    <td className="py-3 px-4 font-medium">${tenant.lease_amount}</td>
                    <td className="py-3 px-4">
                      <span className={`badge ${getStatusColor(tenant.status)}`}>{tenant.status}</span>
                    </td>
                    <td className="py-3 px-4">
                      <Link href={`/dashboard/tenants/${tenant.id}`}>
                        <button className="btn-ghost text-sm px-3">View</button>
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="card text-center py-12">
            <p className="text-muted mb-4">No tenants found</p>
            <Link href="/dashboard/tenants/new">
              <button className="btn-primary">Add Your First Tenant</button>
            </Link>
          </div>
        )}
      </div>
    </DashboardLayout>
  )
}
