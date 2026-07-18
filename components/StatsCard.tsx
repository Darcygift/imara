interface StatsCardProps {
  label: string;
  value: string | number;
  icon: string;
  bgColor: string;
  textColor: string;
}

export default function StatsCard({
  label,
  value,
  icon,
  bgColor,
  textColor,
}: StatsCardProps) {
  return (
    <div className="card">
      <div className="flex justify-between items-start">
        <div>
          <p className="text-sm text-foreground/60 mb-2">{label}</p>
          <p className={`text-3xl font-bold ${textColor}`}>{value}</p>
        </div>
        <span className={`text-4xl p-3 rounded-lg ${bgColor}`}>{icon}</span>
      </div>
    </div>
  );
}
