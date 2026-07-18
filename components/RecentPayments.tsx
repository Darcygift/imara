"use client";

export default function RecentPayments() {
  const payments = [
    {
      id: 1,
      tenant: "John Doe",
      property: "Sunset Apartments - Unit 101",
      amount: 500,
      status: "completed",
      date: "Today",
    },
    {
      id: 2,
      tenant: "Jane Smith",
      property: "Downtown Complex - Unit 205",
      amount: 750,
      status: "pending",
      date: "Yesterday",
    },
    {
      id: 3,
      tenant: "Mike Johnson",
      property: "Park View Building - Unit 305",
      amount: 600,
      status: "completed",
      date: "2 days ago",
    },
    {
      id: 4,
      tenant: "Sarah Williams",
      property: "Riverside Homes - Unit 102",
      amount: 450,
      status: "pending",
      date: "3 days ago",
    },
    {
      id: 5,
      tenant: "Robert Brown",
      property: "Central District - Unit 504",
      amount: 800,
      status: "overdue",
      date: "1 week ago",
    },
  ];

  const getStatusBadgeClass = (status: string) => {
    switch (status) {
      case "completed":
        return "badge-success";
      case "pending":
        return "badge-pending";
      case "overdue":
        return "badge-danger";
      default:
        return "badge-pending";
    }
  };

  return (
    <div className="card">
      <div className="flex justify-between items-center mb-6">
        <h3 className="text-xl font-bold">Recent Payments</h3>
        <a
          href="#"
          className="text-primary hover:underline text-sm font-medium"
        >
          View All
        </a>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full">
          <thead>
            <tr className="border-b border-border">
              <th className="text-left py-3 px-4 font-semibold text-foreground/60">
                Tenant
              </th>
              <th className="text-left py-3 px-4 font-semibold text-foreground/60">
                Property
              </th>
              <th className="text-left py-3 px-4 font-semibold text-foreground/60">
                Amount
              </th>
              <th className="text-left py-3 px-4 font-semibold text-foreground/60">
                Status
              </th>
              <th className="text-left py-3 px-4 font-semibold text-foreground/60">
                Date
              </th>
            </tr>
          </thead>
          <tbody>
            {payments.map((payment) => (
              <tr
                key={payment.id}
                className="border-b border-border hover:bg-background transition-colors"
              >
                <td className="py-4 px-4 font-medium">{payment.tenant}</td>
                <td className="py-4 px-4 text-foreground/60 text-sm">
                  {payment.property}
                </td>
                <td className="py-4 px-4 font-semibold">
                  ${payment.amount.toLocaleString()}
                </td>
                <td className="py-4 px-4">
                  <span
                    className={`badge ${getStatusBadgeClass(payment.status)}`}
                  >
                    {payment.status.charAt(0).toUpperCase() +
                      payment.status.slice(1)}
                  </span>
                </td>
                <td className="py-4 px-4 text-sm text-foreground/60">
                  {payment.date}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
