"use client";

export default function PaymentChart() {
  const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun"];
  const data = [45, 38, 52, 48, 61, 72];
  const maxValue = Math.max(...data);

  return (
    <div className="flex items-end justify-between h-64 gap-2">
      {months.map((month, index) => (
        <div key={month} className="flex flex-col items-center flex-1">
          <div className="w-full relative h-56 flex items-end justify-center">
            <div
              className={`w-3/4 rounded-t-lg transition-all hover:opacity-80 cursor-pointer ${
                index === 5
                  ? "bg-primary"
                  : "bg-foreground/10 dark:bg-foreground/20"
              }`}
              style={{
                height: `${(data[index] / maxValue) * 100}%`,
              }}
            />
          </div>
          <span className="text-xs text-foreground/60 mt-2">{month}</span>
          <span className="text-sm font-semibold mt-1">
            ${(data[index] * 1000).toLocaleString()}
          </span>
        </div>
      ))}
    </div>
  );
}
