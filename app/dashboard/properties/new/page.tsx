'use client'

import { useState } from 'react'
import { DashboardLayout } from '@/components/DashboardLayout'
import { createProperty } from '@/lib/supabase/services'
import { useRouter } from 'next/navigation'

export default function NewPropertyPage() {
  const router = useRouter()
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [formData, setFormData] = useState({
    name: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'US',
    property_type: 'apartment',
    total_units: 1,
    bedrooms: 2,
    bathrooms: 1,
    square_feet: 1000,
    purchase_price: 0,
    purchase_date: '',
    description: '',
  })

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData((prev) => ({
      ...prev,
      [name]: ['total_units', 'bedrooms', 'bathrooms', 'square_feet', 'purchase_price'].includes(name)
        ? parseFloat(value) || 0
        : value,
    }))
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    setError(null)

    try {
      await createProperty({
        ...formData,
        landlord_id: '', // Will be set by RLS context
      })
      router.push('/dashboard/properties')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create property')
    } finally {
      setLoading(false)
    }
  }

  return (
    <DashboardLayout>
      <div className="max-w-2xl">
        <div className="mb-6">
          <h1 className="heading-1">Add New Property</h1>
          <p className="text-muted mt-1">Fill in the details of your property</p>
        </div>

        <form onSubmit={handleSubmit} className="card space-y-6">
          {error && <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 p-3 rounded text-red-700 dark:text-red-400 text-sm">{error}</div>}

          {/* Basic Information */}
          <div>
            <h3 className="font-semibold mb-4">Basic Information</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-1">Property Name *</label>
                <input
                  type="text"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  className="input-field"
                  placeholder="e.g., Sunset Apartments"
                  required
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium mb-1">Property Type *</label>
                  <select name="property_type" value={formData.property_type} onChange={handleChange} className="input-field">
                    <option value="apartment">Apartment</option>
                    <option value="house">House</option>
                    <option value="commercial">Commercial</option>
                    <option value="condo">Condo</option>
                    <option value="townhouse">Townhouse</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">Total Units *</label>
                  <input
                    type="number"
                    name="total_units"
                    value={formData.total_units}
                    onChange={handleChange}
                    className="input-field"
                    min="1"
                    required
                  />
                </div>
              </div>
            </div>
          </div>

          <div className="divider"></div>

          {/* Address Information */}
          <div>
            <h3 className="font-semibold mb-4">Address</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-1">Street Address *</label>
                <input
                  type="text"
                  name="address"
                  value={formData.address}
                  onChange={handleChange}
                  className="input-field"
                  placeholder="123 Main Street"
                  required
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium mb-1">City *</label>
                  <input
                    type="text"
                    name="city"
                    value={formData.city}
                    onChange={handleChange}
                    className="input-field"
                    placeholder="New York"
                    required
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">State/Province</label>
                  <input type="text" name="state" value={formData.state} onChange={handleChange} className="input-field" placeholder="NY" />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium mb-1">Postal Code</label>
                  <input type="text" name="postal_code" value={formData.postal_code} onChange={handleChange} className="input-field" placeholder="10001" />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">Country</label>
                  <input type="text" name="country" value={formData.country} onChange={handleChange} className="input-field" />
                </div>
              </div>
            </div>
          </div>

          <div className="divider"></div>

          {/* Property Details */}
          <div>
            <h3 className="font-semibold mb-4">Property Details</h3>
            <div className="grid grid-cols-3 gap-4">
              <div>
                <label className="block text-sm font-medium mb-1">Bedrooms</label>
                <input type="number" name="bedrooms" value={formData.bedrooms} onChange={handleChange} className="input-field" min="0" />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">Bathrooms</label>
                <input type="number" name="bathrooms" value={formData.bathrooms} onChange={handleChange} className="input-field" step="0.5" min="0" />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">Square Feet</label>
                <input type="number" name="square_feet" value={formData.square_feet} onChange={handleChange} className="input-field" min="0" />
              </div>
            </div>
          </div>

          <div className="divider"></div>

          {/* Financial Information */}
          <div>
            <h3 className="font-semibold mb-4">Financial Information</h3>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium mb-1">Purchase Price</label>
                <input type="number" name="purchase_price" value={formData.purchase_price} onChange={handleChange} className="input-field" min="0" />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">Purchase Date</label>
                <input type="date" name="purchase_date" value={formData.purchase_date} onChange={handleChange} className="input-field" />
              </div>
            </div>
          </div>

          <div className="divider"></div>

          {/* Description */}
          <div>
            <label className="block text-sm font-medium mb-1">Description</label>
            <textarea
              name="description"
              value={formData.description}
              onChange={handleChange}
              className="input-field"
              placeholder="Add any additional notes about the property"
              rows={4}
            />
          </div>

          {/* Buttons */}
          <div className="flex gap-4 pt-6">
            <button type="submit" disabled={loading} className="btn-primary flex-1">
              {loading ? 'Creating...' : 'Create Property'}
            </button>
            <button
              type="button"
              onClick={() => router.back()}
              className="btn-ghost flex-1"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </DashboardLayout>
  )
}
