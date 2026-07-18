'use client'

import { useEffect, useState } from 'react'
import { DashboardLayout } from '@/components/DashboardLayout'
import { getPayments, Payment } from '@/lib/supabase/services'
import Link from 'next/link'

export default function PaymentsPage() {
  const [payments, setPayments] = useState<Payment[]>([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState<'all' | 'pending' | 'completed' | 'overdue'>('all')

  useEffect(() => {
    const fetchPayments = async () => {
      try {
        const data = await getPayments()
        setPayments(data)
      } catch (error) {
        console.error('[v0] Error fetching payments:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchPayments()
  }, [])

  const filteredPayments = payments.filter((payment) => {
    if (filter === 'all') return true
    return payment.status === filter
  })

  const stats = {
    total: payments.reduce((sum, p) => sum + p.amount, 0),
    pending: payments
      .filter((p) => p.status === 'pending')
      .reduce((sum, p) => sum + p.amount, 0),
    completed: payments
      .filter((p) => p.status === 'completed')
      .reduce((sum, p) => sum + p.amount, 0),
  }

  const getStatusBadge = (status: string) => {
    const styles = {
      pending: 'badge-pending',
      completed: 'badge-success',
      overdue: 'badge-danger',
      partial: 'badge-info',
    }
    return styles[status as keyof typeof styles] || 'badge-info'
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex justify-between items-center">
          <div>
            <h1 className="heading-1">Payments</h1>
            <p className="text-muted mt-1">Track and manage rent payments</p>
          </div>
          <Link href="/dashboard/payments/new">
            <button className="btn-primary">Record Payment</button>
          </Link>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="card-highlighted card">
            <p className="text-sm text-muted mb-1">Total Amount</p>
            <p className="text-3xl font-bold">${stats.total.toLocaleString()}</p>
          </div>
          <div className="card bg-yellow-50 dark:bg-yellow-900/20">
            <p className="text-sm text-muted mb-1">Pending</p>
            <p className="text-3xl font-bold text-yellow-600">${stats.pending.toLocaleString()}</p>
          </div>
          <div className="card bg-green-50 dark:bg-green-900/20">
            <p className="text-sm text-muted mb-1">Completed</p>
            <p className="text-3xl font-bold text-green-600">${stats.completed.toLocaleString()}</p>
          </div>
        </div>

        {/* Filters */}
        <div className="flex gap-2">
          {(['all', 'pending', 'completed', 'overdue'] as const).map((status) => (
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

        {/* Payments Table */}
        {loading ? (
          <div className="card">
            <p className="text-muted">Loading payments...</p>
          </div>
        ) : filteredPayments.length > 0 ? (
          <div className="card overflow-x-auto">
            <table className="w-full">
              <thead className="border-b border-border bg-foreground/5">
                <tr>
                  <th className="text-left py-3 px-4 font-semibold">Tenant ID</th>
                  <th className="text-left py-3 px-4 font-semibold">Amount</th>
                  <th className="text-left py-3 px-4 font-semibold">Due Date</th>
                  <th className="text-left py-3 px-4 font-semibold">Payment Date</th>
                  <th className="text-left py-3 px-4 font-semibold">Method</th>
                  <th className="text-left py-3 px-4 font-semibold">Status</th>
                  <th className="text-left py-3 px-4 font-semibold">Action</th>
                </tr>
              </thead>
              <tbody>
                {filteredPayments.map((payment) => (
                  <tr key={payment.id} className="border-b border-border/50 hover:bg-foreground/2">
                    <td className="py-3 px-4 font-mono text-sm">{payment.tenant_id.slice(0, 8)}...</td>
                    <td className="py-3 px-4 font-medium">${payment.amount}</td>
                    <td className="py-3 px-4">{new Date(payment.due_date).toLocaleDateString()}</td>
                    <td className="py-3 px-4">
                      {payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : 'Pending'}
                    </td>
                    <td className="py-3 px-4 capitalize">{payment.payment_method || 'Not set'}</td>
                    <td className="py-3 px-4">
                      <span className={`badge ${getStatusBadge(payment.status)}`}>{payment.status}</span>
                    </td>
                    <td className="py-3 px-4">
                      <button className="btn-ghost text-sm px-3">Edit</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="card text-center py-12">
            <p className="text-muted mb-4">No payments found</p>
            <Link href="/dashboard/payments/new">
              <button className="btn-primary">Record Your First Payment</button>
            </Link>
          </div>
        )}

        {/* MTN MoMo Integration Info */}
        <div className="card bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/50">
          <h3 className="font-semibold mb-2">MTN MoMo Integration</h3>
          <p className="text-sm text-muted mb-3">Enable mobile money payments to accept MTN MoMo transactions directly from tenants.</p>
          <button className="btn-primary text-sm">Configure MTN MoMo</button>
        </div>
      </div>
    </DashboardLayout>
  )
}
