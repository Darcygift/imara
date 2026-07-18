import { NextRequest, NextResponse } from "next/server";

// Mock database
const tenantsDB: any[] = [
  {
    id: 1,
    name: "John Doe",
    phone: "(555) 123-4567",
    email: "john@example.com",
    propertyId: 1,
    unit: "101",
    leaseStart: "2024-01-15",
    leaseEnd: "2025-01-14",
  },
  {
    id: 2,
    name: "Jane Smith",
    phone: "(555) 234-5678",
    email: "jane@example.com",
    propertyId: 1,
    unit: "205",
    leaseStart: "2023-06-01",
    leaseEnd: "2025-05-31",
  },
];

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url);
  const propertyId = searchParams.get("propertyId");

  let filtered = tenantsDB;

  if (propertyId) {
    filtered = filtered.filter(
      (t) => t.propertyId === parseInt(propertyId)
    );
  }

  return NextResponse.json({
    success: true,
    data: filtered,
  });
}

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();

    if (
      !body.name ||
      !body.phone ||
      !body.email ||
      !body.propertyId ||
      !body.unit
    ) {
      return NextResponse.json(
        {
          success: false,
          error: "Missing required fields",
        },
        { status: 400 }
      );
    }

    const newTenant = {
      id: tenantsDB.length + 1,
      ...body,
    };

    tenantsDB.push(newTenant);

    return NextResponse.json(
      {
        success: true,
        message: "Tenant added successfully",
        data: newTenant,
      },
      { status: 201 }
    );
  } catch (error) {
    return NextResponse.json(
      {
        success: false,
        error: "Failed to add tenant",
      },
      { status: 500 }
    );
  }
}
