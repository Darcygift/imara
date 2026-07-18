'use client'

import { useEffect, useState } from 'react'
import { DashboardLayout } from '@/components/DashboardLayout'
import { getDashboardStats, getPayments } from '@/lib/supabase/services'
import Link from 'next/link'

export default function DashboardOverview() {
  const [stats, setStats] = useState({
    totalProperties: 0,
    activeTenants: 0,
    totalRentCollected: 0,
    pendingPayments: 0,
  })
  const [recentPayments, setRecentPayments] = useState<any[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [statsData, paymentsData] = await Promise.all([getDashboardStats(), getPayments()])
        setStats(statsData)
        setRecentPayments(paymentsData.slice(0, 5))
      } catch (error) {
        console.error('[v0] Error fetching dashboard data:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchData()
  }, [])

  const StatCard = ({ label, value, icon, color }: any) => (
    <div className={`card ${color} p-6`}>
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm text-muted mb-1">{label}</p>
          <p className="text-3xl font-bold">{value}</p>
        </div>
        <span className="text-4xl">{icon}</span>
      </div>
    </div>
  )

  return (
    <DashboardLayout>
      <div className="space-y-8">
        {/* Header */}
        <div>
          <h1 className="heading-1 mb-2">Dashboard</h1>
          <p className="text-muted">Welcome back! Here's your property portfolio overview.</p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard label="Total Properties" value={stats.totalProperties} icon="🏢" color="card-highlighted" />
          <StatCard label="Active Tenants" value={stats.activeTenants} icon="👥" color="bg-blue-50 dark:bg-blue-900/20" />
          <StatCard label="Rent Collected" value={`$${stats.totalRentCollected.toLocaleString()}`} icon="💰" color="bg-green-50 dark:bg-green-900/20" />
          <StatCard label="Pending Payments" value={`$${stats.pendingPayments.toLocaleString()}`} icon="⏳" color="bg-yellow-50 dark:bg-yellow-900/20" />
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Link href="/dashboard/properties/new">
            <button className="btn-primary w-full py-3">
              <span className="mr-2">➕</span> Add Property
            </button>
          </Link>
          <Link href="/dashboard/tenants/new">
            <button className="btn-secondary w-full py-3">
              <span className="mr-2">➕</span> Add Tenant
            </button>
          </Link>
          <Link href="/dashboard/payments">
            <button className="btn-outline w-full py-3">
              <span className="mr-2">📊</span> View Payments
            </button>
          </Link>
        </div>

        {/* Recent Payments */}
        <div className="card">
          <h2 className="heading-3 mb-4">Recent Payments</h2>
          {loading ? (
            <p className="text-muted">Loading...</p>
          ) : recentPayments.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="border-b border-border">
                  <tr>
                    <th className="text-left py-2 px-2 font-semibold">Tenant</th>
                    <th className="text-left py-2 px-2 font-semibold">Amount</th>
                    <th className="text-left py-2 px-2 font-semibold">Due Date</th>
                    <th className="text-left py-2 px-2 font-semibold">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {recentPayments.map((payment) => (
                    <tr key={payment.id} className="border-b border-border/50 hover:bg-foreground/5">
                      <td className="py-3 px-2">Tenant {payment.tenant_id?.slice(0, 8)}</td>
                      <td className="py-3 px-2 font-medium">${payment.amount}</td>
                      <td className="py-3 px-2">{new Date(payment.due_date).toLocaleDateString()}</td>
                      <td className="py-3 px-2">
                        <span className={`badge badge-${payment.status}`}>{payment.status}</span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-muted">No payments yet</p>
          )}
        </div>
      </div>
    </DashboardLayout>
  )
}
