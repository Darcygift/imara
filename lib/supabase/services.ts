'use client'

import { createClient } from './client'

export type Property = {
  id: string
  landlord_id: string
  name: string
  address: string
  city: string
  state?: string
  postal_code?: string
  country: string
  property_type: string
  total_units: number
  bedrooms?: number
  bathrooms?: number
  square_feet?: number
  purchase_price?: number
  purchase_date?: string
  description?: string
  image_url?: string
  created_at: string
  updated_at: string
}

export type Unit = {
  id: string
  property_id: string
  unit_number: string
  bedrooms?: number
  bathrooms?: number
  square_feet?: number
  rent_amount: number
  deposit_amount?: number
  status: 'occupied' | 'vacant' | 'maintenance'
  created_at: string
  updated_at: string
}

export type Tenant = {
  id: string
  landlord_id: string
  unit_id?: string
  first_name: string
  last_name: string
  email: string
  phone?: string
  date_of_birth?: string
  id_number?: string
  employment_status?: string
  employer?: string
  annual_income?: number
  lease_start_date: string
  lease_end_date: string
  lease_amount: number
  deposit_paid: number
  status: 'active' | 'inactive' | 'evicted'
  notes?: string
  created_at: string
  updated_at: string
}

export type Payment = {
  id: string
  landlord_id: string
  tenant_id: string
  unit_id?: string
  amount: number
  due_date: string
  payment_date?: string
  status: 'pending' | 'completed' | 'overdue' | 'partial'
  payment_method?: string
  transaction_id?: string
  notes?: string
  created_at: string
  updated_at: string
}

// Property Services
export async function getProperties() {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('properties')
    .select('*')
    .order('created_at', { ascending: false })

  if (error) throw error
  return data as Property[]
}

export async function getProperty(id: string) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('properties')
    .select('*')
    .eq('id', id)
    .single()

  if (error) throw error
  return data as Property
}

export async function createProperty(property: Omit<Property, 'id' | 'created_at' | 'updated_at'>) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('properties')
    .insert([property])
    .select()

  if (error) throw error
  return data?.[0] as Property
}

export async function updateProperty(id: string, updates: Partial<Property>) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('properties')
    .update(updates)
    .eq('id', id)
    .select()

  if (error) throw error
  return data?.[0] as Property
}

export async function deleteProperty(id: string) {
  const supabase = createClient()
  const { error } = await supabase
    .from('properties')
    .delete()
    .eq('id', id)

  if (error) throw error
}

// Unit Services
export async function getUnits(propertyId: string) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('units')
    .select('*')
    .eq('property_id', propertyId)
    .order('unit_number', { ascending: true })

  if (error) throw error
  return data as Unit[]
}

export async function createUnit(unit: Omit<Unit, 'id' | 'created_at' | 'updated_at'>) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('units')
    .insert([unit])
    .select()

  if (error) throw error
  return data?.[0] as Unit
}

// Tenant Services
export async function getTenants() {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('tenants')
    .select('*')
    .order('created_at', { ascending: false })

  if (error) throw error
  return data as Tenant[]
}

export async function getTenant(id: string) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('tenants')
    .select('*')
    .eq('id', id)
    .single()

  if (error) throw error
  return data as Tenant
}

export async function createTenant(tenant: Omit<Tenant, 'id' | 'created_at' | 'updated_at'>) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('tenants')
    .insert([tenant])
    .select()

  if (error) throw error
  return data?.[0] as Tenant
}

export async function updateTenant(id: string, updates: Partial<Tenant>) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('tenants')
    .update(updates)
    .eq('id', id)
    .select()

  if (error) throw error
  return data?.[0] as Tenant
}

// Payment Services
export async function getPayments() {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('payments')
    .select('*')
    .order('due_date', { ascending: false })

  if (error) throw error
  return data as Payment[]
}

export async function getPaymentsByTenant(tenantId: string) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('payments')
    .select('*')
    .eq('tenant_id', tenantId)
    .order('due_date', { ascending: false })

  if (error) throw error
  return data as Payment[]
}

export async function createPayment(payment: Omit<Payment, 'id' | 'created_at' | 'updated_at'>) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('payments')
    .insert([payment])
    .select()

  if (error) throw error
  return data?.[0] as Payment
}

export async function recordPayment(id: string, updates: { payment_date: string; status: 'completed' | 'partial' }) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from('payments')
    .update(updates)
    .eq('id', id)
    .select()

  if (error) throw error
  return data?.[0] as Payment
}

export async function getDashboardStats() {
  const supabase = createClient()

  const [propertiesData, tenantsData, paymentsData] = await Promise.all([
    supabase.from('properties').select('id', { count: 'exact' }),
    supabase.from('tenants').select('id', { count: 'exact' }).eq('status', 'active'),
    supabase.from('payments').select('*').eq('status', 'completed'),
  ])

  const totalRent = paymentsData.data?.reduce((sum, payment) => sum + (payment.amount || 0), 0) || 0
  const pendingPayments = await supabase
    .from('payments')
    .select('amount')
    .eq('status', 'pending')
    .then((res) => res.data?.reduce((sum, payment) => sum + (payment.amount || 0), 0) || 0)

  return {
    totalProperties: propertiesData.count || 0,
    activeTenants: tenantsData.count || 0,
    totalRentCollected: totalRent,
    pendingPayments,
  }
}
