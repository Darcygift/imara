"use client";

import { useState } from "react";
import Link from "next/link";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      // TODO: Connect to backend API
      console.log("Login attempt:", { email, password });
      // Redirect to dashboard after successful login
      // window.location.href = '/';
    } catch (err) {
      setError("Invalid email or password");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex h-screen bg-background">
      {/* Left Side - Branding */}
      <div className="hidden lg:flex lg:w-1/2 bg-secondary text-white flex-col justify-between p-12">
        <div>
          <h1 className="text-5xl font-bold">Smart Rent</h1>
          <p className="text-xl text-white/60 mt-2">
            Professional Property Management
          </p>
        </div>

        <div className="space-y-6">
          <div className="flex gap-4">
            <div className="text-3xl">🏠</div>
            <div>
              <h3 className="font-semibold text-lg">Manage Properties</h3>
              <p className="text-white/60">
                Organize all your properties in one place
              </p>
            </div>
          </div>

          <div className="flex gap-4">
            <div className="text-3xl">💰</div>
            <div>
              <h3 className="font-semibold text-lg">Track Payments</h3>
              <p className="text-white/60">
                Monitor rent collection and outstanding amounts
              </p>
            </div>
          </div>

          <div className="flex gap-4">
            <div className="text-3xl">👥</div>
            <div>
              <h3 className="font-semibold text-lg">Manage Tenants</h3>
              <p className="text-white/60">
                Keep track of all tenant information and leases
              </p>
            </div>
          </div>
        </div>

        <p className="text-sm text-white/60">
          © 2024 Smart Rent. All rights reserved.
        </p>
      </div>

      {/* Right Side - Login Form */}
      <div className="flex-1 flex items-center justify-center p-6 lg:p-12">
        <div className="w-full max-w-md">
          <div className="text-center mb-10">
            <h2 className="text-3xl font-bold mb-2">Welcome Back</h2>
            <p className="text-foreground/60">
              Sign in to your Smart Rent account
            </p>
          </div>

          <form onSubmit={handleLogin} className="space-y-5">
            {error && (
              <div className="bg-red-500/10 border border-red-500/20 rounded-lg p-4 text-red-600 text-sm">
                {error}
              </div>
            )}

            <div>
              <label className="block text-sm font-medium mb-2">Email</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="landlord@example.com"
                className="input-field"
                required
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-2">
                Password
              </label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
                className="input-field"
                required
              />
            </div>

            <div className="flex items-center justify-between text-sm">
              <label className="flex items-center gap-2">
                <input type="checkbox" className="w-4 h-4 rounded" />
                <span>Remember me</span>
              </label>
              <Link href="#" className="text-primary hover:underline">
                Forgot password?
              </Link>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="btn-primary w-full disabled:opacity-50"
            >
              {loading ? "Signing in..." : "Sign In"}
            </button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-foreground/60">
              Don&apos;t have an account?{" "}
              <Link href="/register" className="text-primary hover:underline">
                Create one
              </Link>
            </p>
          </div>

          <div className="mt-8 pt-6 border-t border-border">
            <p className="text-xs text-foreground/60 text-center">
              Demo credentials: landlord@example.com / password123
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
