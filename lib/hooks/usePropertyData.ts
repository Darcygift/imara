'use client'

import { useCallback, useEffect, useState } from 'react'
import { getProperties, getProperty, createProperty, updateProperty, deleteProperty, Property } from '@/lib/supabase/services'

export function useProperties() {
  const [properties, setProperties] = useState<Property[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<Error | null>(null)

  const fetchProperties = useCallback(async () => {
    try {
      setLoading(true)
      const data = await getProperties()
      setProperties(data)
    } catch (err) {
      setError(err instanceof Error ? err : new Error('Failed to fetch properties'))
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    fetchProperties()
  }, [fetchProperties])

  const addProperty = useCallback(
    async (property: Omit<Property, 'id' | 'created_at' | 'updated_at'>) => {
      try {
        const newProperty = await createProperty(property)
        setProperties((prev) => [newProperty, ...prev])
        return newProperty
      } catch (err) {
        throw err instanceof Error ? err : new Error('Failed to create property')
      }
    },
    []
  )

  const updatePropertyData = useCallback(async (id: string, updates: Partial<Property>) => {
    try {
      const updated = await updateProperty(id, updates)
      setProperties((prev) => prev.map((p) => (p.id === id ? updated : p)))
      return updated
    } catch (err) {
      throw err instanceof Error ? err : new Error('Failed to update property')
    }
  }, [])

  const removeProperty = useCallback(async (id: string) => {
    try {
      await deleteProperty(id)
      setProperties((prev) => prev.filter((p) => p.id !== id))
    } catch (err) {
      throw err instanceof Error ? err : new Error('Failed to delete property')
    }
  }, [])

  return {
    properties,
    loading,
    error,
    addProperty,
    updateProperty: updatePropertyData,
    removeProperty,
    refetch: fetchProperties,
  }
}
