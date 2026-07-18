'use client'

import Link from 'next/link'
import { useEffect, useState } from 'react'

export default function SignUpSuccessPage() {
  const [email, setEmail] = useState('')

  useEffect(() => {
    const savedEmail = localStorage.getItem('signUpEmail')
    if (savedEmail) setEmail(savedEmail)
  }, [])

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-accent/5 flex items-center justify-center p-4">
      <div className="w-full max-w-md text-center">
        {/* Success Icon */}
        <div className="flex justify-center mb-6">
          <div className="w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
            <svg className="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>

        {/* Message */}
        <h1 className="heading-2 mb-3">Check Your Email</h1>
        <p className="text-muted mb-6">
          We&apos;ve sent a confirmation link to <span className="font-medium text-foreground">{email || 'your email'}</span>. Click the link to verify your account and get started.
        </p>

        {/* Info Box */}
        <div className="card bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 mb-6">
          <h3 className="font-medium text-blue-900 dark:text-blue-400 mb-2">What&apos;s next?</h3>
          <ul className="text-sm text-blue-800 dark:text-blue-300 space-y-1 text-left">
            <li>✓ Check your email for the verification link</li>
            <li>✓ Click the link to confirm your account</li>
            <li>✓ Set up your first property</li>
          </ul>
        </div>

        {/* Back to Login */}
        <div className="space-y-3">
          <p className="text-sm text-muted">Didn&apos;t receive the email?</p>
          <button className="btn-ghost w-full">
            Resend confirmation email
          </button>
        </div>

        {/* Footer */}
        <div className="mt-8 pt-6 border-t border-border">
          <p className="text-xs text-muted mb-3">
            Already confirmed? Go back to login
          </p>
          <Link href="/auth/login" className="text-primary font-medium hover:underline">
            Sign in to your account
          </Link>
        </div>
      </div>
    </div>
  )
}
