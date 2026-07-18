import { NextRequest, NextResponse } from "next/server";

// Mock database
const propertiesDB: any[] = [
  {
    id: 1,
    name: "Sunset Apartments",
    address: "123 Main Street, Downtown",
    city: "New York",
    type: "apartment",
    units: 8,
  },
  {
    id: 2,
    name: "Park View Building",
    address: "456 Oak Avenue, Midtown",
    city: "New York",
    type: "apartment",
    units: 12,
  },
];

export async function GET(request: NextRequest) {
  return NextResponse.json({
    success: true,
    data: propertiesDB,
  });
}

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();

    if (!body.name || !body.address || !body.city || !body.type) {
      return NextResponse.json(
        {
          success: false,
          error: "Missing required fields",
        },
        { status: 400 }
      );
    }

    const newProperty = {
      id: propertiesDB.length + 1,
      ...body,
    };

    propertiesDB.push(newProperty);

    return NextResponse.json(
      {
        success: true,
        message: "Property created successfully",
        data: newProperty,
      },
      { status: 201 }
    );
  } catch (error) {
    return NextResponse.json(
      {
        success: false,
        error: "Failed to create property",
      },
      { status: 500 }
    );
  }
}
