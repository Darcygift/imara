import Link from 'next/link'

export default function Home() {

  return (
    <div className="min-h-screen bg-background">
      {/* Navigation */}
      <nav className="fixed top-0 w-full bg-background/80 backdrop-blur-md border-b border-border z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
          <div className="flex items-center gap-2">
            <span className="text-2xl">🏠</span>
            <span className="text-xl font-bold text-foreground">Smart Rent</span>
          </div>
          <div className="flex gap-4">
            <Link href="/auth/login">
              <button className="btn-ghost">Login</button>
            </Link>
            <Link href="/auth/sign-up">
              <button className="btn-primary">Sign Up</button>
            </Link>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="pt-32 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div className="grid md:grid-cols-2 gap-12 items-center">
          <div className="space-y-6">
            <div className="space-y-4">
              <h1 className="heading-1 text-5xl">
                Professional Property Management Made Simple
              </h1>
              <p className="text-xl text-muted max-w-lg">
                Manage properties, tenants, and payments all in one platform. Streamline your rental business with Smart Rent.
              </p>
            </div>
            <div className="flex gap-4">
              <Link href="/auth/sign-up">
                <button className="btn-primary py-3 px-8 text-lg">
                  Get Started Free
                </button>
              </Link>
              <Link href="/auth/login">
                <button className="btn-outline py-3 px-8 text-lg">
                  Login to Account
                </button>
              </Link>
            </div>
            <p className="text-sm text-muted">No credit card required. Start managing in minutes.</p>
          </div>
          <div className="bg-gradient-to-br from-primary/10 to-accent/10 rounded-2xl p-8 h-96 flex items-center justify-center">
            <div className="text-center space-y-4">
              <div className="text-6xl">🏢</div>
              <p className="text-foreground/60">Professional Property Management Platform</p>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 bg-foreground/5 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="heading-2 mb-4">Powerful Features</h2>
            <p className="text-muted max-w-2xl mx-auto">Everything you need to manage your rental properties efficiently</p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              {
                icon: '🏠',
                title: 'Property Management',
                description: 'Easily manage multiple properties with all their details in one place',
              },
              {
                icon: '👥',
                title: 'Tenant Management',
                description: 'Track tenant information, leases, and employment history',
              },
              {
                icon: '💰',
                title: 'Payment Tracking',
                description: 'Monitor rent payments, track overdue amounts, and send reminders',
              },
              {
                icon: '📊',
                title: 'Analytics & Reports',
                description: 'Get insights into your portfolio with detailed analytics',
              },
              {
                icon: '📱',
                title: 'Mobile Payments',
                description: 'Accept MTN MoMo and other mobile money payments',
              },
              {
                icon: '🔒',
                title: 'Secure & Reliable',
                description: 'Enterprise-grade security with Supabase authentication',
              },
            ].map((feature, idx) => (
              <div key={idx} className="card hover:shadow-lg transition-shadow">
                <div className="text-4xl mb-4">{feature.icon}</div>
                <h3 className="font-semibold text-lg mb-2">{feature.title}</h3>
                <p className="text-muted">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Pricing Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div className="text-center mb-16">
          <h2 className="heading-2 mb-4">Simple Pricing</h2>
          <p className="text-muted max-w-2xl mx-auto">Choose the plan that fits your needs</p>
        </div>

        <div className="grid md:grid-cols-3 gap-8">
          {[
            {
              name: 'Starter',
              price: 'Free',
              description: 'Perfect for getting started',
              features: [
                'Up to 5 properties',
                'Basic tenant management',
                'Payment tracking',
                'Email support',
              ],
            },
            {
              name: 'Professional',
              price: '$29',
              period: '/month',
              description: 'For growing businesses',
              features: [
                'Unlimited properties',
                'Advanced analytics',
                'Mobile payment integration',
                'SMS notifications',
                'Priority support',
              ],
              highlighted: true,
            },
            {
              name: 'Enterprise',
              price: 'Custom',
              description: 'For large portfolios',
              features: [
                'Everything in Professional',
                'API access',
                'Custom integrations',
                'Dedicated account manager',
                '24/7 phone support',
              ],
            },
          ].map((plan, idx) => (
            <div
              key={idx}
              className={`card ${plan.highlighted ? 'ring-2 ring-primary scale-105' : ''} flex flex-col`}
            >
              <h3 className="font-semibold text-xl mb-2">{plan.name}</h3>
              <p className="text-muted text-sm mb-4">{plan.description}</p>
              <div className="mb-6">
                <span className="text-4xl font-bold">{plan.price}</span>
                {plan.period && <span className="text-muted text-sm">{plan.period}</span>}
              </div>
              <ul className="space-y-3 mb-6 flex-1">
                {plan.features.map((feature, i) => (
                  <li key={i} className="text-sm flex items-center gap-2">
                    <span className="text-accent">✓</span>
                    {feature}
                  </li>
                ))}
              </ul>
              <Link href="/auth/sign-up">
                <button className={`w-full ${plan.highlighted ? 'btn-primary' : 'btn-outline'}`}>
                  Get Started
                </button>
              </Link>
            </div>
          ))}
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 bg-primary text-white rounded-2xl max-w-7xl mx-auto mb-20">
        <div className="text-center space-y-6">
          <h2 className="text-4xl font-bold">Ready to Transform Your Property Management?</h2>
          <p className="text-white/80 max-w-2xl mx-auto">
            Join thousands of property managers using Smart Rent to streamline their business
          </p>
          <Link href="/auth/sign-up">
            <button className="bg-white text-primary font-semibold py-3 px-8 rounded-lg hover:bg-white/90 transition-all">
              Start Your Free Trial
            </button>
          </Link>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-foreground/5 border-t border-border py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="grid md:grid-cols-4 gap-8 mb-8">
            <div>
              <div className="flex items-center gap-2 mb-4">
                <span className="text-2xl">🏠</span>
                <span className="font-bold">Smart Rent</span>
              </div>
              <p className="text-muted text-sm">Professional property management made simple</p>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Product</h4>
              <ul className="space-y-2 text-sm text-muted">
                <li><a href="#" className="hover:text-foreground">Features</a></li>
                <li><a href="#" className="hover:text-foreground">Pricing</a></li>
                <li><a href="#" className="hover:text-foreground">Security</a></li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Company</h4>
              <ul className="space-y-2 text-sm text-muted">
                <li><a href="#" className="hover:text-foreground">About</a></li>
                <li><a href="#" className="hover:text-foreground">Blog</a></li>
                <li><a href="#" className="hover:text-foreground">Contact</a></li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Legal</h4>
              <ul className="space-y-2 text-sm text-muted">
                <li><a href="#" className="hover:text-foreground">Privacy</a></li>
                <li><a href="#" className="hover:text-foreground">Terms</a></li>
                <li><a href="#" className="hover:text-foreground">Security</a></li>
              </ul>
            </div>
          </div>
          <div className="border-t border-border pt-8">
            <p className="text-center text-muted text-sm">
              © 2024 Smart Rent. All rights reserved.
            </p>
          </div>
        </div>
      </footer>
    </div>
  )
}
