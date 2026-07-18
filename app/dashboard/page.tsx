'use client'

import { useState } from 'react'
import DashboardLayout from '@/components/DashboardLayout'

export default function DashboardPage() {
  const [timeRange, setTimeRange] = useState('month')

  const stats = [
    { label: 'Total Properties', value: '24', change: '+2', icon: '🏢', color: 'from-blue-500 to-blue-600' },
    { label: 'Active Tenants', value: '156', change: '+12', icon: '👥', color: 'from-green-500 to-green-600' },
    { label: 'Monthly Revenue', value: '$45,850', change: '+8%', icon: '💰', color: 'from-emerald-500 to-emerald-600' },
    { label: 'Pending Payments', value: '12', change: '-3', icon: '⏳', color: 'from-amber-500 to-amber-600' },
  ]

  const recentPayments = [
    { id: 1, tenant: 'John Doe', property: 'Maple Street Apt 101', amount: '$1,200', date: 'Today', status: 'completed' },
    { id: 2, tenant: 'Sarah Smith', property: 'Oak Lane House', amount: '$1,800', date: 'Yesterday', status: 'completed' },
    { id: 3, tenant: 'Mike Johnson', property: 'Pine Ave Apt 205', amount: '$950', date: '2 days ago', status: 'pending' },
    { id: 4, tenant: 'Emma Davis', property: 'Cedar St Duplex', amount: '$2,100', date: '3 days ago', status: 'completed' },
  ]

  return (
    <DashboardLayout>
      <div className="p-8">
        {/* Header */}
        <div className="flex justify-between items-center mb-8">
          <div>
            <h2 className="heading-1 mb-2">Dashboard</h2>
            <p className="text-muted">Overview of your property portfolio</p>
          </div>
          <div className="flex gap-2">
            {['week', 'month', 'year'].map((range) => (
              <button
                key={range}
                onClick={() => setTimeRange(range)}
                className={`px-4 py-2 rounded-lg font-medium transition-all ${
                  timeRange === range
                    ? 'bg-primary text-white'
                    : 'bg-white dark:bg-slate-800 text-foreground hover:bg-foreground/5'
                }`}
              >
                {range.charAt(0).toUpperCase() + range.slice(1)}
              </button>
            ))}
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {stats.map((stat) => (
            <div key={stat.label} className="card hover:shadow-lg transition-all group">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <p className="text-muted text-sm mb-1">{stat.label}</p>
                  <h3 className="heading-3">{stat.value}</h3>
                </div>
                <div className={`w-12 h-12 bg-gradient-to-br ${stat.color} rounded-lg flex items-center justify-center text-2xl group-hover:scale-110 transition-transform`}>
                  {stat.icon}
                </div>
              </div>
              <div className="flex items-center gap-2">
                <span className={`text-sm font-medium ${stat.change.includes('+') ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`}>
                  {stat.change}
                </span>
                <span className="text-xs text-muted">vs last period</span>
              </div>
            </div>
          ))}
        </div>

        {/* Main Content Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Revenue Chart */}
          <div className="lg:col-span-2 card">
            <div className="flex justify-between items-center mb-6">
              <h3 className="heading-3">Revenue Trend</h3>
              <button className="text-muted hover:text-foreground transition-colors">📊</button>
            </div>
            
            {/* Chart Placeholder */}
            <div className="h-64 bg-gradient-to-br from-primary/5 to-accent/5 rounded-lg flex items-end justify-around px-4 pb-4">
              {[40, 65, 50, 75, 60, 80, 55].map((height, i) => (
                <div
                  key={i}
                  className="flex-1 mx-1 bg-gradient-to-t from-primary to-primary/40 rounded-t-lg transition-all hover:from-primary/80"
                  style={{ height: `${height}%` }}
                ></div>
              ))}
            </div>
            
            <div className="flex justify-around mt-6 pt-6 border-t border-border">
              {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map((day) => (
                <span key={day} className="text-xs text-muted font-medium">
                  {day}
                </span>
              ))}
            </div>
          </div>

          {/* Quick Actions */}
          <div className="space-y-6">
            {/* Summary Card */}
            <div className="card-highlighted card">
              <h3 className="font-bold text-foreground mb-4">Quick Summary</h3>
              <div className="space-y-3">
                <div className="flex justify-between items-center">
                  <span className="text-muted">Collection Rate</span>
                  <span className="font-bold text-green-600 dark:text-green-400">94.2%</span>
                </div>
                <div className="w-full h-2 bg-foreground/10 rounded-full overflow-hidden">
                  <div className="h-full w-[94%] bg-gradient-to-r from-green-500 to-emerald-500 rounded-full"></div>
                </div>
                <div className="flex justify-between items-center pt-2">
                  <span className="text-muted">Occupancy Rate</span>
                  <span className="font-bold text-blue-600 dark:text-blue-400">89%</span>
                </div>
                <div className="w-full h-2 bg-foreground/10 rounded-full overflow-hidden">
                  <div className="h-full w-[89%] bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full"></div>
                </div>
              </div>
            </div>

            {/* Actions */}
            <button className="btn-primary w-full">+ Add Property</button>
            <button className="btn-outline w-full">View Reports</button>
          </div>
        </div>

        {/* Recent Payments */}
        <div className="card mt-8">
          <h3 className="heading-3 mb-6">Recent Payments</h3>
          
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-foreground/5">
                <tr className="border-b border-border">
                  <th className="px-6 py-3 text-left text-sm font-semibold text-foreground">Tenant</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-foreground">Property</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-foreground">Amount</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-foreground">Date</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-foreground">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border">
                {recentPayments.map((payment) => (
                  <tr key={payment.id} className="hover:bg-foreground/5 transition-colors">
                    <td className="px-6 py-4 text-sm text-foreground font-medium">{payment.tenant}</td>
                    <td className="px-6 py-4 text-sm text-muted">{payment.property}</td>
                    <td className="px-6 py-4 text-sm font-semibold text-foreground">{payment.amount}</td>
                    <td className="px-6 py-4 text-sm text-muted">{payment.date}</td>
                    <td className="px-6 py-4">
                      <span
                        className={`badge ${
                          payment.status === 'completed'
                            ? 'badge-success'
                            : 'badge-pending'
                        }`}
                      >
                        {payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="mt-4 text-center">
            <button className="text-primary hover:underline font-medium">View All Payments →</button>
          </div>
        </div>
      </div>
    </DashboardLayout>
  )
}
