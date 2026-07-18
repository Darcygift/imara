'use client'

import Link from 'next/link'
import { useState, useEffect } from 'react'
import { usePathname, useRouter } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'

interface NavItem {
  href: string
  label: string
  icon: string
  badge?: number
}

const navItems: NavItem[] = [
  { href: '/dashboard', label: 'Dashboard', icon: '📊' },
  { href: '/properties', label: 'Properties', icon: '🏢', badge: 3 },
  { href: '/tenants', label: 'Tenants', icon: '👥', badge: 12 },
  { href: '/payments', label: 'Payments', icon: '💳', badge: 5 },
  { href: '/reports', label: 'Reports', icon: '📈' },
  { href: '/settings', label: 'Settings', icon: '⚙️' },
]

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(true)
  const [user, setUser] = useState<any>(null)
  const pathname = usePathname()
  const router = useRouter()
  const supabase = createClient()

  useEffect(() => {
    const getUser = async () => {
      const {
        data: { user },
      } = await supabase.auth.getUser()
      setUser(user)

      if (!user && !pathname.includes('/auth')) {
        router.push('/auth/login')
      }
    }

    getUser()
  }, [supabase, router, pathname])

  const handleLogout = async () => {
    await supabase.auth.signOut()
    router.push('/auth/login')
  }

  return (
    <div className="flex h-screen bg-background overflow-hidden">
      {/* Sidebar */}
      <aside
        className={`sidebar transition-all duration-300 ${
          sidebarOpen ? 'w-64' : 'w-20'
        } border-r border-border flex flex-col`}
      >
        {/* Logo */}
        <div className="p-4 border-b border-border/50 flex items-center justify-between">
          {sidebarOpen && (
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                <span className="text-white text-lg font-bold">SR</span>
              </div>
              <div>
                <h2 className="font-bold text-white text-sm">Smart Rent</h2>
                <p className="text-xs text-white/60">Property Mgmt</p>
              </div>
            </div>
          )}
          <button
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className="p-1 hover:bg-white/10 rounded-lg transition-colors"
          >
            {sidebarOpen ? '←' : '→'}
          </button>
        </div>

        {/* Navigation */}
        <nav className="flex-1 overflow-y-auto p-4 space-y-2">
          {navItems.map((item) => (
            <Link key={item.href} href={item.href}>
              <div
                className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-all ${
                  pathname === item.href
                    ? 'bg-primary text-white font-medium'
                    : 'text-white/70 hover:bg-white/10'
                }`}
              >
                <span className="text-xl">{item.icon}</span>
                {sidebarOpen && (
                  <div className="flex-1">
                    <span>{item.label}</span>
                    {item.badge && (
                      <span className="ml-auto inline-block bg-red-500 text-white text-xs rounded-full px-2 py-1">
                        {item.badge}
                      </span>
                    )}
                  </div>
                )}
              </div>
            </Link>
          ))}
        </nav>

        {/* User Section */}
        <div className="p-4 border-t border-white/10">
          <div className="flex items-center gap-3 mb-4">
            <div className="w-10 h-10 bg-gradient-to-br from-accent to-primary rounded-full flex items-center justify-center text-white font-bold">
              {user?.email?.charAt(0).toUpperCase() || 'U'}
            </div>
            {sidebarOpen && (
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-white truncate">
                  {user?.user_metadata?.first_name || 'User'}
                </p>
                <p className="text-xs text-white/60 truncate">{user?.email}</p>
              </div>
            )}
          </div>
          <button
            onClick={handleLogout}
            className="w-full px-3 py-2 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-colors text-sm font-medium"
          >
            {sidebarOpen ? 'Logout' : '↓'}
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Top Header */}
        <header className="bg-white dark:bg-[hsl(var(--secondary))] border-b border-border/50 px-8 py-4 flex items-center justify-between shadow-sm">
          <div>
            <h1 className="heading-3 text-foreground">Welcome back</h1>
            <p className="text-sm text-muted">
              {new Date().toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
              })}
            </p>
          </div>
          <div className="flex items-center gap-4">
            <button className="p-2 hover:bg-foreground/10 rounded-lg transition-colors">
              🔔
            </button>
            <button className="p-2 hover:bg-foreground/10 rounded-lg transition-colors">
              ⚙️
            </button>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-y-auto bg-gradient-to-br from-background via-background to-accent/5">
          {children}
        </main>
      </div>
    </div>
  )
}
